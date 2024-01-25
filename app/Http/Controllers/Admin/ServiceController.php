<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Commaned;
use App\Models\Admin;
use DB;
use Validator;
use Redirect;
use IMS;
use App\Exports\ServicesExport;
use App\Models\NewTrip;
use Maatwebsite\Excel\Facades\Excel;

class ServiceController extends Controller {

	public $folder  = "admin/services.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{
		$res = new Commaned;
        $admin = new Admin;
        $status = 0;
        $title = 'Listado de Servicios';
		if (isset($_GET['status'])) {
			$status = $_GET['status'];
			if($_GET['status'] == 0)
			{
				$title = "Nuevos Servicios";
			}
			elseif($_GET['status'] == 1)
			{
				$title = "Servicios en ejecución";
			}
			elseif($_GET['status'] == 2)
			{
				$title = "Servicios cancelados";
			}
            elseif($_GET['status'] == 3)
			{
				$title = "Servicios no asignados";
			}
			elseif($_GET['status'] == 6)
			{
				$title = "Servicios finalizados";
			}	
		}

		if ($admin->hasperm('Servicios')) {
			if (isset($_GET['export']) && $_GET['export'] == 1) {
				$services = Commaned::where('status', 6)->latest()->get();
				return Excel::download(new ServicesExport($services), 'services.xlsx');
			}
	
            return View($this->folder.'index',[
                'data' 		=> $res->getAll($status),
				'comm_f'    => new Commaned,
                'link' 		=> env('admin').'/Services/',
                'form_url'	=> env('admin').'/Services/assign',
                'admin'     => $admin
			]);

		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Servicios');
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

		if ($admin->hasperm('Servicios')) {
		
			return View($this->folder.'index',[

				'data' 		=> new Commaned,
				'form_url' 	=> env('admin').'/Services',
				'array'		=> [],
				'admin'     => $admin

			]);
		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Servicios');
		}
	}

	/*
	|---------------------------------------
	|@Save data in DB
	|---------------------------------------
	*/
	public function store(Request $Request)
	{
		$data = new Commaned;

		if($data->validate($Request->all(),'add'))
		{
			return redirect::back()->withErrors($data->validate($Request->all(),'add'))->withInput();
			exit;
		}

		$data->addNew($Request->all(),"add");
		return redirect(env('admin').'/Services')->with('message','New Record Added Successfully.');
	}

	/*
	|---------------------------------------
	|@Edit Page
	|---------------------------------------
	*/
	public function edit($id)
	{
		$admin = new Admin;
		$res   = new Commaned;
		$trips = NewTrip::where('commaned_id', $id)->latest()->get();

		if ($admin->hasperm('Servicios')) {
			return View($this->folder.'edit',[
				'data' 		=> $res->getElement($id),
				'form_url' 	=> env('admin').'/Services/'.$id,
				'admin'     => Admin::find(1),
				'res'       => $res,
				'trips'    	=> $trips
			]);
		} else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Servicios');
		}
	}

	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{
		$data = new Commaned;
		$data->updateComm($Request->all(),$id);

		return redirect(env('admin').'/Services')->with('message','Servicio actualizado con exito.');
	}

	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		Commaned::where('id',$id)->delete(); 
		return redirect(env('admin').'/Services')->with('message','Elemento eliminado');
	}

	/*
	|---------------------------------------------
	|@Change Status
	|---------------------------------------------
	*/
	public function status($id)
	{
		$res 			= Commaned::find($id);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		return redirect(env('admin').'/Services')->with('message','Status Updated Successfully.');
	}

	public function cancel($id)
	{
		$res 			= Commaned::find($id);
		$res->status 	= 2;
		$res->save();

		return redirect(env('admin').'/Services')->with('message','Status Updated Successfully.');
	}

}
