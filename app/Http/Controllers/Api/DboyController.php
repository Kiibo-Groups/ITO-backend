<?php

namespace App\Http\Controllers\api;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NodejsServer;

use Illuminate\Http\Request;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\Order;
use App\Models\Commaned;
use App\Language;
use App\Models\Order_staff;
use App\Text;
use App\Models\Admin;
use App\Models\AppUser; 
use App\Models\Bonus;
use App\Models\ClanRequest;
use App\Page;
use App\Balance;
use DB;
use Validator;
use Redirect;
use Excel;
use Stripe;
use JWTAuth;
use Carbon\Carbon;
use App\Models\EmergencyContact;
use App\Models\RegisterKm;

class DboyController extends Controller
{

	public function __construct()
	{
		$this->middleware('delivery:api')->except([
			'chkUser',
			'login',
			'homepage_init',
			'homepage',
			'signup',
			'stripe',
			'getTypeDriver',
			'pages',
			'forgot',
			'verify',
			'updatePassword'
		]);
	}
	
	public function pages()
	{
		try {
			$res = new Page; 
			return response()->json(['status' => true,'data' => $res->getAppData()],200);
		} catch (\Exception $th) {
			return response()->json(['status' => false, 'data' => $th->getMessage()],500);
		}
	}

	/**
     * Solicitud de primera informacion
     */
    public function homepage_init()
	{
		$text    = new Text;
		try { 
			return response()->json([ 
                'status'    => true,
				'text'		=> $text->getAppData(0),
				'admin'		=> Admin::find(1) 
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false, 'data' => $th->getMessage()]);
		}
	}

	public function homepage()
	{
		$res 	 = new Commaned;
		$text    = new Text;

		return response()->json([
			'data' 		=> $res->history_ext(0),
			'events' 	=> $res->history_staff(0),
			'text'		=> $text->getAppData(0),
			'admin'		=> Admin::find(1)
		]);
	}

	public function homepage_ext()
	{
		try {
			$res 	 = new Commaned;
			$text    = new Text;
			$Neworder = Order_staff::where('d_boy', $_GET['id'])->whereIn('status', [0])->count();
			$Ruteorder = Order_staff::where('d_boy', $_GET['id'])->whereIn('status', [1, 3, 4.5])->count();
			return response()->json([
				'data' 		=> $res->history_ext(0),
				'Neworder'  => $Neworder,
				'Ruteorder' => $Ruteorder,
				'events' 	=> $res->history_staff(0),
				'text'		=> $text->getAppData(0),
				'admin'		=> Admin::find(1)
			]);
		} catch (\Exception $th) {
			return response()->json(['data' => 'error', 'error' => $th->getMessage()]);
		}
	}

	public function overview()
	{
		try {
			$res 	 = new Delivery;

			return response()->json([
				'status'    => true,
				'data' 		=> $res->overview(),
				'balance'   => Balance::where('dboy_id',$_GET['id'])->orderBy('created_at','desc')->get(),
				'admin'		=> Admin::find(1),
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false,'data' => 'error', 'error' => $th->getMessage()]);
		}
	}

	public function stripe()
	{

		try {
			Stripe\Stripe::setApiKey(Admin::find(1)->stripe_api_id);

			$res = Stripe\Charge::create ([
					"amount" => $_GET['amount'] * 100,
					"currency" => "USD", // $_GET['currency'], "MXN", "USD"
					"source" => $_GET['token'],
					"description" => $_GET['description']
			]);

			if($res['status'] === "succeeded")
			{	
				// Agregamos el saldo al usuario
				$user = Delivery::find($_GET['user_id']);

				$newSaldo = $user->amount_acum + $_GET['amount'];
				$user->amount_acum = $newSaldo;
				$user->save();

				// Agregamos al balance  
				$balance = new Balance;
				$balance->addNew(null,$_GET['user_id'],$_GET['amount'],1,$res['source']['id']);
				return response()->json(['data' => "done",'id' => $res['source']['id']]);
			}
			else
			{
				// Agregamos al balance  
				$balance = new Balance;
				$balance->addNew(0,$_GET['user_id'],$_GET['amount'],2,'');
				return response()->json(['status' => true,'data' => "error"]);
			}
		} catch (\Exception  $th) {
			return response()->json([
				'status' => false,
				'data' => 'error',
				'error' => $th->getMessage(),
				'amount' => $_GET['amount']
			]);
		}
			
	}

