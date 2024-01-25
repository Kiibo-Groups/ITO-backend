<?php

namespace App\Models;

use App\Http\Controllers\NodejsServer;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Auth;
use DB; 
use App\{City, Zones};
use App\Models\NewTrip;

class Commaned extends Authenticatable
{
    protected $table = "commaned";

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class);
    }

    public function dboy() {
        return $this->belongsTo(Delivery::class, 'd_boy');
    }
   
    public function new_trips() {
        return $this->hasMany(NewTrip::class, 'commaned_id', 'id');
    }

    public function getISR($subtotal) {
        $limite = 644.58;
        $limite_inferior = 0.01;
        $restLimit = 0;
        $excedente = 0.0192;
        $calc = 0;
        $cuota = 0;

        if ($subtotal > $limite) {
            $limite = 5470.92;
            $limite_inferior = 644.59;
            $excedente = 0.0640;
            $cuota = 12.38;
        }
        // multiplicaremos la tasa establecida

        $calc = $subtotal * $excedente;

         //  localizamos en el rango salarial en el que nos encontramos, para restarle el límite inferior:

         $restLimit = $subtotal - $calc;

         // debemos sumar esta cantidad a la cuota fija  y retornamos

        return $restLimit;
    }

    public function addNew($data) 
    {
        $add                    = new commaned;
        $add->user_id           = isset($data['user_id']) ? $data['user_id'] : '';
       
        $usr = AppUser::find($add->user_id);

        $add->address_origin    = isset($data['address_origin']) ? $data['address_origin'] : '';
        $add->city_id          = isset($data['city_id']) ? $data['city_id'] : 0;
        $add->name_origin       = $usr->name;
        $add->phone_origin      = $usr->phone;
        $add->first_instr       = isset($data['first_instr']) ? $data['first_instr'] : '';
        $add->lat_orig          = isset($data['lat_orig']) ? $data['lat_orig'] : 0;
        $add->lng_orig          = isset($data['lng_orig']) ? $data['lng_orig'] : 0;

        $add->address_destin    = isset($data['address_destin']) ? $data['address_destin'] : '';
        $add->who_receives      = isset($data['name_destin']) ? $data['name_destin'] : '';
        $add->phone_receives    = isset($data['phone_destin']) ? $data['phone_destin'] : '';
        $add->second_instr      = isset($data['second_instr']) ? $data['second_instr'] : '';
        $add->lat_dest          = isset($data['lat_dest']) ? $data['lat_dest'] : 0;
        $add->lng_dest          = isset($data['lng_dest']) ? $data['lng_dest'] : 0;
        
        $add->d_boy             = isset($data['d_boy']) ? $data['d_boy'] : 0;
        $add->type_driver       = isset($data['type_driver']) ? $data['type_driver'] : 0;
        $add->price_comm        = isset($data['price_comm']) ? $data['price_comm'] : 0;
        $add->d_charges         = isset($data['d_charges']) ? $data['d_charges'] : 0;
        $add->type_trips        = isset($data['type_trips']) ? $data['type_trips'] : 0;

        $add->discount          = isset($data['discount']) ? $data['discount'] : 0;
        /**
         * 
         * Verificamos si utilizo algun cupon
         * 
         */
        $add->cupon_id          = isset($data['cupon']) ? $data['cupon'] : 0;
        if ($add->cupon_id > 0) {
            // Verificamos si ya ha utilizado este cupon
            $chkCp = OfferList::where('offer_id',$add->cupon_id)->where('user_id',$add->user_id)->first();
            if (!$chkCp) {
               // Registramos el cupon
                $newCup = new OfferList;
                $newCup->user_id    = isset($data['user_id']) ? $data['user_id'] : 0;
                $newCup->offer_id   = isset($data['cupon']) ? $data['cupon'] : 0;
                $newCup->count      = 1;
                $newCup->save();
            }else {
                $chkCp->count   = $chkCp->count+1;
                $chkCp->save();
            }   
        }


        $add->total             = isset($data['total']) ? $data['total'] : 0;
        $add->payment_method    = isset($data['payment_method']) ? $data['payment_method'] : 0;

        if ($data['payment_method'] == 2) { // Si el metodo de pago es con wallet checamos saldo
            if ($usr->saldo < $data['total']) { // NO contamos con saldo disponible 
            return ['data' => 'fail','msg' => "balance_insuficient"];
            }
        }

        $add->payment_id        = isset($data['payment_id']) ? $data['payment_id'] : 0;
        $add->status            = isset($data['status']) ? $data['status'] : 0;
        $add->use_mon           = isset($data['use_mon']) ? $data['use_mon'] : 0;

        /**
         *  
         * Verificamos si utilizo su Monedero Electronico
         * 
         */
         if ($add->use_mon == true) {
            $add->uso_monedero = $usr->monedero;
         }

        // Cuanto Cashback genero con esta compra
        $add->monedero = isset($data['monedero']) ? $data['monedero'] : 0;
         
        if ($add->save()) {

            foreach($data['new_trips'] as $item) {
                NewTrip::create([
                    'commaned_id' => $add->id,
                    'search' => $item['search'],
                    'flag' => $item['flag'],
                    'address_new' => $item['address_new'],
                    'lat_new' => $item['lat_new'],
                    'lng_new' => $item['lng_new']
                ]);
            }
        }

        // Comenzamos la solicitud de repartidores
        $return = array(
            'id'        => $add->id,
            'user'     => [
                'id'        => $usr->id,
                'name'      => $usr->name,
                'phone'     => $usr->phone,
            ],
            'origin'     => [
                'lat'       => $add->lat_orig,
                'lng'       => $add->lng_orig,
                'address'   => $add->address_origin
            ],
            'destin'    => [
                'lat'       => $add->lat_dest,
                'lng'       => $add->lng_dest,
                'address'   => $add->address_destin,
                'who_receives' => $add->who_receives,
                'phone_receives' => $add->phone_receives
            ],
            'payment_method'     => $add->payment_method,
            'type_driver' => $add->type_driver,
            'd_charges' => $add->d_charges,
            'type_trips' => $add->type_trips,
            'total'     => $add->total,
            'status'    => 0
        );

        $server_fb = new NodejsServer;
        $addServer = $server_fb->newOrder($return);
        $add->external_id = $addServer['data'];
        $add->save();

        // Comenzamos la solicitud de repartidores en automatico
        $req_staff = [
            'id_order' => $add->id
        ];
        
        $server_fb->NewOrderComm($req_staff);
        // Retornamos hecho
        return ['data' => 'done'];
        
    }

    public function getIva($costs_ship)
    {
        $admin = Admin::find(1);

        $iva_amount      = 0;
        $iva_amount_type = $admin->iva_type; // Cargos de iva de la plataforma
        $iva_amount_value = $admin->iva_value; // Cargos de iva de la plataforma
        // Comision + IVA 
        if ($iva_amount_type == 0) { // Valor en %
            $iva_amount = ($costs_ship * $iva_amount_value) / 100;
        }

        return $iva_amount;
    }

    /*
    |--------------------------------------
    |Actualizacion de instrucciones
    |--------------------------------------
    */
    public function updateComm($data,$id)
    {
        $add                    = commaned::find($id);
        $add->first_instr       = isset($data['first_instr']) ? $data['first_instr'] : '';
        $add->second_instr      = isset($data['second_instr']) ? $data['second_instr'] : '';

        $add->save();

        return true;
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll($status)
    {
        return commaned::where(function($query) use($status){

            if ($status == 1) {
                $query->whereIn('commaned.status',[1,4.5]);
            }else {
                $query->where('commaned.status',$status);
            }

        })->leftjoin('app_user','app_user.id','=','commaned.user_id')
            ->select('app_user.name as name_user','app_user.*','commaned.*')
            ->orderBy('commaned.id','DESC')->get();
    }
    

    /*
    |--------------------------------------
    |Get Element data from db
    |--------------------------------------
    */
    public function getElement($id)
    {
        return commaned::where('commaned.id',$id)
            ->leftjoin('app_user','app_user.id','=','commaned.user_id')
            ->select('app_user.name as name_user','app_user.*','commaned.*')
            ->orderBy('commaned.id','DESC')->get();
    }

    public function viewDboyComm($id)
    {
        $comm = Commaned::find($id);

        if ($comm->d_boy > 0) {
            $dboy = Delivery::find($comm->d_boy);
            if ($dboy) {
                return $dboy->name;
            }else {
                return 'No encontrado';
            }
        }else {
            return "Sin asignar";
        }
    }

    public function viewUserComm($id)
    {
        $comm = Commaned::find($id);

        if ($comm->user_id > 0) {
            $user = AppUser::find($comm->user_id);
            if ($user) {
                return $user->name;
            }else {
                return 'No encontrado';
            }
        }else {
            return "No Encontrado";
        }
    }

    /**
     * 
     * Obtenemos costos de envio por repartos
     * 
    */

    function Costs_shipKM($data)
    {
        
        $admin = Admin::find(1);
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".
        $data['lat_orig'].",".
        $data['lng_orig'].
        "&destinations=".$data['lat_dest'].",".
        $data['lng_dest'].
        "&key=".$admin->ApiKey_google;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close ($ch);


        $request = json_decode($output, true);

        // Obtenemos la ciudad en la que se encuenra
        $city_dat           = $this->GetNearbyCity($data['lat_orig'],$data['lng_orig']);
        
        if ($city_dat['nearby'] == true) {
            $city               = City::find($city_dat['data']['id']);

            $min_distance       = $city->min_distance; // Distancia minima del servicio = 1
            $type_value         = $city->c_type; // Tipo del valor KM/Fijo  = 0
            $value              = $city->c_value; // Valor de la comision = 30
            $min_value          = $city->min_value; // Valor por el minimo del servicio = 30
            $distance = 0; // Distancia de un punto a otro
            $service = 0; // Status del servicio
            $costs_ship = 0; // Costos de envio
            $times_delivery = '0 mins'; // Tiempos de entrega
            $purse_x_delivery = 0; // CashBack

            if($request['status'] == 'OK') {
                $km_inm = $request['rows'][0]['elements'][0]['distance']['value'];
                $times_delivery = $request['rows'][0]['elements'][0]['duration']['text'];

                $distance = ($km_inm / 1000); // 1542 / 1000 = 1.558
                
                $service = 1; // Si hay servicio
                
                // si los km extra son menor a 0 se cobra el minimo por servicio
                if (round($distance,2) < $min_distance) {
                  $costs_ship = $min_value;
                }else {
                    $km_extra   = ($distance - $min_distance); // 1.558 - 1 =  0.558
                    $value_ext  = ($type_value == 0) ? ($km_extra * $value) : ($km_extra + $value); // -1.442 * 10
                    $costs_ship = ($min_value + $value_ext); // 20 + 
                }

                // Calculamos el CashBack
                if ($admin->purse_x_delivery > 0) {
                    $purse_x_delivery = round(($costs_ship * $admin->purse_x_delivery) / 100,2);
                }
            }

            
            return [
                'city'          => $city,
                'service'       => 1,
                'costs_ship'    => round($costs_ship,2),
                'cashBack'      => $purse_x_delivery,
                'duration'      => $times_delivery,
                'distance'      => round($distance,2),
                'min_distance' => $min_distance,
                'city'          => $city_dat,
                'url'           => $url
            ];
        }else {
            return [
                 'city'         => 0,
                'service'       => 0,
                'costs_ship'    => 0,
                'duration'      => "0 min",
                'distance'      => 0,
                'city'          => $city_dat
            ];
        }
    }

    /************
     * 
     * Obtenemos servicio de ubicacion en base a zonas
     * 
     */
    public function getServiceZone($lat,$lng)
    {
        // Obtenemos la ciudad en la que se encuentra
		$city_dat   = $this->GetNearbyCity($lat,$lng);
        $data       = [];
        if ($city_dat['nearby'] == true) { // Tenemos una ciudad
            
            // Obtenemos las Zonas de esta ciudad ordenadas por tamaño de poligono
            $zones = Zones::where('city_id',$city_dat['data']['id'])->orderBy('coverage','DESC')->get();

            foreach ($zones as $key => $value) {
               $data[] = [
                'zone' => $value->id,
                'coords' => $value->coords
               ];
            }
            return [
                'status' => true,
                'data' => $data
            ];

        }else {
            return [
                'status' => false,
                'msg' => 'not_service_city'
            ];
        }
    }

    /************
     * 
     * Obtenemos la ciudad para el servicio
     * 
     */

    public function GetNearbyCity($lat,$lon)
    { 
        $data = [];
        $allCity = [];

        $nearby = City::where(function($query) { 
            $query->where('status',0);
        })->select('city.*',DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
        * cos(radians(city.lat)) 
        * cos(radians(city.lng) - radians(" . $lon . ")) 
        + sin(radians(" .$lat. ")) 
        * sin(radians(city.lat))) AS distance"))->orderBy('distance','ASC')->get();

        foreach ($nearby as $key) { 
            if ($key->distance <= $key->max_distance) {
                $data[] = [
                    'id' => $key->id,
                    'name' => $key->name,
                    'lat'  => $key->lat,
                    'lng'  => $key->lng,
                    'status' => $key->status,
                    'distance' => $key->distance
                ];   
            }  
            
            $allCity[] = [
                'id' => $key->id,
                'name' => $key->name,
                'lat'  => $key->lat,
                'lng'  => $key->lng,
                'status' => $key->status,
                'distance' => $key->distance
            ];
        }

        if (count($data) > 0) {
            return ['nearby' => true, 'data' => $data[0]];
        }else {
            return ['nearby' => false, 'data' => $allCity];
        }
    }

    /**
     * 
     * Obtenemos listado de repartidores mas cercanos 
     * 
    */
    public function getNearby($event_id)
    {
        // Obtenemos las coordenadas de entrega
        $order       = Commaned::find($event_id);
        $type_driver = $order->type_driver;
        // Obtenemos el arreglo de los repartidores
        $staff       = Delivery::where('status',0)
                        ->where('type_driver',$type_driver) // Que sea del tipo de conductor seleccionado
                        ->where('status',0) // Que este activo
                        ->where('status_admin',0) // Que no este bloqueado
                        ->get(); // que este activo
                        

        // Seteamos el mensaje
        $msg2 = "Nuevo viaje, Ingresa para más información";
        
        $data  = [];
        foreach ($staff as $key) {
            // Obtenemos lat & lng de cada repa
            $lat = $key->lat;
            $lon = $key->lng;

            // Verificamos que esten bien
            if ($lat != null || $lat !='' && $lot != null || $lon !='') {        
                // Comparamos las coordenadas entre el repartidor y el punto de recoleccion del pedido
                $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=kilometers&origins=".
                $lat.",".
                $lon."&destinations=".
                $order->lat_orig.",".
                $order->lng_orig.
                "&key=".Admin::find(1)->ApiKey_google;
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec ($ch);
                $info = curl_getinfo($ch);
                $http_result = $info ['http_code'];
                curl_close ($ch);
        
        
                $request = json_decode($output, true);
                
                $max_distance = 0;
                $max      = 0;
                
                if($request['status'] == 'OK') {
                    if ($request['rows'][0]['elements'][0]['status'] == 'OK') {
                        $km_inm = intval(str_replace('km','',$request['rows'][0]['elements'][0]['distance']['value']));
                        
                        $max_distance = round($km_inm / 1000,2);
                        
                        //Obtenemos la distancia de cada repa al punto A
                        $distancia_total = $max_distance;
                        // Si la distancia maxima del repa es mayor a 0 procedemos 
                        if ($key->max_range_km > 0) {
                            // Si la distancia maxima de entrega entra en el rango entramos
                            if ($distancia_total <= $key->max_range_km) {
                                // Si el saldo es mayor a 0 asignamos,
                                if ($key->amount_acum >= 0) {
                                   $data[] = [
                                        'max_range_km' => $key->max_range_km,
                                        'distancia_total' => $distancia_total,
                                        'km_inm' => $km_inm,
                                        'dboy' => $key->id,
                                        'name' => $key->name,
                                        'request' => $request
                                    ];
                                }    
                            };
                        };
                    }
                }   
            } 
        }

        return [
            'dboys' => (count($data) > 0) ? $this->ORDER_ASC_STAFF($data) : []
        ];
    }

    function ORDER_ASC_STAFF($data)
    {
        foreach ($data as $key => $row) {
            $aux[$key] = $row['distancia_total'];
        }

        array_multisort($aux, SORT_ASC, $data);

        return $data;
    }

    /**
     * 
     * Seteamos el repartidor enviado por el servidor NODEJS 
     * 
    */

    function setStaffEvent($event_id,$dboy_id)
    {
        
        // Checamos si el pedido ya fue tomado
        $event = Commaned::find($event_id);

        if ($event->status != 2) { // No ha sido canelado
        
            if ($event->d_boy != 0) {
                return [
                    'status' => 'in_rute'
                ];
            }else {
                // Seteamos la tabla
                Order_staff::where('event_id',$event_id)->delete();

                // Guardamos el Nuevo
                $order = new Order_staff;

                $order->event_id = $event_id;
                $order->d_boy    = $dboy_id;
                $order->type     = 1; // 0 = Food Delivery & 1 = Delivery Box
                $order->status   = 0;
                $order->save();
    
                // Notificamos al repartidor
                app('App\Http\Controllers\Controller')->sendPushD("Nuevo viaje","Un nuevo viaje te esta esperando, revisa los detalles.",$dboy_id);

                return [
                    'status' => 'not_rute'
                ];
            }
        }else {
            // Seteamos la tabla
            Order_staff::where('event_id',$event_id)->delete();
        }
    }

    /**
     * 
     * Eliminamos al no tener respuesta de algun repartidor 
     * 
    */

    function delStaffEvent($event_id)
    {
        // Seteamos la tabla
        Order_staff::where('event_id',$event_id)->delete();

        // Marcamos como repartidor no encontrado status = 3
        $event = Commaned::find($event_id);
        
        if ($event->status == 2) { // Ya estaba cancelado
            $event->status = 2;
            $event->save();
            // Cambiamos el status en FB 
            $fb_server = new NodejsServer;
            $dat_s = array(
                'external_id' 	=> $event->external_id,
                'status' 		=> $event->status,
                'change_from'   => 'user_app'
            );
            $fb_server->orderStatus($dat_s); 

            // Notificamos al usuario que no se encontro repartidores
            $msg = "Hemos detectado que tu viaje ha sido cancelado, Si no fue asi por favor ponte en contacto con soporte.";
            $title = "Viaje cancelado";
            app('App\Http\Controllers\Controller')->sendPush($title,$msg,$event->user_id);
            
            return [
                'status' => 'done'
            ];
        }else {
            $event->status = 3;
            $event->save();

            // Cambiamos el status en FB 
            $fb_server = new NodejsServer;
            $dat_s = array(
                'external_id' 	=> $event->external_id,
                'status' 		=> $event->status,
                'change_from'   => 'user_app'
            );
            $fb_server->orderStatus($dat_s); 

            // Notificamos al usuario que no se encontro repartidores
            $msg = "No hemos encontrado un repartidor disponible para tu solicitud, por favor vuelve a intentarlo";
            $title = "No encontramos repartidores!!";
            app('App\Http\Controllers\Controller')->sendPush($title,$msg,$event->user_id);
            
            return [
                'status' => 'done'
            ];
        }

        
    }

    /**
     * 
     * Obtenemos el historial completo 
     * 
    */

    public function history($id)
    {
       $data     = [];
       $currency = Admin::find(1)->currency;
 
       $orders = Commaned::where(function($query) use($id){
 
          if($id > 0)
          {
             $query->where('commaned.user_id',$id);
          }
 
          if(isset($_GET['status']))
          {
             if($_GET['status'] == 3 || $_GET['status'] == 3.5)
             {
                $query->whereIn('commaned.status',[3,3.5,4]);
             }
             else
             {
                $query->where('commaned.status',5);
             }
          }
 
       })->join('delivery_boys','commaned.d_boy','=','delivery_boys.id')
          ->select('commaned.*','delivery_boys.name as dboy')
          ->orderBy('id','DESC')
          ->get();
 
       
       foreach($orders as $order)
       {
          
          if($order->status == 0)
          {
            $status = "Pendiente";
          }
          elseif($order->status == 1)
          {
            $status = "Confirmada";
          }
          elseif($order->status == 2)
          {
            $status = "Cancelada";
          }
          elseif($order->status == 3)
          {
            $status = "Conductor no encontrado";
          }
          elseif($order->status == 4)
          {
            $status = "Elegido para entregar por ".$order->dboy;
          }
          elseif($order->status == 5)
          {
            $status = "Viaje finalizado";
          }
          elseif($order->status == 6)
          {
            $status = "Viaje finalizado";
          }
          else
          {
             $status = "Sin estatus";
          }
 
          $countRate = Rate::where('event_id',$order->id)->where('user_id',$id)->first();
          $tot_com   = $order->total - $order->d_charges;
 
          $data[] = [
 
             'id'        => $order->id,
             'date'      => date('d-M-Y',strtotime($order->created_at))." | ".date('h:i:A',strtotime($order->created_at)),
             'total'     => $order->total,
             'dboy'      => ($order->d_boy > 0) ? Delivery::find($order->d_boy) : [],
             'tot_com'   => $tot_com, 
             'd_charges' => $order->d_charges,
             'status'    => $status,
             'st'        => $order->status,
             'hasRating' => isset($countRate->id) ? $countRate->star : 0,
             'ratStaff'  => isset($countRate->staff_id) ? $countRate->staff_id : 0,
             'event'      => $order,
             'pay'       => $order->payment_method
          ];
       }
 
       return $data;
    }

    public function history_staff($id)
    {
       $data     = [];
       $currency = Admin::find(1)->currency;
 
       $orders = Commaned::with('new_trips')->where(function($query) use($id){
 
          if(isset($_GET['id']))
          {
             $query->where('commaned.d_boy',$_GET['id']);
          }
 
          $query->whereIn('commaned.status',[0,1,2,3,4,4.5,5,6]);
 
       })->join('delivery_boys','commaned.d_boy','=','delivery_boys.id')
          ->select('commaned.*','delivery_boys.name as dboy')
          ->orderBy('id','DESC')
          ->get();
 
       
       foreach($orders as $order)
       {
          
          if($order->status == 0)
          {
            $status = "Pendiente";
          }
          elseif($order->status == 1)
          {
            $status = "Confirmada";
          }
          elseif($order->status == 2)
          {
            $status = "Cancelada";
          }
          elseif($order->status == 3)
          {
            $status = "Repartidor no encontrado";
          }
          elseif($order->status == 4)
          {
            $status = "Elegido para entregar por ".$order->dboy;
          }
          elseif($order->status == 5)
          {
            $status = "Pedido entregado";
          }
          elseif($order->status == 6)
          {
            $status = "Pedido entregado";
          }
          else
          {
             $status = "Sin estatus";
          }
 
          $countRate = Rate::where('event_id',$order->id)->where('staff_id',$_GET['id'])->first();
          $tot_com   = $order->total - $order->d_charges;
 
          $data[] = [
 
             'id'        => $order->id,
             'date'      => date('d-M-Y',strtotime($order->created_at))." | ".date('h:i:A',strtotime($order->created_at)),
             'total'     => $order->total,
             'tot_com'   => $tot_com, 
             'd_charges' => $order->d_charges,
             'status'    => $status,
             'st'        => $order->status,
             'hasRating' => isset($countRate->id) ? $countRate->star : 0,
             'ratStaff'  => isset($countRate->staff_id) ? $countRate->staff_id : 0,
             'event'      => $order,
             'pay'       => $order->payment_method
          ];
       }
 
       return $data;
    }

    public function history_ext($id)
   {
      $data     = [];
      $currency = Admin::find(1)->currency;

      $orders = Order_staff::where(function($query) use($id){

         if(isset($_GET['id']))
         {
            $query->whereIn('orders_staff.d_boy',[$_GET['id']]);
         }

         if(isset($_GET['status']))
         {
            if($_GET['status'] == 1)
            {
               $query->whereIn('orders_staff.status',[0,1,2,3,4,4.5]);
            }
         }

      })->get();

      if ($orders->count() > 0) {

         foreach($orders as $pedido)
         {
            
            $order = Commaned::with('new_trips')->find($pedido->event_id);

            $countRate = Rate::where('event_id',$order->id)->where('staff_id',$id)->first();
            $tot_com   = $order->total - $order->d_charges;

            $can_make_calls = null;
            $clan = null;
            if ($order->status >= 1) {
                $can_make_calls = $order->dboy->can_make_calls;
                
                if (isset($order->dboy->group_member)) {
                    $clan =  $order->dboy->group_member->group->name;
                }
            }

            $data[] = [
                'type'      => 'comanded',
                'id'        => $order->id,
                'user'      => AppUser::find($order->user_id),
                'date'      => date('d-M-Y',strtotime($order->created_at))." | ".date('h:i:A',strtotime($order->created_at)),
                'total'     => $order->total,
                'd_charges' => $order->d_charges,
                'tot_com'   => $tot_com, //$i->RealTotal($order->id),
                'st'        => $order->status,
                'stime'     => $order->status_time,
                'sid'       => $order->user_id,
                'hasRating' => isset($countRate->id) ? $countRate->star : 0,
                'currency'  => $currency,
                'pay'       => $order->payment_method,
                'comm'      => $order,
                'clan'      =>  $clan,
            ];
            
         }
      }
      return $data;
   }

    /**
     * 
     * Obtenemos todos los eventos de este usuario que esten activos 
     * 
    */
    function chkEvents_comm($id)
    {
        $req = commaned::where(function($query) use($id){
            $query->where('commaned.user_id',$id);
            $query->whereIn('commaned.status',[0,1,3,4,4.5,5]);
        })->orderBy('id','DESC')
        ->get();
        
        $data = [];

        foreach ($req as $key) {
            $data[] = [
                'created_at' => $key->created_at->diffForHumans(),
                'dboy' => ($key->d_boy != 0) ? Delivery::find($key->d_boy) : [],
                'event' => $key
            ];
        }

        return $data;
    }

    /**
     * 
     * Obtenemos toda la info de este servicio unico
     * 
    */
    function chk_comm($id)
    {
        $req = commaned::find($id); 
        
        $data = [
            'date' => $req->created_at->format('d-M-Y H:m A'),
            'dboy' => ($req->d_boy != 0) ? Delivery::find($req->d_boy) : [],
            'event' => $req
        ];

        return $data;
    }

      /**
     * 
     * Cancelamos el viaje por parte del conductor 
     * 
    */
    function cancelCommDboy_event($data)
    {
        
        $req = Commaned::find($data->event_id);

        $req->status = 2;
        $req->cancellation_reason = $data->cancellation_reason;
        if ($req->save()) {

        // Cambiamos el status en FB 
        $fb_server = new NodejsServer;
        $dat_s = array(
            'external_id' 	=> $req->external_id,
            'status' 		=> $req->status,
            'change_from'   => 'user_app'
        );
        $fb_server->orderStatus($dat_s); 

        // Seteamos la tabla
        Order_staff::where('event_id',$data->event_id)->delete();

        $data = [
            'cancellation_reason' => $req->cancellation_reason,
            'event_id' => $data->event_id,
            'dboy_id' =>  $req->d_boy,
            'user_id'   =>   $req->user_id
        ];

        return ['data' => $data, 'msg' => 'OK'];
        }
        
        return ['data' => [], 'msg' => 'Viaje no se puede cancelar'];
        
    }


    /**
     * 
     * Cancelamos el viaje por parte del usuario 
     * 
    */

    function cancelComm_event($event_id)
    {
        
        $req = Commaned::find($event_id);

        $req->status = 2;
        $req->save();

        // Cambiamos el status en FB 
        $fb_server = new NodejsServer;
        $dat_s = array(
            'external_id' 	=> $req->external_id,
            'status' 		=> $req->status,
            'change_from'   => 'user_app'
        );
        $fb_server->orderStatus($dat_s); 

        // Seteamos la tabla
        Order_staff::where('event_id',$event_id)->delete();

        return [
            'status' => 'done'
        ];
        
    }

    /**
     * 
     * finaliza el viaje
     * 
     * 
    */

    public function finallyRate($data) {
        $add = new Rate;
        // Agregamos nuevo
        if (isset($data['user_id'])) {
            $add->user_id     = $data['user_id'];
        }

        $add->staff_id    = $data['d_boy'];
        $add->event_id    = $data['oid'];
            
        
        if (isset($data['user_id'])) {
            if ($data['otype'] == 1) { // Pago en efectivo
                // Cambiamos el status en Mysql
                $req = commaned::find($data['oid']);
                $req->status = 6;
                $req->save();

                // Cambiamos el status en FB 
                $fb_server = new NodejsServer;
                $dat_s = array(
                    'external_id' 	=> $req->external_id,
                    'status' 		=> $req->status,
                    'change_from'   => 'user_app'
                );
                $fb_server->orderStatus($dat_s); 
                
                $add->save();

                return ['data' => 'done'];
            }else {
                // Verificamos si cuenta con el saldo suficiente
                $usr = AppUser::find($data['user_id']);

                if ($usr->saldo > $data['total']) { // Si contamos con saldo disponible 
                
                    // Quitamos saldo
                    $saldo = ($usr->saldo - $data['total']);
                    $usr->saldo = $saldo;
                    $usr->save();

                    // Cambiamos el status en Mysql
                    $req = commaned::find($data['oid']);
                    $req->status = 6;
                    $req->save();

                    // Cambiamos el status en FB 
                    $fb_server = new NodejsServer;
                    $dat_s = array(
                        'external_id' 	=> $req->external_id,
                        'status' 		=> $req->status,
                        'change_from'   => 'user_app'
                    );
                    $fb_server->orderStatus($dat_s); 

                    // Guardamos lo anterior
                    $add->save();
                  
                    return ['data' => 'done'];
        
                }else { // No contamos con saldo
                    return ['data' => 'fail','msg' => "balance_insuficient"];
                }
            }
        }else {
            $add->save();
        }
    }

    /**
     * 
     * Conductor califica al usuario
     * 
     */

    public function rateCommDboy_event($rate) {

        $data = Rate::where('event_id', $rate['oid'])->first();

        if (!$data) {
            return ['data' => [], 'msg' => 'Error,el evento no existe'];
        }

        $data->star_user = $rate['star_user'];
        $data->comment_dboy = $rate['comment_dboy'];

        $data->save();

        $msg = "El conductor te ha calificado con ".$rate['star_user'].' estrellas.';
        $title = "Te han calificado.";

        app('App\Http\Controllers\Controller')->sendPush($title,$msg, $data->user_id);

        return ['data' => $data, 'msg' => 'OK'];
    }

    /**
     * 
     * Usuario califica el servicio
     * 
    */

    function rateComm_event($data)
    {

        $data = Rate::where('event_id', $data['oid'])->first();

        if (!$data) {
            return ['data' => [], 'msg' => 'Error,el evento no existe'];
        }

        $data->star = $data['star'];
        $data->comment_staff = $data['comment'] ?? '';
        $data->save();

        $msg = "El usuario ha calificado tu servicio con ".$data['star'].' estrellas.';
        $title = "Te han calificado por tu servicio.";
        app('App\Http\Controllers\Controller')->sendPushD($title,$msg,$data['d_boy']);

        return ['data' => $data, 'msg' => 'OK'];
        
    }

    /**
     * 
     * Obtenemos Reporte de Servicios 
     * 
    */

    public function getReport($data)
    {
       $res = Commaned::where(function($query) use($data) {
 
          if(isset($data['from']))
          {
             $from = date('Y-m-d',strtotime($data['from']));
          }
          else
          {
             $from = null;
          }
 
          if(isset($data['to']))
          {
             $to = date('Y-m-d',strtotime($data['to']));
          }
          else
          {
             $to = null;
          }
 
          if($from)
          {
             $query->whereDate('commaned.created_at','>=',$from);
          }
 
          if($to)
          {
             $query->whereDate('commaned.created_at','<=',$to);
          }
 
       })->orderBy('commaned.id','ASC')->get();
 
       $allData = [];
 
       foreach($res as $row)
       {
 
            // ID
            // Usuario
            // Email
            // Repartidor
            // Origen
            // Destino
            // Cargos de envio
            // Cargos de IVA
            // Total
            // Metodo de pago
            // Imagen de entrega
            // Estatus del pedido.

            // Obtenemos el usuario
            $user = User::find($row->user_id);

            // Obtenemos el repartidor
            $staff = Delivery::find($row->d_boy);

            $allData[] = [
                'id'     => $row->id,
                'date'   => $row->created_at,
                'user'   => isset($user) ? $user->name : 'Indefinido.',
                'email'  => isset($user) ? $user->email : 'Indefinido.',
                'staff'  => isset($staff) ? $staff->name : 'Indefinido',
                'origin' => isset($row->address_origin) ? $row->address_origin : 'Indefinido',
                'destin' => isset($row->address_destin) ? $row->address_destin : 'Indefinido',
                'd_charges' => $row->d_charges,
                'type_trips' => $row->type_trips,
                'total'  => $row->total,
                'payment_method' => $row->payment_method,
                'pic_order' => Asset('upload/order/delivery/'.$row->pic_end_order),
                'status' => $row->status
            ];
       }
 
       return $allData;
    }


    /**
     * 
     * Obtenemos la lista de transacciones que generaon CashBack
     * 
     */
    public function getCashBack($id)
    {
        $res = Commaned::where('user_id',$id)
                ->where('status','6') // Terminado
                ->orderBy('id','DESC')
                ->get();
        $data = [];

        foreach ($res as $key) {
            
            $data[] = [
                'id'            => $key->id,
                'd_charges'     => $key->d_charges,
                'payment_method' => $key->payment_method,
                'uso_monedero'  => $key->uso_monedero,
                'monedero'      => $key->monedero,
                'date'          => date('d-M-Y',strtotime($key->created_at))." | ".date('h:i')
            ];
        }

        return $data;
    }

    public function getHistory($dboy_id = null) {

        if ($dboy_id) {

            $query = Commaned::where('d_boy', $dboy_id)->latest()->get();

            if ($query) {
                $data = [];
                foreach ($query as $key) {
                    $data[] = [
                        'id'                => $key->id,
                        'dboy_id'           => $key->d_boy,
                        'address_origin'    => $key->address_origin,
                        'address_destin'    => $key->address_destin,
                        'total'             => $key->total,
                        'status'            => $this->getStatusText($key->status),
                        'date'              => date('d-M-Y',strtotime($key->created_at))." | ".date('h:i')
                    ];
                }

                return ['data' => $data, 'msg' => 'OK'];
            }

        }

        return ['data' => [], 'msg' => 'Sin historial'];
    }

    private function getStatusText($status) { 
      
        $statusEnum = [
            0 => "Pendiente",
            1 => "Confirmada",
            2 => "Cancelada",
            3 => "Repartidor no encontrado",
            4.5 => "Pedido en camino",
            5 => "Pedido entregado",
            6 => "Pedido calificado"
        ];

        return $statusEnum[$status] ?? "Sin estatus";
    }

    public function getTimeDelivery() {
        // Tiempo estimados
        $admin = Admin::find(1);
        $api_key = $admin->ApiKey_google;

        // Validar y escapar datos de entrada
        $lat_orig = urlencode($this->lat_orig);
        $lng_orig = urlencode($this->lng_orig);
        $lat_dest = urlencode($this->lat_dest);
        $lng_dest = urlencode($this->lng_dest);

        // Construir la URL
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$lat_orig},{$lng_orig}&destinations={$lat_dest},{$lng_dest}&key={$api_key}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        $http_result = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_result == 200) {
            $request = json_decode($output, true);

            //convertimos a minutos

            return  ($request['rows'][0]['elements'][0]['duration']['value']) / 60;
        }  
        return 0;      
    }
}
