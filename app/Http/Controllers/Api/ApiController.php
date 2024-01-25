<?php namespace App\Http\Controllers\api;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NodejsServer;

use Illuminate\Http\Request;
use Auth;
use App\City;
use App\OfferStore;
use App\Offer;
use App\Cart;
use App\CartCoupen;
use App\Models\AppUser;
use App\Order;
use App\Models\Order_staff;
use App\OrderAddon;
use App\OrderItem;
use App\Lang;
use App\Models\Rate;
use App\Slider;
use App\Banner;
use App\Address;
use App\Models\Admin;
use App\Page;
use App\Language;
use App\Text;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\CategoryStore;
use App\Opening_times;
use App\Balance;
use App\Models\Commaned;
use DB;
use Validator;
use Redirect;
use Excel;
use Stripe;
use Twilio\Rest\Client;

use App\Models\{EmergencyContact, Bonus};

class ApiController extends Controller {


	public function welcome()
	{
		$res = new Slider;

		return response()->json(['data' => $res->getAppData()]);
	}

	public function city()
	{
		$city = new City;
        $text = new Text;
        $lid =  isset($_GET['lid']) && $_GET['lid'] > 0 ? $_GET['lid'] : 0;

		return response()->json(['data' => $city->getAll(0),'text'		=> $text->getAppData($lid)]);
	}

	public function getAdmin()
	{
		
		$data = [
			'admin'		=> Admin::find(1),
		];
	
		return response()->json(['data' => $data]);
	}

	public function updateCity()
	{
		$res = AppUser::find($_GET['id']);
		$res->last_city = $_GET['city_id'];
		$res->save();

		return response()->json(['data' => 'done']);
	}

	public function lang()
	{
		$res = new Language;

		return response()->json(['data' => $res->getWithEng()]);
	}

	public function homepage($city_id)
	{
		$text    = new Text;
		$offer   = new Offer;

		$data = [
			'text'		=> $text->getAppData($_GET['lid']),
			'admin'		=> Admin::find(1),
			'offers'    => $offer->getAll(0),
		];

		return response()->json(['data' => $data]);
	}

	public function getStore($id)
	{
		
		$store   = new User;
		
		
		return response()->json(['data' => $store->getStore($id)]);
	}

	public function GetInfiniteScroll($city_id) {
		
		$store   = new User;
		
		$data = [
			'store'		=> $store->GetAllStores($city_id)
		];

		return response()->json(['data' => $data]);
	}

	public function getTypeDelivery($id)
	{
		$user = new User;
		return response()->json([$user->getDeliveryType($id)]);
	}

	public function search($query,$type,$city)
	{
		$user = new User;

		return response()->json(['data' => $user->getUser($query,$type,$city)]);
	}

	public function SearchCat($city_id)
	{
		$user = new User;
		return response()->json(['cat'=> CategoryStore::find($_GET['cat'])->name,'data' => $user->SearchCat($city_id)]);
	}


	public function addToCart(Request $Request)
	{
		$res = new Cart;

		return response()->json(['data' => $res->addNew($Request->all())]);
	}

	public function updateCart($id,$type)
	{
		$res = new Cart;

		return response()->json(['data' => $res->updateCart($id,$type)]);
	}

	public function cartCount($cartNo)
	{
	  if(isset($_GET['user_id']) && $_GET['user_id'] > 0)
	  {
	  	$order = Order::where('user_id',$_GET['user_id'])->whereIn('status',[0,1,1.5,3,4])->count();
	  }
	  else
	  {
	  	$order = 0;
	  }

	  $cart = new Cart;

	  return response()->json([ 
			'data'  => Cart::where('cart_no',$cartNo)->count(),
			'order' => $order,
			'cart'	=> $cart->getItemQty($cartNo) 
	  	]);
	}

	public function getCart($cartNo)
	{
		$res = new Cart;

		return response()->json(['data' => $res->getCart($cartNo)]);
	}

	public function getOffer($user_id)
	{
		try {
			$res = new Offer; 
			return response()->json(['data' => $res->getOffer($user_id)]);
		} catch (\Exception $th) {
			return response()->json(['data' => 'fail','msg' => $th->getMessage()]);
		}
	}

	public function applyCoupen($id,$cartNo)
	{
		$res = new CartCoupen;

		return response()->json($res->addNew($id,$cartNo));
	}

	public function signup(Request $Request)
	{
		try {
			$res = new AppUser; 
			return response()->json($res->addNew($Request->all(),'add'));
		} catch (\Exception $th) {
			return response()->json(['data' => 'fail','msg' => $th->getMessage()]);
		}
		
	}