	public function staffStatus($type)
	{
		$res 			= Delivery::find($_GET['user_id']);
		$res->status 	= ($type == true) ? 1 : 0;
		$res->save();

		return response()->json(['data' => true]);
	}

	public function login(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'email' => 'required|string|email',
				'password' => 'required|min:6',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;
			$data = $res->login($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}
			return response()->json($data, 200);
		} catch (\Exception $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function logout()
	{
		$res = new Delivery;
		return response()->json($res->logout(), 200);
	}

	public function signup(Request $Request)
	{
		try {
			$validator = Validator::make($Request->all(), [
				'email' => ['required','string', 'email', 'unique:delivery_boys,email']
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => "El email ya se encuentra registrado", 'errors' => $errors], 200);
			}

			$res = new Delivery;
			return response()->json(['data' => $res->addNew($Request->all(), 'add', 'app')]);
		} catch (\Exception $th) {
			return response()->json(['data' => 'error', 'error' => $th->getMessage()]);
		}
	}

	public function startRide()
	{
		$res 		 = Commaned::find($_GET['id']);
		$res->status = $_GET['status'];
		$res->d_boy  = $_GET['d_boy'];

		if ($res->isr === 0.00 && $res->iva === 0.00) {
			$subtotal = $res->d_charges;
			$iva = $subtotal * 0.16;
			$isr = $res->getISR($subtotal);

			$res->isr = $isr;
			$res->iva = $iva;
			$res->subtotal = $subtotal - ($iva + $isr);

		}

		$res->save();

		$data = [
			'data' => 'done',
			'time_exceeded' => null,
			'extra_charge' => null,
			'extra_charge_iva' => null,
			'extra_charge_isr' => null,
			'iva' => $res->iva ?? null,
			'isr' => $res->isr ?? null,
			'subtotal' => $res->subtotal ?? null,
			'total' => $res->total,
			'd_charges' =>$res->d_charges
		];

		// El viaje ha sido aceptado
		if ($_GET['status'] == 1) {
			// Notificamos al usuario que el conductor acepto el viaje
			app('App\Http\Controllers\Controller')->sendPush("Conductor en camino 😃", "El conductor ha aceptado el viaje y va en camino a tú ubicación.", $res->user_id);

			// Marcamos al conductor ocupado.
			$staff = Delivery::find($res->d_boy);
			$staff->status_send = 0;
			$staff->save();
			// Eliminamos toda la info de la tabla repas
			Order_staff::where('event_id', $_GET['id'])->delete();

			// Registramos al conductor asignado
			$order_Ext = new Order_staff;
			$order_Ext->event_id 	= $_GET['id'];
			$order_Ext->d_boy 		= $_GET['d_boy'];
			$order_Ext->type 		= 1;
			$order_Ext->status 		= '1';
			$order_Ext->save();
		} else if ($_GET['status'] == 4.5) {
			// Notificamos al usuario que su conductor ha marcado el viaje rumbo al destino.
			app('App\Http\Controllers\Controller')->sendPush("¡En rumbo a tu destino! 😃", "¡Excelente! El conductor ha marcado el viaje rumbo al destino. 😃", $res->user_id);

			$order_Ext = Order_staff::where('event_id', $_GET['id'])->first();
			$order_Ext->status = 4.5;
			$order_Ext->save();
			
			$res->start_time = Carbon::now();
			$res->save();
		} else if ($_GET['status'] == 5) {
			Order_staff::where('event_id', $_GET['id'])->delete();

			$staff = Delivery::find($res->d_boy);
			$staff->status_send = 0;
			$staff->save();
			
			$res->end_time = Carbon::now();
			$res->save();

			// Calculamos el tiempo de diferencia 

			$start_time = Carbon::parse($res->start_time);
			$end_time = Carbon::parse($res->end_time);


			// Calcular la diferencia de tiempo en minutos
			$minutesDifference = $end_time->diffInMinutes($start_time);

			$times_delivery = $res->getTimeDelivery();

			// Calcular tiempo excedido

			$time_exceeded = $minutesDifference - $times_delivery;

			$extra_charge = 0;

			// Notificamos al usuario
			app('App\Http\Controllers\Controller')->sendPush("Viaje terminado", "🎉 Tu viaje a finalizado 🎉😃, ayudanos recomendandonos y no te olvides de calificar al conductor por su servicio.", $res->user_id);


			// Registramos los kms total recorrido
			$mileage = new RegisterKm;
			$km = $mileage->createKms($_GET['d_boy'], $_GET['km'], $_GET['id']);

			if ($time_exceeded > 0) {
				// Se obtiene el precio por minuto $min_price y se multiplica por los minutos excedidos
				$min_price = $res->city->min_price;
				$extra_charge = $time_exceeded * $min_price;

				if ($res->extra_charge === 0.00) {
				$iva = $extra_charge * 0.16;
				$isr = $res->getISR($extra_charge);
				$subtotal = $extra_charge - ($iva + $isr);
				$res->iva += $iva;
				$res->isr += $isr;
				$res->subtotal += $subtotal;
				$res->total += $extra_charge;
				$res->time_exceeded = $time_exceeded;
				$res->extra_charge = $extra_charge;
				$res->monedero += $extra_charge;
				$res->save();
				}

				$data = [
					'data' => 'done',
					'time_exceeded' => $res->time_exceeded,
					'extra_charge' => $res->extra_charge,
					'extra_charge_iva' => $iva ?? null,
					'extra_charge_isr' => $isr ?? null,
					'iva' => $res->iva,
					'isr' => $res->isr,
					'subtotal' => $res->subtotal,
					'total' => $res->total,
					'd_charges' =>$res->d_charges
				]; 
			}

			// Agregamos al Monedero electronico
			$user = new AppUser;
			$user->addMoney($res->monedero, $res->user_id, $res->use_mon);

			// Agregamos la comision al repartidor
			$staff = new Delivery;
			$staff->Commset_delivery($res->id, $_GET['d_boy']);

			if ($time_exceeded > 0 && $extra_charge > 0) {
				return response()->json(['data' => $data], 200);
			}

		}
	
		return response()->json(['data' => $data], 200);
	}

