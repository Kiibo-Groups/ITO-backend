<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\{Admin, ClanRequest, GroupMember};
use DB;
use Validator;
use Redirect;

class ClanRequestsController extends Controller
{

	public $folder  = "admin/clan_requests.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{
		$admin = new Admin;

		if ($admin->hasperm('Repartidores')) {
			return View($this->folder . 'index', [
				'data' => ClanRequest::get(),
				'link' => env('admin') . '/clan_requests/',
				'admin'   => $admin
			]);
		} else {
			return Redirect::to(env('admin') . '/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}
	
	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{

		ClanRequest::find($id)->delete();
		return redirect(env('admin') . '/clan_requests')->with('message', 'Registro eliminado con exito.');
	}

	/*
	|---------------------------------------------
	|@Change Status
	|---------------------------------------------
	*/
	public function statusAccept($id)
	{
		$res 			= ClanRequest::find($id);

		if ($res->status === 'Pendiente') {
			$res->status 	= 'Aceptada';
			if ($res->save()) {

				$data 			= GroupMember::where('group_id', $res->clan_id)->where('member_id', $res->dboy_id)->count();

				if ($data === 0) {

				$member = new GroupMember;
				$member->group_id =  $res->clan_id;
				$member->member_id = $res->dboy_id;
				$member->type = 'MEMBER';
				$member->save();

				}

			}
			return redirect(env('admin') . '/clan_requests')->with('message', 'Status Updated Successfully.');

		}
		return redirect(env('admin') . '/clan_requests')->with('error', 'No se puede aceptar el status');

	}

	public function statusReject($id)
	{
		$res 			= ClanRequest::find($id);

		if ($res->status === 'Pendiente') {
			$res->status 	= 'Rechazada';
			$res->save();
			return redirect(env('admin') . '/clan_requests')->with('message', 'Status Updated Successfully.');

		}
		return redirect(env('admin') . '/clan_requests')->with('error', 'No se puede rechazar el status');

	}
}
