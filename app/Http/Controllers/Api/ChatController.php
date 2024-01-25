<?php 
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NodejsServer;
use App\Models\{Group};
use App\Models\Chat;
use App\Models\AppUser;
use App\Models\Delivery;

use Validator;

class ChatController extends Controller {

	public function __construct()
	{
		$this->middleware('delivery:api')->except(['getChats','getClanChat']); // 
	}

	public function groups() {
		try {

			$res = new Group;

			$data = $res->getGroups();

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function getGroupUnique($user_id, $clan)
	{
		try {

			$res = new Group;

			$data = $res->getGroupUnique($user_id,$clan);

			return response()->json(['data' => $data, 'msg' => 'OK'], 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);
		}
	}

	public function createGroup(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'created_by' => 'required',
				'name' => 'required|min:4',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Group;

			$data = $res->createGroups($request);
			
			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);

		}
	}


	public function groupLikes(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Group;
			$data = $res->groupLikes($request);
			return response()->json($data, 200);
		
			
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);

		}
	}

	public function groupTrips(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required',
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Group;
			$data = $res->groupTrips($request);
			return response()->json($data, 200);
		
			
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);

		}
	}

	public function groupAddMember(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				'id' => 'required', // id del clan
				'driver_id' => 'required', // id del conductor
				'type' => 'required' // tipo de miembro
			]);

			if ($validator->fails()) {
				$errors = $validator->errors();
				return response()->json(['data' => [], 'msg' => $errors], 400);
			}

			$res = new Group;
			$data = $res->groupAddMember($request);

			return response()->json($data, 200);

		} catch (\Exception $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);

		}
	}

	public function groupAllMembers($id) {
		try {
			$res = new Group;
			$data = $res->groupAllMembers($id);

			return response()->json($data, 200);
		} catch (\Throwable $th) {
			return response()->json(['data' => [], 'msg' => $th->getMessage()], 500);

		}
	}

	/**
	 * Generacion de PuhserId para Chats
	 */
	public function createIdPusher($id)
	{
		try {
			$add			= Carrier::find($id);
			$add->id_pusher = substr(md5($add->id.$add->name),0,15);
			$add->save();

			return response()->json([
				'status' => true,
				'data' => $add
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false,'data' => $th->getMessage()]);
		}
	}

	/**
	 * 
	 * Funciones del chat
	 * 
	 */
	public function getChats($id)
	{
		try {

			$req = new Chat; 
			
			$req = Chat::where('sender',$id)->orderBy('id','DESC')->get()->unique('clan_id');
			$data = [];
			$news_msgs = 0;
			$chats_norms = 0;
			$chats_accept = 0;

			foreach ($req as $key) {
				
				// Verificamos si hay algun chat sin leer con este usuario
				$viewer_chats = Chat::where('clan_id',$key->clan_id)
				->where('sender',$id) 
				->where('viewer',0)->count();

				// Sumamos todos los chats
				$news_msgs += $viewer_chats;
 
				// Obtenemos el Clan
				$srvs = Group::find($key->clan_id);
				
				if ($srvs) {
				$data_clans = [
					'id' => $srvs->id,
					'name' => $srvs->name, 
					'description' => $srvs->description,
					'created_by' => [
						'id' => $srvs->dboy->id,
						'name' => $srvs->dboy->name,
						'email' => $srvs->dboy->email,
						'profile' =>  asset('upload/profile/' . $srvs->dboy->profile)
					],
					'members' => $srvs->members->count(),
					'like' => $srvs->likes,
					'trips' => $srvs->trips,
					'image' => asset('upload/clans/' . $srvs->image),
				];
				} 

				// Rellenamos la informacion
				// Obtenemos el sender
				if($key->sender == 0){ // Msg del sistema
					$sender = 2;
				}else {
					if ($key->sender == $id) { // Lo envio el solicitante
						$sender = 1;
					}else {
						$sender = 0;
					}
				}

				$dboy = Delivery::find($key->sender);
				$profile = asset('upload/profile/' . $dboy->profile);
				$data[] = [
					'dboy' => $dboy,
					'profile_dboy' => $profile,
					'clan' => $data_clans,
					'sender' => $sender,
					'viewers' => $viewer_chats,  
					'type_chat' => $key->type_chat,
					'msg' => $key->msg,
					'attach' => asset('upload/chats/'.$key->attachment), 
					'viewer' => $key->viewer,
					'status' => $key->status,
					'date' => $key->created_at->diffForHumans(),
				];
			}

			return response()->json([
				'status' => true,
				'data' => $data, 
				'news_msgs' => $news_msgs
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false,'data' => $th->getMessage()]);
		}
	}

	public function getClanChat($user_id,$clan_id)
	{
		
		try {
			$chats   = new Chat;
			$clans   = new Group;
			// Obtenemos el Clan
			$srvs = Group::find($clan_id); 
			// Validamos si el conductor es miembro de este clan
			$memberActive = $clans->ValidateMemberActive($srvs->id, $user_id); 
			$data_servs = [
				'id'      => $srvs->id, 
				'id_pusher' => $srvs->id_pusher, 
                'name' => $srvs->name,
                'memberActive' => $memberActive,
                'description' => $srvs->description,
                'created_by' => [
                    'id' => $srvs->dboy->id,
                    'name' => $srvs->dboy->name,
                    'email' => $srvs->dboy->email,
                ],
                'members' => $srvs->members->count(),
                'like' => $srvs->likes,
                'trips' => $srvs->trips,
			];

			$chats_clans = [];
			if ($memberActive) {
				$chats_clans = $chats->getChatClan($user_id ,$clan_id);
			}

			return response()->json([
				'status'    => true, 
				'clan' 		=> $data_servs, 
				'chats' 	=> $chats_clans
			]);	
		} catch (\Exception $th) {
			return response()->json(['status' => false, 'data' => $th->getMessage()]);
		}
	}

	public function sendChat(Request $Request)
	{
		try {
			$input = $Request->all();
			$lims_data_chat = new Chat;
			$lims_data_chat->create($input);

			// Notificacion Push
			// app('App\Http\Controllers\Controller')->sendPush("Nuevo mensaje","Nuevo mensaje del Clan #".$input['clan_id'],$input['app_user_id']);

			return response()->json([
				'status' => true
			]);
		} catch (\Exception $th) {
			return response()->json(['status' => false,'data' => $th->getMessage()]);
		}
	}

	public function getChat($user_id,$channel,$event)
	{
		try {
			// Enviamos Notificaciones
			$noty = new NodejsServer;
			$req_not = [
				'channel' => $channel,
				'event'   => $event
			];

			// $chat_viewer = Chat::where('dboy_id',$user_id)->where('sender',0)->get();
            // foreach ($chat_viewer as $key) {
            //     $key->viewer = 1;
            //     $key->save();
            // };

			return response()->json(['data' => $noty->sendChatServer($req_not)]);
		} catch (\Exception $th) {
			return response()->json(['data' => "error",'error' => $th->getMessage()]);
		}
	}
}