<?php

namespace App\Models;

use App\Http\Controllers\Api\ApiController;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Validator;
use Mail;
use DB;

class Chat extends Authenticatable
{
    protected $table = 'chats';

    protected $fillable =[ 
        'clan_id', // Id del clan
        'type_chat', // 0 => solo texto, 1 => imagen, 2 => video, 3 => audio, 4 => documentos
        'msg', // Mensaje
        'attachment',
        'sender',  // 0 => Msg Ajeno, 1 => Msg Propio, 2 => Msg del sistema
        'viewer', // Visto
        'status', // Archivado
    ];

    /*
    |--------------------------------------
    |Obtenemos todos los chats de un Clan
    |--------------------------------------
    */
    public function getChatClan($user_id ,$clan_id)
    {
        $chats = Chat::where('clan_id',$clan_id)->get();
        $data = [];

        foreach ($chats as $key) {

            // Obtenemos el sender
            if($key->sender == 0){ // Msg del sistema
                $sender = 2;
            }else {
                if ($key->sender == $user_id) { // Lo envio el solicitante
                    $sender = 1;
                }else {
                    $sender = 0;
                }
            }
            
            $data[] = [
                'dboy' => Delivery::find($key->sender), 
                'sender' => $sender,
                'type_chat' => $key->type_chat,
                'msg' => $key->msg,
                'attach' => Asset('upload/chats/'.$key->attachment), 
                'viewer' => $key->viewer,
                'status' => $key->status,
                'date' => $key->created_at->diffForHumans(),
            ];
        }
         
        return $data;
    }
}