	public function sendOTP(Request $Request)
	{
		$phone = $Request->phone;
		$hash  = $Request->hash;

		return response()->json(['otp' => app('App\Http\Controllers\Controller')->sendSms($phone,$hash)]);
	}

	public function sendOtpSms(Request $Request)
	{
			// Your Account SID and Auth Token from twilio.com/console
			$account_sid = 'ACdf8b57f206c8945767af31e320a884e6';
			// In production, these should be environment variables. E.g.:
			$auth_token = "a49c7ad019b683ce6b5293363f7d2168";

			// A Twilio number you own with SMS capabilities
			$twilio_number = "+12056512299";
			$phone_send = $Request->get('phone');
			$reg = "#^\(?\d{2}\)?[\s\.-]?\d{4}[\s\.-]?\d{4}$#";

			if (preg_match($reg, $phone_send)) {
				
				$ot_1     = mt_rand(0,50);
				$ot_2     = mt_rand(50,100);
				$ot_3     = mt_rand(100,150);
				$ot_4     = mt_rand(150,200);

				$otp_send = substr($ot_1.$ot_2.$ot_3.$ot_4,0,4);

				$client = new Client($account_sid, $auth_token);
				$client->messages->create(
					// Where to send a text message (your cell phone?)
					'+521'.$phone_send,
					array(
						'from' => $twilio_number,
						'body' => '<#> Tu Codigo Treiber '.$otp_send
					)
				);

				return response()->json(['data' => 'done', 'otp' => $otp_send]);
			}else {
				return response()->json(['data' => 'phone_not_valid']);
			}
	}