	public function rejected(Request $Request)
	{
		try {
			// Reiniciamos el pedido
			$id 		 = $Request->get('id');
			// Damos entre a otro Repartidor
			$chk         = Order_staff::where('event_id', $id);

			if ($chk) {
				Order_staff::where('event_id', $id)->delete();
			}

			return response()->json(['data' => 'done']);
		} catch (\Exception $th) {
			return response()->json(['data' => 'error', 'error' => $th->getMessage()]);
		}
	}

	public function userInfo($id)
	{
		try {
			$count = Commaned::where('d_boy', $id)->where('status', 6)->count();
			$staff = new Delivery;
			return response()->json([
				'status' => true,
				'data' => $staff->getStaff($id),
				'order' => $count
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false ,'data' => 'error', 'error' => $th->getMessage()]);
		}
	}

	public function updateLocation(Request $Request)
	{
		if ($Request->get('user_id') > 0) {
			$add 			= Delivery::find($Request->get('user_id'));
			$add->lat 		= $Request->get('lat');
			$add->lng 		= $Request->get('lng');
			$add->save();
		}

		return response()->json(['data' => true]);
	}

	public function getPolylines()
	{
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $_GET['latOr'] . "," . $_GET['lngOr'] . "&destination=" . $_GET['latDest'] . "," . $_GET['lngDest'] . "&mode=driving&key=" . Admin::find(1)->ApiKey_google;
		$max      = 0;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		$http_result = $info['http_code'];
		curl_close($ch);


		$request = json_decode($output, true);

		return response()->json($request);
	}

	public function chkNotify()
	{
		$content = ["en" => "Prueba de audio, Notificaciones Push"];
		$head 	 = ["en" => "Notificacion Comercios"];


		$fields = array(
			'app_id' => "fd78b049-75a1-42da-9c70-4468205f1e3a",
			'included_segments' => array('All'),
			// 'filters' => [$daTags],
			'data' => array("foo" => "bar"),
			'contents' => $content,
			'headings' => $head,
			'android_channel_id' => '80321c11-2ef0-4c8a-813b-7456492d3db9'
		);


		$fields = json_encode($fields);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: Basic ZWIwODMwNDUtNWM2Mi00YzFhLTg0MGEtODVhOWYyMTk0YzM4'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);



		$req = json_decode($response, TRUE);

		return response()->json(['data' => $req]);
	}

