<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\User;
use App\City;
use App\Models\Admin;
use App\Models\Rate;
use DB;
use Validator;
use Redirect;
use IMS;
class typedeliveryController extends Controller {

	public $folder  = "admin/delivery_type.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{
		$admin = new Admin; 

		if ($admin->hasperm('Repartidores')) {
            $res = new DeliveryType;

            return View($this->folder.'index',[
                'data' => $res->getAll(0),
                'link' => env('admin').'/type_delivery/', 
                'admin'   => $admin
            ]);
		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
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
			$city = new City;
			return View($this->folder.'add',[
				'data' => new DeliveryType,
				'form_url' => env('admin').'/type_delivery', 
				'admin'  => $admin
			]);
		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{
		$data = new DeliveryType;

		if($data->validate($Request->all(),'add'))
		{
			return redirect::back()->withErrors($data->validate($Request->all(),'add'))->withInput();
			exit;
		}

		$data->addNew($Request->all(),"add",'web');

		return redirect(env('admin').'/type_delivery')->with('message','New Record Added Successfully.');
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
				$this->folder.'edit',
				[
					'data' => DeliveryType::find($id),
					'form_url' => env('admin').'/type_delivery/'.$id,
                    'admin' => $admin
					]
				);
		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	} 
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{
		$data = new DeliveryType;

		if($data->validate($Request->all(),$id))
		{
			return redirect::back()->withErrors($data->validate($Request->all(),$id))->withInput();
			exit;
		}

		$data->addNew($Request->all(),$id,'web');

		return redirect(env('admin').'/type_delivery')->with('message','Record Updated Successfully.');
	}

	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		// Contamos los conductores que tengan asignado este type_delivery
		$count_type = Delivery::where('type_driver',$id)->count();
		if ($count_type > 0) {
			return redirect(env('admin').'/type_delivery')->with('error','Existen vehiculos asignados a esta categoria.');
		}else {
			DeliveryType::where('id',$id)->delete();
			return redirect(env('admin').'/type_delivery')->with('message','Categoria eliminada con exito.');
		}
	}

	/*
	|---------------------------------------------
	|@Change Status
	|---------------------------------------------
	*/
	public function status($id)
	{
		$res 			= DeliveryType::find($id);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/type_delivery')->with('message','Status Updated Successfully.');
	}
  
}