	public function SignPhone(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->SignPhone($Request->all()));
	}

	public function chkUser(Request $Request)
	{
		$res = new AppUser;
		return response()->json($res->chkUser($Request->all()));
	}

	public function login(Request $Request)
	{
		try {
			$res = new AppUser; 
			return response()->json($res->login($Request->all()));
 		} catch (\Exception $th) {
			return response()->json(['data' => 'fail','err' => $th->getMessage()]);
		}
	}

	public function Newlogin(Request $Request)
	{
		try {
			$res = new AppUser;
			return response()->json($res->Newlogin($Request->all()));
		} catch (\Exception $th) {
			return response()->json(['msg' => 'error','error' => $th->getMessage()]);
		}
	}

	public function forgot(Request $Request)
	{
		$res = new AppUser;
		// return response()->json($Request->all());
		return response()->json($res->forgot($Request->all()));
	}

	public function verify(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->verify($Request->all()));
	}

	public function updatePassword(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->updatePassword($Request->all()));
	}

	public function loginFb(Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->loginFb($Request->all()));
	}

	public function getAddress($id)
	{
		$address = new Address;
		$cart 	 = new Cart;

		$data 	 = [
		'address'	 => $address->getAll($id),
		'Comercio'   => User::find($_GET['store']),
		'total'   	 => $cart->getCart($_GET['cart_no'])['total'],
		'c_charges'  => $cart->getCart($_GET['cart_no'])['c_charges']
		];

		return response()->json(['data' => $data]);
	}

	public function getAllAdress($id)
	{
		$address = new Address;
	
		return response()->json(['data' => $address->getAll($id)]);
	}

	public function addAddress(Request $Request)
	{
		$res = new Address;

		return response()->json($res->addNew($Request->all()));
	}

	public function removeAddress($id)
	{
		$res = new Address;
		return response()->json($res->Remove($id));
	}

	public function GetNearbyCity()
	{
		$city = new City;
        $text = new Text;
        $lid =  isset($_GET['lid']) && $_GET['lid'] > 0 ? $_GET['lid'] : 0;

		return response()->json([
			'data' => $city->GetNearbyCity(0),
			'text' => $text->getAppData($lid)
		]);
	}

	public function searchLocation(Request $Request)
	{
		$city = new City;
		return response()->json([
			'citys' => $city->getAll()
		]);

	}

	public function order(Request $Request)
	{
		$res = new Order;

		return response()->json($res->addNew($Request->all()));
	}

	public function userinfo($id)
	{
		$comm = new Commaned;
		$user = AppUser::find($id);
		return response()->json([
			'data' => $user,
			'user_profile' => asset('upload/user_profile/' . $user->image_pic),
			'balance' => Balance::where('user_id',$id)->get(),
			'cashBack' => $comm->getCashBack($id)
		]);
	}

	public function updateInfo($id,Request $Request)
	{
		$res = new AppUser;

		return response()->json($res->updateInfo($Request->all(),$id));
	}

	public function cancelOrder($id,$uid)
	{
		$res = new Order;

		return response()->json($res->cancelOrder($id,$uid));
	}


	public function rate(Request $Request)
	{
		$rate = new Rate;

		return response()->json($rate->addNew($Request->all()));

	}

	public function pages()
	{
		$res = new Page;

		return response()->json(['data' => $res->getAppData()]);
	}

	public function myOrder($id)
	{
		$req = new Commaned;

		return response()->json([
			'events' 	=> $req->history($id)
		]);
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
				$user = AppUser::find($_GET['user_id']);

				$newSaldo = $user->saldo + $_GET['amount'];
				$user->saldo = $newSaldo;
				$user->save();

				// Agregamos al balance  
				$balance = new Balance; 
				$balance->addNew($_GET['user_id'],0,$_GET['amount'],1,$res['source']['id']);
				return response()->json(['data' => "done",'id' => $res['source']['id']]);
			}
			else
			{
				// Agregamos al balance  
				$balance = new Balance;
				$balance->addNew($_GET['user_id'],0,$_GET['amount'],2,'');
				return response()->json(['data' => "error"]);
			}
		} catch (\Exception  $th) {
			return response()->json([
				'data' => 'error',
				'error' => $th->getMessage(),
				'amount' => $_GET['amount']
			]);
		}
			
	}

	public function getStatus($id)
	{
		$order = Order::find($id);
		$dboy  = Delivery::find($order->d_boy);
		$store = User::find($order->store_id);

		return response()->json(['data' => $order,'dboy' => $dboy, 'store' => $store]);
	}
 

	public function getAllStaffs()
	{
		return response()->json([
			'data' =>  Delivery::where('status',0)->get()
		]);
	}

	public function getChat($id)
	{
		// $chat  = new Chat;
		$op_time = new Opening_times;
		return response()->json(['chat' => $op_time->ViewTime($id)]);
 
			
	}

	public function sendChat(Request $Request)
	{
		$chat = new Chat;
		return response()->json($chat->addNew($Request->all()));
	}

	public function deleteOrders (Request $Request)
	{
		$items  = $Request->all()['SendChk'];

		for ($i=0; $i < count($items); $i++) { 
			Order::find($items[$i])->delete();
			Order_staff::where('order_id',$items[$i])->delete();
			OrderAddon::where('order_id',$items[$i])->delete();
			OrderItem::where('order_id',$items[$i])->delete();
		}	

		return response()->json(['data' => 'done']);
	}

	public function setLocationavailability(Request $Request)
	{
		$req = new City;

		return response()->json($req->chkLocationavailability($Request->all()));
	}

	public function viewNearbyDrivers(Request $Request)
	{
		$req = new Delivery;
		return response()->json($req->getNearby());
	}

	public function demoCronNodejs()
	{
		$req = new NodejsServer;
		$data = [
			'id_order' => 448
		];
		
		$req->NewOrderComm($data);
	}

	/**
	 * Mandaditos
	*/
	public function OrderComm(Request $Request)
	{
		try {
			$res = new Commaned;
			return response()->json($res->addNew($Request->all()));
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	public function ViewCostShipCommanded(Request $Request)
	{
		try {
			$req = new Commaned; 
			$delivery = new DeliveryType;
			return response()->json([
				'data' => $req->Costs_shipKM($Request->all()),
				'drivers' => $delivery->getAll(1)
			]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	public function chkEvents_comm($id)
	{
		try {
			$req = new Commaned;
			return response()->json(['data' => $req->chkEvents_comm($id)]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	public function chk_comm($id)
	{
		
		try {
			$req = new Commaned;
			return response()->json(['data' => $req->chk_comm($id)]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	public function chkEvents_staffs(Request $Request)
	{
		// Reseteamos
		$event = Commaned::find($Request->get("id"));
		$event->status = 0;
		$event->save();

		// Cambiamos el status en FB 
        $fb_server = new NodejsServer;
        $dat_s = array(
            'external_id' 	=> $event->external_id,
            'status' 		=> $event->status,
            'change_from'   => 'user_app'
        );

        $fb_server->orderStatus($dat_s); 
		// Volvemos a solicitar repartidores
		$req_staff = [
            'id_order' => $event->id
        ];
		$req = new NodejsServer;
		return response()->json(['data' => $req->NewOrderComm($req_staff)]);
	}

	public function getNearbyEvents($id)
	{
		$req = new Commaned;
		return response()->json(['data' => $req->getNearby($id)]);
	}

	public function getPolylines()
	{
		$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$_GET['latOr'].",".$_GET['lngOr']."&destination=".$_GET['latDest'].",".$_GET['lngDest']."&mode=driving&key=".Admin::find(1)->ApiKey_google;
		$max      = 0;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec ($ch);
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close ($ch);


		$request = json_decode($output, true);

		return response()->json($request);
	}

	public function getServiceZone($lat,$lng)
	{
		try {
			// Obtenemos la ciudad en la que se encuentra
			$req 		= new Commaned;  
			return response()->json([
				'data' => $req->getServiceZone($lat,$lng)
			]);
		} catch (\Exception $th) {
			return response()->json(['data' => 'fail','err' => $th->getMessage()]);
		}
	}

	public function setStaffEvent($event_id,$dboy)
	{
		try {
			$req = new Commaned;
			return response()->json(['data' => $req->setStaffEvent($event_id,$dboy)]);	
		} catch (\Exception $th) {
			return response()->json(['data' => ['status' => 'error'],'err' => $th->getMessage()]);
		}
	}

	public function delStaffEvent($event_id)
	{
		$req = new Commaned;
		return response()->json(['data' => $req->delStaffEvent($event_id)]);
	}

	public function cancelComm_event($event_id)
	{
		$req = new Commaned;
		return response()->json(['data' => $req->cancelComm_event($event_id)]);
	}

	public function finallyRate(Request $Request)
	{
		try {
			$req = new Commaned;
			return response()->json(['data' => $req->finallyRate($Request->all())]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	public function rateComm_event(Request $Request)
	{
		try {
			$req = new Commaned;
			return response()->json(['data' => $req->rateComm_event($Request->all())]);
		} catch (\Exception $e) {
			return response()->json(['data' => 'fail','err' => $e->getMessage()]);
		}
	}

	/**
	  * 
	  * Solcitud de repartidores cercanos
	  *
	 */

	public function getNearbyStaffs($order,$type_staff)
	{
		// Obtenemos repartidores Mas cercanos
		$delivery = new Delivery;
		return response()->json(['data' => $delivery->getNearby($order, $type_staff)]);
	}

	public function setStaffOrder($order, $dboy)
	{
		// Chequeo de pedido y registro de repartidores
		$delivery = new Delivery;
		return response()->json(['data' => $delivery->setStaffOrder($order,$dboy)]);	
	}

	public function delStaffOrder($order)
	{
		// Chequeo de pedido y registro de repartidores
		$delivery = new Delivery;
		return response()->json(['data' => $delivery->delStaffEvent($order)]);	
	}

	public function updateStaffDelivery($staff, $external_id)
	{
		$staff = Delivery::find($staff);

		$staff->external_id = $external_id;
		$staff->save();

		return response()->json(['data' => 'done']);
	}

	public function emergencyContacts($user_id) {
		try {
			$res = new EmergencyContact;
			$data = $res->emergencyContacts(null, $user_id);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function createEmergencyContact(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'user_id' => 'required',
				'phone' => 'required',
				'name' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new EmergencyContact;

			$data = $res->createEmergencyContact($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);

		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	
	/**
	 * Generacion de Msg vi Whatsapp
	 */
	public function WhatsappLinkGenerator(Request $request)
	{

		try {
			$input = $request->all();
			$staff = Delivery::find($input['idDboy']);
			$userSOS = AppUser::find($input['idUser']);
			$contact = EmergencyContact::find($input['contact']);

			$dnl = "\r\n";
			$ddnl = "\n\n";
			$nl="\n";
			$tabSpace="      ";

			$msg = 'Hola '.$contact->name.$dnl;
			$msg .= "Mensaje creado el: ".date('d-M-Y',strtotime(now()))." | ".date('h:i:A',strtotime(now())).$ddnl;

			$msg .= "Estoy en un viaje en el aplicativo de VAGOMX".$ddnl;
			$msg .= "**** y creo que estoy en peligro ****".$dnl;

			$msg .= $nl;

			$msg .= "Mi Nombre: ".$userSOS->name.$dnl;
			$msg .= "Mi Telefono: ".$userSOS->phone.$dnl; 

			$msg .= "Nombre del conductor: ".$staff->name.$dnl;
			$msg .= "Telefono del conductor: ".$staff->phone.$dnl; 
			$msg .= "Número de placas: ".$staff->number_plate.$dnl; 

			$msg .= "Te envio mis coordenadas actuales".$dnl;

			$msg .= $nl;
			$msg .= "(https://www.google.com/maps?q=".$staff->lat.",".$staff->lng.")".$nl;
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

	public function updateImage(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'user_id' => 'required',
				'camera_file' => 'string'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new AppUser();

			$data = $res->updateImage($request);

			if (empty($data['data'])) {
				return response()->json($data, 400);
			}

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}
}
