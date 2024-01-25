<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\{Admin, Bonus, RegisterKm};
use App\Models\Rate;
use DB;
use Validator;
use Redirect;

class BonusesController extends Controller
{

	public $folder  = "admin/bonuses.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{
		$admin = new Admin;

		if ($admin->hasperm('Repartidores')) {
			$res = new Bonus;

			return View($this->folder . 'index', [
				'data' => $res->getAll(),
				'link' => env('admin') . '/bonuses/',
				'admin'   => $admin
			]);
		} else {
			return Redirect::to(env('admin') . '/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

	/*
	|---------------------------------------
	|@Add new page
	|---------------------------------------
	*/
	public function show()
	{
		$admin = new Admin;

		if ($admin->hasperm('Repartidores')) {
			return View($this->folder . 'add', [
				'data' => new Bonus,
				'form_url' => env('admin') . '/bonuses',
				'admin'  => $admin
			]);
		} else {
			return Redirect::to(env('admin') . '/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{
		$data = new Bonus;

		if ($data->validate($Request->all(), 'add')) {
			return redirect::back()->withErrors($data->validate($Request->all(), 'add'))->withInput();
			exit;
		}

		$data->addNew($Request->all(), "add");

		return redirect(env('admin') . '/bonuses')->with('message', 'New Record Added Successfully.');
	}

	/*
	|---------------------------------------
	|@Edit Page
	|---------------------------------------
	*/
	public function edit($id)
	{
		$admin = new Admin;

		if ($admin->hasperm('Repartidores')) {

			return View(
				$this->folder . 'edit',
				[
					'data' => Bonus::find($id),
					'form_url' => env('admin') . '/bonuses/' . $id,
					'admin' => $admin
				]
			);
		} else {
			return Redirect::to(env('admin') . '/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request, $id)
	{
		$data = new Bonus;

 
		if ($data->validate($Request->all(), $id)) {
			return redirect::back()->withErrors($data->validate($Request->all(), $id))->withInput();
			exit;
		}

		$data->addNew($Request->all(), $id);

		return redirect(env('admin') . '/bonuses')->with('message', 'Record Updated Successfully.');
	}

	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{

		Bonus::find($id)->delete();
		return redirect(env('admin') . '/bonuses')->with('message', 'Registro eliminado con exito.');
	}

	/*
	|---------------------------------------------
	|@Change Status
	|---------------------------------------------
	*/
	public function status($id)
	{
		$res 			= Bonus::find($id);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin') . '/bonuses')->with('message', 'Status Updated Successfully.');
	}
}