	public function uploadpic_order()
	{
		//Rename and move the file to the destination folder 
		// Input::file('file')->move($destinationPath,$newImageName);

		$target_path = "upload/user/delivery/";

		$target_path = $target_path . basename($_FILES['file']['name']);

		move_uploaded_file($_FILES['file']['tmp_name'], $target_path);

		return response()->json(['data' => "echo"]);
	}

	public function notifyClient(Request $Request)
	{
		try {
			$user 	= $Request->get('user_id');
			$title 	= $Request->get('title');
			$msg  	= $Request->get('msg');

			return response()->json([
				'data' => app('App\Http\Controllers\Controller')->sendPush($title, $msg, $user),
			]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'error', 'err' => $e->getMessage()]);
		}
	}

	public function rateComm_event(Request $Request)
	{
		try {
			$type = $Request->get('type_order');

			$req = new Commaned;
			return response()->json(['data' => $req->rateComm_event($Request->all())]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail', 'err' => $e->getMessage()]);
		}
	}

	public function rateCommDboy_event(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'oid' => 'required',
				'star_user' => 'required',
				'comment_dboy' => 'string'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$req = new Commaned;

			$data = $req->rateCommDboy_event($request->all());


			if (empty($data['data'])) {
				return response()->json($data, 400);
			}
			
			return response()->json(['data' => $data]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail', 'err' => $e->getMessage()]);
		}
	}

	public function chkUser(Request $Request)
	{
		$res = new Delivery;
		return response()->json($res->chkUser($Request->all()));
	}

	public function verifyDocuments(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;
			return response()->json($res->verifyDocuments($request), 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function uploadDocuments(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
				'type' => 'required',
				'camera_file' => 'string'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;

			$data = $res->uploadDocuments($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function updateImage(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'dboy_id' => 'required',
				'camera_file' => 'string'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;

			$data = $res->updateImage($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function updateRFC(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
				'rfc' => ['required', 'regex:/^([A-Z]{4})([0-9]{6})([A-Z0-9]{3})$/'],
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;

			$data = $res->updateRFC($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function updateData(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
				'email' => ['required','string', 'email', 'unique:delivery_boys,email,'.$request->id],
				'city_id' => 'required',
				'type_driver' => 'required',
				'type_edriver' => 'required',
				'max_range_km' => 'required',
				'brand' => 'required',
				'model'=> 'required',
				'color' => 'required',
				'number_plate' => 'required',
				'passenger' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Delivery;

			$data = $res->updateData($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);

		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function emergencyContacts($dboy_id) {
		try {
			$res = new EmergencyContact;
			$data = $res->emergencyContacts($dboy_id, null);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function createEmergencyContacts(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'dboy_id' => 'required',
				'phone' => 'required',
				'name' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new EmergencyContact;

			$data = $res->createEmergencyContacts($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);

		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function getKms($dboy_id) {
		try {
			$res = new RegisterKm;
			$data = $res->getKms($dboy_id);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['status' => true,'data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function getTypeDriver()
	{
		try {

			$DeliveryType = DeliveryType::where('status',0)->get();
			$data = [];

			foreach ($DeliveryType as $key => $value) {
				
				$data[] = [
					'id'   => $value->id,
					'icon' => asset('upload/driver_type/'.$value->icon),
					'type' => $value->type,
					'name' => $value->name
				];
			}

			return response()->json(['status' => true,'data' => $data], 200);
		} catch (\Throwable $th) {
			return response()->json(['status' => false,'data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	/*
	* Get Bonuses
	*/
	public function getBonuses($dboy_id) {
		try {
			$res = new Bonus;
			$data = $res->getBonuses($dboy_id);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	/**
	 * Get Biometrics
	 */
	public function getBiometrics(Request $request)
	{
		  
		$nodeJS = new NodejsServer;
		return response()->json($nodeJS->getBiometrics($request->all()));
	}

	public function getHistory($dboy_id) {
		try {
			$res = new Commaned;
			$data = $res->getHistory($dboy_id);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function createStartKm() {
		try {
			$mileage = new RegisterKm;
			$data = $mileage->createKms($_GET['dboy_id'], $_GET['km'], 0);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function sendLocationNotification($user_id) {
		app('App\Http\Controllers\Controller')->sendPush("¡Tu conductor ya está en tu ubicación! 🚕", "¡Excelente! El conductor ha marcado que esta a fuera de tu domicilio. 😃", $user_id);

		return response()->json(['data' => 'done']);
	}

	public function createClanRequests(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'dboy_id' => 'required',
				'clan_id' => 'required'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => 'clan_id y dboy_id son requeridos.'], 400);
			}

			$validate = ClanRequest::where('dboy_id', $request->dboy_id)->where('clan_id', $request->clan_id)->whereIn('status', ['Aceptada', 'Pendiente'])->count();

			if ($validate > 0) {
				return response()->json(['data' => [],  'msg' => 'Ya cuentas con una solicitud en este clan'], 400);
			}

			$res = new ClanRequest;
			$data = $res->createClanRequests($request);

			return response()->json($data, 200);

		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function checkClanRequests(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'dboy_id' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => 'dboy_id son requeridos.'], 400);
			}

			$validate = ClanRequest::where('dboy_id', $request->dboy_id)->whereIn('status', ['Aceptada', 'Pendiente'])->first();

			if ($validate) { 
				return response()->json(['data' => ['status' => $validate->status], 'clan_id' => $validate->clan_id, 'msg' => 'Ya cuentas con una solicitud al clan ' . $validate->clan->name], 200);
			}

			return response()->json(['data' => [],  'msg' => 'Ok'], 200);

		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function forgot(Request $Request)
	{
		$res = new Delivery;
		// return response()->json($Request->all());
		return response()->json($res->forgot($Request->all()));
	}

	public function verify(Request $Request)
	{
		$res = new Delivery;

		return response()->json($res->verify($Request->all()));
	}

	public function updatePassword(Request $Request)
	{
		$res = new Delivery;

		return response()->json($res->updatePassword($Request->all()));
	}
	/**
	 * Generacion de Msg vi Whatsapp
	 */
	public function WhatsappLinkGenerator(Request $request)
	{

		try {
			$input = $request->all();
			$userSOS = Delivery::find($input['idUser']);
			$contact = EmergencyContact::find($input['contact']);

			$dnl = "\r\n";
			$ddnl = "\n\n";
			$nl="\n";
			$tabSpace="      ";

			$msg = 'Hola '.$contact->name.$dnl;
			$msg .= "Mensaje creado el: ".date('d-M-Y',strtotime(now()))." | ".date('h:i:A',strtotime(now())).$ddnl;

			$msg .= "Estoy en un viaje en el aplicativo de ITO Conductores".$ddnl;
			$msg .= "**** y creo que estoy en peligro ****".$dnl;

			$msg .= $nl;

			$msg .= "Nombre: ".$userSOS->name.$dnl;
			$msg .= "Telefono: ".$userSOS->phone.$dnl; 

			$msg .= "Te envio mis coordenadas actuales".$dnl;

			$msg .= $nl;
			$msg .= "(https://www.google.com/maps?q=".$userSOS->lat.",".$userSOS->lng.")".$nl;
			$msg .= $nl;
 
			// Quitamos espacios del telefiono
			$phone = str_replace(' ','',$contact->phone);
			$phone = str_replace('-','',$contact->phone);
			$phone = str_replace('+','',$contact->phone);

			$url = 'https://wa.me/+521'.$phone.'?text='.urlencode($msg);

			return response()->json(['status' => true,'data' => $url], 200);
		} catch (\Throwable $th) {
			return response()->json(['status' => false, 'data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function cancelCommDboy_event(Request $request) {
		try {
			$res = new Commaned;
			$data = $res->cancelCommDboy_event($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function verifyClanDboy($id) {
		try {
			$res = new Delivery;
			$data = $res->verifyClanDboy($id);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function checkClanRequestsAll(Request $request) {
		try {
			$res = new ClanRequest;
			$data = $res->checkClanRequestsAll($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function statusAcceptClan($id) {
		try {
			$res = new ClanRequest;
			$data = $res->statusAcceptClan($id);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function statusRejectClan($id) {
		try {
			$res = new ClanRequest;
			$data = $res->statusRejectClan($id);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

}
