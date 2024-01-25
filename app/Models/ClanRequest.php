<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClanRequest extends Model {

    protected $table = 'clan_requests';

    protected $fillable = [
        'clan_id',
        'dboy_id',
        'status'
    ];

    public function dboy() {
        return $this->belongsTo(Delivery::class, 'dboy_id');
    }

    public function clan() {
        return $this->belongsTo(Group::class, 'clan_id', 'id');
    }

    public function createClanRequests($request) {

        $query = new ClanRequest;
        $query->clan_id = $request->clan_id;
        $query->dboy_id = $request->dboy_id;
        $query->status = 'Pendiente';
        $query->save();

        return ['data' => [], 'msg' => 'Solicitud enviada con éxito'];

    }

    public function checkClanRequestsAll($request) {

          
        $group = GroupMember::where('group_id', $request->clan_id)
            ->where('member_id', $request->dboy_id)->where('type', 'LEADERS')->first();

        if (!$group) {
            return ['data' => [], 'msg' => 'Tienes ser lider del clan'];
        }

        $query = ClanRequest::with('clan')
            ->where('clan_id', $group->group_id)
            ->where('status', 'Pendiente')
            ->get();
        $data = [];
    
        if ($query->isEmpty()) {
            return ['data' => [], 'msg' => 'No hay solicitudes para unirse a este clan'];
        }
    
        foreach ($query as $item) {
            $data[] = [
                'request_id' => $item->id,
                'clan_id' => $item->clan->id,
                'clan_name' => $item->clan->name,
                'dboy_id' => $item->dboy_id,
                'dboy_name' => $item->dboy->name,
                'status' => $item->status,
                'created_at' => $item->created_at
            ];
        }
    
        return ['data' => $data, 'msg' => 'OK.'];
    }

    public function statusAcceptClan($id)
	{
		$res = ClanRequest::find($id);

        if (!$res) {
            return ['data' => [], 'msg' => 'Solicitud no existe.'];
        }

        $data = GroupMember::where('member_id', $res->dboy_id)->count();

        if ($data > 0) {
            return ['data' => [], 'msg' => 'Conductor ya pertenece a un clan.'];
        }

        $member = new GroupMember;
		$member->group_id =  $res->clan_id;
		$member->member_id = $res->dboy_id;
		$member->type = 'MEMBER';
        $res->status 	= 'Aceptada';
		if ($member->save() && $res->save()) {

            return ['data' => $member, 'msg' => 'Solicitud aceptada.'];
		}

		return ['data' => [], 'msg' => 'No se pudo aceptar la solicitud.'];
	}

    public function statusRejectClan($id) {
        $res = ClanRequest::find($id);
        if (!$res) {
            return ['data' => [], 'msg' => 'Solicitud no existe.'];
        }

        $data = GroupMember::where('member_id', $res->dboy_id)->count();

        if ($data > 0) {
            return ['data' => [], 'msg' => 'Conductor ya pertenece a un clan.'];
        }


        if ($res->status === 'Pendiente') {
			$res->status 	= 'Rechazada';
			$res->save();

			return ['data' => $res, 'msg' => 'Solicitud rechazada.'];

		}


        return ['data' => [], 'msg' => 'No se pudo rechazar la solicitud.'];


    }
}