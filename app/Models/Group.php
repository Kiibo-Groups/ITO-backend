<?php

namespace App\Models;

use App\Models\Delivery;
use Illuminate\Database\Eloquent\Model;

class Group extends Model {

    protected $table = 'groups';

    protected $guarded = ['id'];

    protected $fillable = [
        'id_pusher',
        'name',
        'description',
        'created_by',
        'likes',
        'trips',
        'image'
    ];

    public function dboy() {
        return $this->belongsTo(Delivery::class, 'created_by');
    }

    public function members() {
        return $this->hasMany(GroupMember::class);
    }

    public function getGroups() {
        return [
            'data' => Group::query()
            ->OrderBy('created_at','DESC')
            ->with('members', 'dboy')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($data) => [
                'id' => $data->id,
                'name' => $data->name,
                'description' => $data->description,
                'created_by' => [
                    'id' => $data->dboy->id,
                    'name' => $data->dboy->name,
                    'email' => $data->dboy->email,
                ],
                'members' => $data->members->count(),
                'like' => $data->likes,
                'trips' => $data->trips,
                'image' => asset('upload/clans/' . $data->image),
            ]),
            'msg' => 'OK'
        ];
    }

    public function getGroupUnique($user_id, $clan) {
        $req = Group::where('id',$clan)->with('members', 'dboy')->first();
        $memberActive = $this->ValidateMemberActive($clan, $user_id); 
        
        $data = [
            'id' => $req->id,
            'name' => $req->name,
            'memberActive' => $memberActive,
            'description' => $req->description,
            'created_by' => [
                'id' => $req->dboy->id,
                'name' => $req->dboy->name,
                'email' => $req->dboy->email,
            ],
            'members' => $req->members->count(),
            'like' => $req->likes,
            'trips' => $req->trips,
            'image' => asset('upload/clans/' . $req->image),
        ];
        

        return $data;
    }

    public function createGroups($request) {

        $group = new Group;
        $group->name = $request->name;
        $group->description = $request->description;
        $group->created_by = $request->created_by;

        if($request->hasFile('image'))
        {
            $path = 'upload/clans/';
            $extension =  $request->file('image')->getClientOriginalExtension();
            $filename   = time().rand(111,699).'.' . $extension; 
            $request->file('image')->move(public_path($path), $filename);   
            $group->image           = $filename;
        }  

        if ($group->save()) {
            // Generamos el ID Puhser
            $group->id_pusher = substr(md5($group->id.$group->name),0,15);
			$group->save();

            $member = new GroupMember;
            $member->group_id = $group->id;
            $member->member_id = $group->created_by;
            $member->type = 1;
            $member->save();

            $data = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'created_by' => [
                    'id' => $group->dboy->id,
                    'name' => $group->dboy->name,
                    'email' => $group->dboy->email,
                ],
                'members' => $group->members->count(),
                'like' => $group->likes,
                'trips' => $group->trips,
            ];

            return ['data' => $data, 'msg' => 'Clan creado con éxito'];

        }

    }

    public function groupLikes($request) {

        $data = Group::find($request->id);

        if (!$data) {
            return ['data' => [], 'msg' => 'Clan no encontrado'];
        }

        $data->likes += 1;

        $data->save();

        return ['data' => $data, 'msg' => 'Clasificación agregada con éxito'];

    }

    public function groupTrips($request) {

        $data = Group::find($request->id);

        if (!$data) {
            return ['data' => [], 'msg' => 'Clan no encontrado'];
        }

        $data->trips += 1;

        $data->save();

        return ['data' => $data, 'msg' => 'Viaje agregado con éxito'];

    }

    public function groupAddMember($request) {
        $group = Group::find($request->id);
        
        if (!$group) {
            return ['data' => [], 'msg' => 'Clan no encontrado'];
        }

        /*$check = GroupMember::where('group_id', $group->id)->where('member_id', $request->driver_id)->count();

        if ($check > 0) {
            return ['status' => false,'data' => [], 'msg' => 'Miembro ya se encuentra registrado en este clan'];
        }*/

        $check = GroupMember::where('member_id', $request->driver_id)->count();
        if ($check > 0) {
            return ['status' => false,'data' => [], 'msg' => 'Conductor ya se encuentra registrado en un clan'];
        }

            $member = new GroupMember;
            $member->group_id = $group->id;
            $member->member_id = $request->driver_id;
            $member->type = $request->type;
            $member->save();

            return ['status' => true, 'data' => $member, 'msg' => 'Miembro agregado con éxito'];

    }

    public function groupAllMembers($id) {

        return [
            'data' => GroupMember::query()
            ->with('dboy')
            ->where('group_id', $id)
            ->paginate(10)
            ->withQueryString()
            ->through(function($data) {
              $dboy = collect($data->dboy)->except(['created_at','updated_at','lat','lng']);
              $profile = asset('upload/profile/' . $dboy->get('profile'));
              $dboy->put('profile',  $profile);
            return[
                'id' => $data->id,
                'group_id' => $data->group_id, 
                'dboy' => $dboy,
                'name' => $data->dboy->name,
                'type' => $data->type
            ];
        }),
            'msg' => 'OK'
        ];

    }

    /**
     * Validamos si el conductor es miembro activo de un clan
     */
    public function ValidateMemberActive($group_id, $member_id)
    {
        $chkMember = GroupMember::where('group_id',$group_id)->where('member_id',$member_id)->first();
        $memberActive = false;
        if (isset($chkMember)) {
            $memberActive = true;
        }
        return $memberActive;
    }

}