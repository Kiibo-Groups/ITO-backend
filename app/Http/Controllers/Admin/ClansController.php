<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth; 
use App\Models\Admin;
use App\Models\Group;
use App\Models\{GroupMember, ClanRequest};
use App\Models\Delivery;

use DB;
use Validator;
use Redirect;
use IMS;
class ClansController extends Controller
{
    
	public $folder  = "admin/clans.";
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admin = new Admin;

		if ($admin->hasperm('Clanes')) {

            $res = new Group;
 
            return View($this->folder.'index',[
                'data' => $res->getGroups(),
                'admin' => $admin,
                'link' 	=> env('admin').'/clans/'
            ]);

		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Clanes');
		}
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lims_data_clan = new Group;
        $lims_data_clan->createGroups($request);

		return redirect(env('admin').'/clans')->with('message','Nuevo clan agregado.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = new Admin;
 
		if ($admin->hasperm('Clanes')) {
			return View($this->folder.'add',[
                'data'   		=> new Group,  
                'dboys'         => Delivery::where('status',0)->where('status_admin',0)->get(),
                'form_url' 		=> env('admin').'/clans',
                'admin'	 		=> $admin
			]);
		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Clanes');
		}
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = new Admin;
		if ($admin->hasperm('Clanes')) {

		return View($this->folder.'edit',[
			'data' => Group::find($id),
            'dboys'  => Delivery::where('status',0)->where('status_admin',0)->get(),
			'form_url' => env('admin').'/clans/'.$id,
            'admin' => $admin
            ]);
		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Clanes');
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $lims_data_clan = Group::findOrFail($id);

        $image = null;
        if($request->hasFile('image'))
        {
            $path = 'upload/clans/';
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename   = time().rand(111,699).'.' . $extension; 
            $request->file('image')->move(public_path($path), $filename);   
            $image           = $filename;
        } 

        $input = $request->all();
        $input['image'] = $image;
        // Actualizamos Clan principal
        $lims_data_clan->update($input);

        // Actualizamos el lider del clan
        $LeaderGroups = GroupMember::where('group_id',$id)->where('type',1)->first();
        $LeaderGroups->member_id = $input['created_by'];
        $LeaderGroups->save();

        return redirect(env('admin').'/clans')->with('message','Clan actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            
            $lims_data_clan = Group::find($id);

            // Validamos si tiene conductores asignados....
            $chkMembers = GroupMember::where('group_id',$id)->where('type','!=',1)->get();

            if (count($chkMembers) > 0) { // Tiene Miembros agregados
                return Redirect::to(env('admin').'/clans')->with('error', "Este clan tiene miembros activos, elimina todos los miembros para poder eliminar el clan.");
            }else {
                GroupMember::where('group_id',$id)->delete();
                $lims_data_clan->delete();
                return Redirect::to(env('admin').'/clans')->with('message', "Este clan ha sido eliminado.");
            }

        
        } catch (\Exception $th) {
            return Redirect::to(env('admin').'/clans')->with('error', $th->getMessage());
        }
    }

    /**
     * View Members List of Clan
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        $admin = new Admin;

		if ($admin->hasperm('Clanes')) {

            $res = new Group;
 
            return View($this->folder.'members',[
                'Clan' => Group::find($id),
                'data' => $res->groupAllMembers($id),
                'admin' => $admin,
                'link' 	=> env('admin').'/clans/'
            ]);

		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Clanes');
		}
    }

    public function delete_member($clan, $member)
    {

        $chkMembers = GroupMember::where('group_id',$clan)->where('member_id',$member)->first();

        if (isset($chkMembers)) {
            if ($chkMembers->type == 'LEADERS') {
                return Redirect::to(env('admin').'/clans/view/'.$clan)->with('error', 'No puedes eliminar al lider del clan, Actualizalo y/o Elimina el Clan.');
            }else 
            {
                $clanRequests = ClanRequest::where('dboy_id', $member)->get();
			
                foreach ($clanRequests as $clanRequest) {
                    $clanRequest->delete();
                }
                GroupMember::where('group_id',$clan)->where('member_id',$member)->delete();
                return Redirect::to(env('admin').'/clans/view/'.$clan)->with('message', "Este Miembro ha sido del clan eliminado.");
            }
        }else {
            return Redirect::to(env('admin').'/clans/view/'.$clan)->with('error', 'El Clan y/o el miembro no existen.');
        }

        // return response()->json([
        //     'clan' => $clan,
        //     'member' => $member,
        //     'chk' => $chkMembers
        // ]);
    }
}
