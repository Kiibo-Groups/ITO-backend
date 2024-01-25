<?php

namespace App\Models;

use App\Http\Controllers\NodejsServer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Validator;
use Auth;
use DB;
use App\Enums\StatusAdmin;
use Illuminate\Support\Facades\Date;

class Delivery extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = "delivery_boys";
    protected $hidden = [
        'password',
        'remember_token',
        'shw_password'
    ];

    protected $guarded = ['id'];

    public function getDisabledAttribute()
    {
        return Date::parse($this->updated_at)->isToday();
    }

    public function group_member()
    {
        return $this->hasOne(GroupMember::class, 'member_id', 'id');
    }
    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */

    public function kms() {
        return $this->hasMany(RegisterKm::class, 'dboy_id', 'id');
    } 
    public function rules($type)
    {
        $emailRule = 'required|unique:delivery_boys,email';
        $phoneRule = 'required|unique:delivery_boys,phone';
        
        if ($type !== 'add') {
            $emailRule .= ',' . $type;
            $phoneRule .= ',' . $type;
        }

        return [
            'phone' => $phoneRule,
            'email' => $emailRule,
        ];
    }

    public function validate($data, $type)
    {
        $validator = Validator::make($data, $this->rules($type));
        
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return $errors;
        }
    }

    /*
    |--------------------------------
    |Create/Update city
    |--------------------------------
    */

    public function addNew($data, $type, $from)
    {

        $add                    = $type === 'add' ? new Delivery : Delivery::find($type);
        $add->city_id           = isset($data['city_id']) ? $data['city_id'] : 0;
        $add->can_make_calls    = isset($data['can_make_calls']) ? $data['can_make_calls'] : 1;
        $add->name              = isset($data['name']) ? $data['name'] : null;
        $add->phone             = isset($data['phone']) ? $data['phone'] : null;
        $add->email             = isset($data['email']) ? $data['email'] : null;
        $add->type_driver       = isset($data['type_driver']) ? $data['type_driver'] : 0;
        $add->type_edriver      = isset($data['type_edriver']) ? $data['type_edriver'] : 0;
        $add->max_range_km      = isset($data['max_range_km']) ? $data['max_range_km'] : null;
        $add->brand             = isset($data['brand']) ? $data['brand'] : null;
        $add->model             = isset($data['model']) ? $data['model'] : null;
        $add->color             = isset($data['color']) ? $data['color'] : null;
        $add->number_plate      = isset($data['number_plate']) ? $data['number_plate'] : null;
        $add->passenger         = isset($data['passenger']) ? $data['passenger'] : 0;

        if (isset($data['licence'])) {
            $path = 'upload/licence/';
            $extension = $data['licence']->getClientOriginalExtension();
            $filename   = time() . rand(111, 699) . '.' . $extension;
            $data['licence']->move(public_path($path), $filename);
            $add->licence           = $filename;
        }

        if (isset($data['carnet'])) {
            $path = 'upload/credential/';
            $extension = $data['carnet']->getClientOriginalExtension();
            $filename   = time() . rand(111, 699) . '.' . $extension;
            $data['carnet']->move(public_path($path), $filename);
            $add->carnet           = $filename;
        }

        $add->rfc               = isset($data['rfc']) ? $data['rfc'] : null;

        if ($from == 'app') {
            $add->status = 1; // Bloqueado
            $add->status_admin = 1; // Bloqueado
        } else {
            $add->status            = isset($data['status']) ? $data['status'] : 0;
            $add->status_admin            = isset($data['status_admin']) ? $data['status_admin'] : 0;
        }

        if (isset($data['password'])) {
            $add->password      = bcrypt($data['password']);
            $add->shw_password  = $data['password'];
        }

        $add->save();
        // Registramos en el servidor Secundario
        try {
            $addServer = new NodejsServer;
            $return = array(
                'id'        => $add->id,
                'city_id'   => $add->city_id,
                'can_make_calls' =>  $add->can_make_calls,
                'name'      => $add->name,
                'phone'     => $add->phone,
                'type_driver' => $add->type_driver,
                'max_range_km' => $add->max_range_km,
                'external_id'   => $add->external_id,
                'status'        => $add->status,
                'status_admin'  => $add->status_admin,
            );

            if ($type == 'add') {
                $addServer->newStaffDelivery($return);
            } else {
                $addServer->updateStaffDelivery($return);
            }

            if ($from == 'app') {
                return ['status' => true ,'msg' => 'done', 'user_id' => $add->id, 'external_id' => $add->external_id];
            }
        } catch (\Throwable $th) {
            if ($from == 'app') {
                return ['status' => false ,'msg' => 'fail'];
            }
        }
    }


    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll($store = 0)
    {
        return Delivery::where(function ($query) use ($store) {
        })->leftjoin('city', 'delivery_boys.city_id', '=', 'city.id')
            ->leftjoin('delivery_type', 'delivery_boys.type_driver', '=', 'delivery_type.id')
            ->select('city.name as city', 'delivery_type.icon as icon_driver', 'delivery_type.name as name_driver', 'delivery_boys.*')
            ->orderBy('delivery_boys.id', 'DESC')->get();
    }

    public function getStaff($id)
    {
        $res = Delivery::find($id);
        $vehicle = DeliveryType::find($res->type_driver);
        /****** Ratings ********/
        $totalRate    = Rate::where('staff_id', $id)->count();
        $totalRateSum = Rate::where('staff_id', $id)->sum('star');

        if ($totalRate > 0) {
            $avg          = $totalRateSum / $totalRate;
        } else {
            $avg           = 0;
        }
        /****** Ratings ********/

        $data = [
            'id'            =>  $res->id,
            'external_id'   =>  $res->external_id,
            'profile'   =>  asset('upload/profile/' . $res->profile),
            'can_make_calls' =>  $res->can_make_calls,
            'name'          =>  ucwords($res->name),
            'phone'         =>  $res->phone,
            'rfc'           =>  $res->rfc,
            'email'         =>  ucfirst($res->email),
            'amount_acum'   =>  $res->amount_acum,
            'brand'         =>  $res->brand,
            'carnet'        =>  Asset('upload/credential/' . $res->carnet),
            'licence'       =>  Asset('upload/licence/' . $res->licence),
            'biometrics'    =>  Asset('upload/biometric/' . $res->biometric),
            'city_id'       =>  $res->city_id,
            'lat'           =>  $res->lat,
            'lng'           =>  $res->lng,
            'max_range_km'  =>  $res->max_range_km,
            'model'         =>  $res->model,
            'color'         =>  $res->color,
            'number_plate'  =>  $res->number_plate,
            'passenger'     =>  $res->passenger,
            'rating'        =>  $avg > 0 ? number_format($avg, 1) : '0.0',
            'type_driver'   =>  $res->type_driver,
            'vehicle'       =>  $vehicle,
            'type_edriver'  =>  $res->type_edriver,
        ];


        return $data;
    }

    /*
    |--------------------------------------
    |Login To
    |--------------------------------------
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function login($request)
    {
        $credentials = $request->only('email', 'password');

        $delivery = Delivery::where('email', $credentials['email'])->first();

        if ($delivery && $delivery->status_admin  === StatusAdmin::BLOQUEADO) {
            return ['data' => [], 'msg' => 'Error! Conductor bloqueado'];
        }

        if (!$delivery || !Hash::check($credentials['password'], $delivery->password)) {
            return ['data' => [], 'msg' => 'Error! Detalles de acceso incorrectos'];
        }

        $token = JWTAuth::fromUser($delivery);

        return ['data' => $delivery, 'msg' => 'OK', 'token' => $token, 'type' => 'bearer'];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return ['msg' => 'OK'];
    }

    public function verifyDocuments($request)
    {
        $dboy = Delivery::find($request->id);

        if (!$dboy) {
            return ['data' => [], 'msg' => 'Error! Conductor no encontrado'];
        }

        return [
            'data' => [
                'rfc' => !$dboy->rfc ? 'rfc_not_exist' : 'rfc_exist',
                'credential' => !$dboy->credential? 'credential_not_exist' : 'credential_exist',
                'licence' => !$dboy->licence ? 'licence_not_exist' : 'licence_exist',
                'biometric' =>  !$dboy->biometric ? 'biometric_not_exist' : 'biometric_exist',
            ],
            'msg' => 'OK'
        ];
    }

    public function uploadDocuments($request)
    {
        $dboy = Delivery::find($request->id);

        if (!$dboy) {
            return ['data' => [], 'msg' => 'Conductor no encontrado'];
        }

        $type = $request->type;

        $url = null;

        try {
            switch ($type) {
                case 'licence':
                    $fileToDelete = public_path($dboy->licence);
                    if (file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                    break;
                case 'credential':
                    $fileToDelete = public_path($dboy->credential);
                    if (file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                    break;
                case 'biometric':
                    $fileToDelete = public_path($dboy->biometric);
                    if (file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                    break;
            }
        } catch (\Throwable $th) {
        }


        $path = '/' . 'upload/' . $type . '/';

        if ($request->has('camera_file')) {

            $imagenBase64 = $request->input('camera_file');

            $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);

            $imagenDecodificada = base64_decode($image);

            $imageName =  time() . '.png';

            file_put_contents(public_path($path . $imageName), $imagenDecodificada);

            $url = $imageName;
        }

        if (!is_Null($url)) {
            $dboy->fill([
                $type => $url
            ])->save();

            return ['data' => [$url], 'msg' => 'OK'];
        }

        return ['data' => [], 'msg' => 'No se puedo subir la imagen'];
    }

    public function updateImage($request)
    {
        $dboy = Delivery::find($request->dboy_id);

        if (!$dboy) {
            return ['data' => [], 'msg' => 'Conductor no encontrado'];
        }

        $url = null;

        try {
            $fileToDelete = public_path($dboy->profile);
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        } catch (\Throwable $th) {
        }


        $path = '/' . 'upload/profile/';

        if ($request->has('camera_file')) {

            $imagenBase64 = $request->input('camera_file');

            $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);

            $imagenDecodificada = base64_decode($image);

            $imageName =  time() . '.png';

            file_put_contents(public_path($path . $imageName), $imagenDecodificada);

            $url = $imageName;
        }

        if (!is_Null($url)) {
            $dboy->fill([
               'profile' => $url
            ])->save();

            return ['data' => [$url], 'msg' => 'OK'];
        }

        return ['data' => [], 'msg' => 'No se puedo subir la imagen'];
    }

    public function updateRFC($request) {
        $dboy = Delivery::find($request->id);

        if (!$dboy) {
            return ['data' => [], 'msg' => 'Conductor no encontrado'];
        }

        if ($dboy->update(['rfc' => $request->rfc])) {
            return ['data' => $dboy, 'msg' => 'RFC actualizado con éxito'];
        } else {
            return ['data' => [], 'msg' => 'No se pudo actualizar el RFC'];
        }
    }

    public function updateData($request) {
        $dboy = Delivery::find($request->id);
        if (!$dboy) {
            return ['data' => [], 'msg' => 'Conductor no encontrado'];
        }

        $dboy->fill([
            'city_id' => $request->city_id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'type_driver' => $request->type_driver,
            'type_edriver' => $request->type_edriver,
            'max_range_km' => $request->max_range_km,
            'brand' => $request->brand,
            'model' => $request->model,
            'color' => $request->color,
            'number_plate' => $request->number_plate,
            'passenger' => $request->passenger,
            'amount_acum' => $request->amount_acum,
            // 'status' => $request->status,
            // 'status_admin' => $request->status_admin,
            // 'status_send' => $request->status_send,
            'lat' => $request->lat,
            'lng' => $request->lng
        ]);

        if ($dboy->save()) {
            return ['data' => $dboy, 'msg' => 'Datos actualizado con éxito'];
        } else {
            return ['data' => [], 'msg' => 'Error al actualizar los datos'];
        }

    }

    /*
    |--------------------------------------
    |Get Report
    |--------------------------------------
    */
    public function getReport($data)
    {
        $res = Delivery::where(function ($query) use ($data) {

            if ($data['staff_id']) {
                $query->where('delivery_boys.id', $data['staff_id']);
            }
        })->join('commaned', 'delivery_boys.id', '=', 'commaned.d_boy')
            ->select('commaned.user_id as ord_user_id', 'commaned.*', 'delivery_boys.*')
            ->orderBy('delivery_boys.id', 'ASC')->get();

        $allData = [];

        foreach ($res as $row) {

            // Obtenemos el usuario
            $user = AppUser::find($row->ord_user_id);

            $allData[] = [
                'id'                => $row->id,
                'name'              => $row->name,
                'rfc'               => $row->rfc,
                'email'             => $row->email,
                'user'             => $user->name,
                'user_email'         => $user->email,
                'platform_porcent'  => $row->price_comm,
                'type_staff_porcent' => ($row->c_type_staff == 0) ? 'Valor Fijo' : 'valor en %',
                'staff_porcent'     => $row->c_value_staff,
                'total'             => $row->total
            ];
        }

        return $allData;
    }

    /*
    |--------------------------------------
    |Get all data from db for Charts
    |--------------------------------------
    */
    public function overView()
    {
        // 

        $admin = new Admin;

        return [
            'total'     => Commaned::where('d_boy', $_GET['id'])->count(),
            'complete'  => Commaned::where('d_boy', $_GET['id'])->where('status', 6)->count(),
            'canceled'  => Commaned::where('d_boy', $_GET['id'])->where('status', 2)->count(),
            'saldos'    => $this->saldos($_GET['id']),
            'x_day'     => [
                'tot_orders' => Commaned::where('d_boy', $_GET['id'])->whereDate('created_at', 'LIKE', '%' . date('m-d') . '%')->count(),
                'amount'     => $this->chartxday($_GET['id'], 0, 1)['amount']
            ],
            'day_data'     => [
                'day_1'    => [
                    'data'  => $this->chartxday($_GET['id'], 2, 1),
                    'day'   => $admin->getDayName(2)
                ],
                'day_2'    => [
                    'data'  => $this->chartxday($_GET['id'], 1, 1),
                    'day'   => $admin->getDayName(1)
                ],
                'day_3'    => [
                    'data'  => $this->chartxday($_GET['id'], 0, 1),
                    'day'   => $admin->getDayName(0)
                ]
            ],
            'week_data' => [
                'total' => $this->chartxWeek($_GET['id'])['total'],
                'amount' => $this->chartxWeek($_GET['id'])['amount']
            ],
            'month'     => [
                'month_1'     => $admin->getMonthName(2),
                'month_2'     => $admin->getMonthName(1),
                'month_3'     => $admin->getMonthName(0),
            ],
            'complet'   => [
                'complet_1'    => $this->chart($_GET['id'], 2, 1)['order'],
                'complet_2'    => $this->chart($_GET['id'], 1, 1)['order'],
                'complet_3'    => $this->chart($_GET['id'], 0, 1)['order'],
            ],
            'cancel'   => [
                'cancel_1'    => $this->chart($_GET['id'], 2, 1)['cancel'],
                'cancel_2'    => $this->chart($_GET['id'], 1, 1)['cancel'],
                'cancel_3'    => $this->chart($_GET['id'], 0, 1)['cancel']
            ]
        ];
    }

    public function saldos($id)
    {
        // Saldos y Movimientos
        $discount = 0;
        $cargos   = 0;
        $ventas   = 0;
        $comm     = 0;

        $staff      = Delivery::find($id);
        $vehicle    = DeliveryType::find($staff->type_driver);
        $c_type     = $vehicle->type_comm; // 1 = %, 0 = Fijo
        $c_value    = $vehicle->comm;
        $saldo      = $staff->amount_acum;
        $order_day  = Commaned::where(function ($query) use ($id) {

            $query->where('d_boy', $id);
        })->where('status', 6)->get();

        $sum = Commaned::where(function ($query) use ($id) {

            $query->where('d_boy', $id);
        })->where('status', 6)->sum('d_charges');

        if ($order_day->count() > 0) {
            if ($c_type == 0) {
                $comm = $c_value;
                $ventas = $ventas + ($sum - $comm);
            } else {
                $comm = ($sum * $c_value) / 100;
                $ventas = $ventas + ($sum - $comm);
            }

            $cargos = $cargos + $comm;
        }

        return [
            'Saldo'      => round($saldo, 2),
            'cargos'     => round($cargos, 2),
            'ventas'     => round($ventas, 2)
        ];
    }

    public function chart($id, $type, $sid = 0)
    {
        $month      = date('Y-m', strtotime(date('Y-m') . ' - ' . $type . ' month'));

        $order   = Commaned::where(function ($query) use ($sid, $id) {

            if ($sid > 0) {
                $query->where('d_boy', $id);
            }
        })->where('status', 6)->whereDate('created_at', 'LIKE', $month . '%')->count();


        $cancel  = Commaned::where(function ($query) use ($sid, $id) {

            if ($sid > 0) {
                $query->where('d_boy', $id);
            }
        })->where('status', 2)->whereDate('created_at', 'LIKE', $month . '%')->count();

        return ['order' => $order, 'cancel' => $cancel];
    }

    public function chartxday($id, $type, $sid = 0)
    {
        $admin = new Admin;
        $date_past = strtotime('-' . $type . ' day', strtotime(date('Y-m-d')));
        $day = date('m-d', $date_past);


        $comm = 0;
        $amount = 0;
        $debt  = 0;
        $ventas = 0;

        $order   = Commaned::where(function ($query) use ($sid, $id) {

            if ($sid > 0) {
                $query->where('d_boy', $id);
            }
        })->where('status', 6)->whereDate('created_at', 'LIKE', '%' . $day . '%')->count();


        $cancel  = Commaned::where(function ($query) use ($sid, $id) {

            if ($sid > 0) {
                $query->where('d_boy', $id);
            }
        })->where('status', 2)->whereDate('created_at', 'LIKE', '%' . $day . '%')->count();


        if ($type == 0) {
            $staff          = Delivery::find($id);
            $vehicle    = DeliveryType::find($staff->type_driver);
            $c_type     = $vehicle->type_comm; // 1 = %, 0 = Fijo
            $c_value    = $vehicle->comm;

            $sum   = Commaned::where(function ($query) use ($id) {

                $query->where('d_boy', $id);
            })->where('status', 6)
                ->whereDate('created_at', 'LIKE', '%' . $day . '%')->sum('d_charges');

            if ($c_type == 0) {
                $comm = $c_value;
                $ventas = $ventas + ($sum - $comm);
            } else {
                $comm = ($sum * $c_value) / 100;
                $ventas = $ventas + ($sum - $comm);
            }
        }

        return [
            'order' => $order,
            'cancel' => $cancel,
            'amount' => round($ventas, 2)
        ];
    }

    public function chartxWeek($id)
    {
        $date = strtotime(date("Y-m-d"));
        $ventas = 0;
        $init_week = strtotime('last Sunday');
        $end_week  = strtotime('next Saturday');

        $total   = Commaned::where(function ($query) use ($id) {

            $query->where('d_boy', $id);
        })->where('status', 6)
            ->where('created_at', '>=', date('Y-m-d', $init_week))
            ->where('created_at', '<=', date('Y-m-d', $end_week))->count();

        $sum   = Commaned::where(function ($query) use ($id) {

            $query->where('d_boy', $id);
        })->where('status', 6)
            ->where('created_at', '>=', date('Y-m-d', $init_week))
            ->where('created_at', '<=', date('Y-m-d', $end_week))->sum('d_charges');

        $dboy = Delivery::find($id);
        $vehicle    = DeliveryType::find($dboy->type_driver);
        $c_type     = $vehicle->type_comm; // 1 = %, 0 = Fijo
        $c_value    = $vehicle->comm;

        if ($c_type == 0) {
            $comm = $c_value;
            $ventas = $ventas + ($sum - $comm);
        } else {
            $comm = ($sum * $c_value) / 100;
            $ventas = $ventas + ($sum - $comm);
        }

        return [
            'total'   => $total,
            'amount'  => round($ventas, 2),
            'lastday' => date('Y-m-d', $init_week),
            'nextday' => date('Y-m-d', $end_week)
        ];
    }

    /*
    |--------------------------------------
    |Add Comm
    |--------------------------------------
    */

    public function add_comm($data, $id)
    {
        $staff = Delivery::find($id);

        $acum  = round($staff->amount_acum + $data['pay_staff'], 0);
        $staff->amount_acum = $acum;
        $staff->save();
        return true;
    }

    public function Commset_delivery($order_id, $d_boy_id)
    {
        $order          = Commaned::find($order_id);
        $staff          = Delivery::find($d_boy_id);
        $DeliveryType   = DeliveryType::find($staff->type_driver);

        $payment_method = $order->payment_method; // tipo de pago 1 Efectivo
        $c_value_staff  = $DeliveryType->comm; //  Comision segun el tipo de vehiculo

        $delivery_charges = $order->d_charges; // 39.89

        if ($DeliveryType->type_comm == 1) { // en %
            $comm_admin   = round(($delivery_charges * $c_value_staff) / 100,2); // $5.9835
        }else { // Valor Fijo
            $comm_admin   = ($delivery_charges - $c_value_staff); // Punto D
        }
        
        $comm_repa    = ($delivery_charges - $comm_admin); // = 41 - Ganancia del repa


        /*
        * si payment == 1 el pago fue en efectivo y el repartidor le debe al admin
        * si payment == 2 el pago fue con tarjeta y el administrador le debe al repartidor
        * Plataform = $32.9065
        * Conductor = $5.9835
        */

        if ($payment_method == 1) {
            $newSaldo = ($staff->amount_acum - $comm_admin);
        } else {
            $newSaldo = ($staff->amount_acum + $comm_repa);
        }

        $staff->amount_acum = $newSaldo;
        $staff->save();

        return true;
    }

    /*
    |--------------------------------------
    |Get Nearby
    |--------------------------------------
    */

    public function getNearby()
    {
        $staff = Delivery::where('status', 0)->get();
        $max_distance = Admin::find(1)->max_distance_staff; // Rango maximo

        $data  = [];
        foreach ($staff as $key) {
            $lat = $key->lat; // Lat Driver
            $lon = $key->lng; // Lng Driver

            $user_lat = isset($_GET['lat']) ? $_GET['lat'] : 0;
            $user_lng = isset($_GET['lng']) ? $_GET['lng'] : 0;

            if ($lat != null || $lat != '' && $lot != null || $lon != '') {
                $res  = DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                    * cos(radians(" . $user_lat . ")) 
                    * cos(radians(" . $user_lng . ") - radians(" . $lon . ")) 
                    + sin(radians(" . $lat . ")) 
                    * sin(radians(" . $user_lat . "))) AS distance")->get();

                $data[] = [
                    'data' => $res
                ];
            }
        }

        return [
            'data' => $data,
            'lat'  => $_GET['lat']
        ];
    }

    public function setStaffOrder($order_id, $dboy_id)
    {
        // Checamos si el pedido ya fue tomado
        $order = Order::find($order_id);

        if ($order->d_boy != 0) {
            return [
                'status' => 'in_rute'
            ];
        } else {
            // Seteamos la tabla
            Order_staff::where('order_id', $order_id)->delete();

            // Guardamos el Nuevo elemento
            $order_Staff = new Order_staff;

            $order_Staff->external_id = $order->external_id;
            $order_Staff->order_id = $order_id;
            $order_Staff->d_boy    = $dboy_id;
            $order_Staff->status   = 0;
            $order_Staff->save();

            // Guardamos en su Score
            $req     = new Rate_staff;
            $score = array(
                'order' => $order_id,
                'dboy'  => $dboy_id,
                'status' => 0 // en espera
            );
            $req->addNew($score);

            // Notificamos al repartidor
            app('App\Http\Controllers\Controller')->sendPushD("Nuevo pedido recibido", "Tienes una solicitud de pedido, ingresa para más detalles", $dboy_id);

            return [
                'status' => 'not_rute',
                'external_id'  => $order_Staff->external_id
            ];
        }
    }

    /**
     * 
     * Eliminamos al no tener respuesta de algun repartidor 
     * 
     */

    function delStaffOrder($order_id)
    {
        // Seteamos la tabla
        Order_staff::where('order_id', $order_id)->delete();

        $order = Order::find($order_id);

        $order->status = 1;
        $order->save();

        // Notificamos al negocio que no se encontraron repartidores
        $msg = "No hemos encontrado un repartidor disponible para tu solicitud, por favor vuelve a intentarlo";
        $title = "No encontramos repartidores!!";
        app('App\Http\Controllers\Controller')->sendPushS($title, $msg, $order->store_id);

        return [
            'status' => 'done'
        ];
    }

    function delStaffEvent($order_id)
    {
        // Seteamos la tabla
        Order_staff::where('event_id', $order_id)->delete();

        $order = Commaned::find($order_id);

        $order->status = 3;
        $order->save();

        // Notificamos al negocio que no se encontraron repartidores
        $msg = "No hemos encontrado un repartidor disponible para tu solicitud, por favor vuelve a intentarlo";
        $title = "No encontramos repartidores!!";
        app('App\Http\Controllers\Controller')->sendPushS($title, $msg, $order->store_id);

        return [
            'status' => 'done'
        ];
    }
    
    public function chkUser($data)
    {

        if (isset($data['user_id']) && $data['user_id'] != 'null') {
            // Intentamos con el id
            $res = Delivery::find($data['user_id']);

            if (isset($res->id)) {
                $token = JWTAuth::fromUser($res);
                return ['msg' => 'user_exist', 'id' => $res->id, 'data' => $res, 'token' => $token];
            } else {
                return ['msg' => 'not_exist'];
            }
        }
    }

    public function forgot($data)
    {
        $res = Delivery::where('email',$data['email'])->first();

        if(isset($res->id))
        {
            $otp = rand(1111,9999);

            $res->otp = $otp;
            $res->save();

            $para       =   $data['email'];
            $asunto     =   'Codigo de acceso - ITO';
            $mensaje    =   "Hola ".$res->name." Un gusto saludarte, se ha pedido un codigo de recuperacion para acceder a tu cuenta en Zendit";
            $mensaje    .=  ' '.'<br>';
            $mensaje    .=  "Tu codigo es: <br />";
            $mensaje    .=  '# '.$otp;
            $mensaje    .=  "<br /><hr />Recuerda, si no lo has solicitado tu has caso omiso a este mensaje y te recomendamos hacer un cambio en tu contrasena.";
            $mensaje    .=  "<br/ ><br /><br /> Te saluda el equipo de ITO";
        
            $cabeceras = 'From: ITO' . "\r\n";
            
            $cabeceras .= 'MIME-Version: 1.0' . "\r\n";
            
            $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            @mail($para, $asunto, utf8_encode($mensaje), $cabeceras);
    
            $return = ['msg' => 'done','dboy_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! Este correo electrónico no está registrado con nosotros.'];
        }

        return $return;
    }

    public function verify($data)
    {
        $res = Delivery::where('id',$data['dboy_id'])->where('otp',$data['otp'])->first();

        if(isset($res->id))
        {
            $return = ['msg' => 'done','dboy_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! OTP no coincide.'];
        }

        return $return;
    }

    public function updatePassword($data)
    {
        $res = Delivery::where('id',$data['dboy_id'])->first();

        if(isset($res->id))
        {
            $res->password      = bcrypt($data['password']);
            $res->shw_password  = $data['password'];
            $res->save();

            $return = ['msg' => 'done','dboy_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! Algo salió mal.'];
        }

        return $return;
    }

    public function verifyClanDboy($id) {

        $dboy = Delivery::find($id);

        if (!$dboy) {
            return ['data' => [], 'msg' => 'Conductor no encontrado'];
        }

        $data = [
            'dboy_id' => $dboy->id,
            'name' => $dboy->name,
            'calls' => $dboy->can_make_calls ? 'activa' : 'inactiva',
            'clan_id' => null,
            'clan_name' => null,
            'type_dboy' => null,
            'lider_id' => null,
            'lider_name' => null,
        ];

        if ($dboy->group_member && $dboy->group_member->exists()) {
            $clan = $dboy->group_member->group;
            $leader = GroupMember::where('group_id', $clan->id)->where('type', 'LEADERS')->first();
            $data['clan_id'] = $clan->id;
            $data['clan_name'] = $clan->name;
            $data['type_dboy'] = $dboy->group_member->type;
            $data['lider_id'] = $leader->member_id;
            $data['lider_name'] =  $leader->dboy->name;
        }


        return ['data' => $data, 'msg' => 'OK.'];
    }
}