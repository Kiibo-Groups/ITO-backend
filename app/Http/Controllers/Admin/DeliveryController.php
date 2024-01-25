<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NodejsServer;
use Illuminate\Http\Request;
use Auth;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\User;
use App\City;
use App\Models\Admin;
use App\Models\Rate;
use App\Models\RegisterKm;
use App\Models\{Bonus, ClanRequest, CompletedBonus, EmergencyContact};
use DB;
use Validator;
use Redirect;
use IMS;
use App\Exports\MaintenancesExport;
use Maatwebsite\Excel\Facades\Excel;

class deliveryController extends Controller {

	public $folder  = "admin/delivery.";
	/*
	|---------------------------------------
	|@Showing all records
	|---------------------------------------
	*/
	public function index()
	{
		$admin = new Admin;
        $city = Auth::guard('admin')->user()->city_id;

		if ($admin->hasperm('Repartidores')) {
            if(Auth::guard('admin')->user()->city_id == 0){
                $res = new Delivery;

			    return View($this->folder.'index',[
					'data' => $res->getAll(0),
					'link' => env('admin').'/delivery/',
					'getKm' => new RegisterKm,
					'array' => [],
					'export' => env('admin').'/exportDboy/',
					'form_url' => env('admin').'/exportData_staff',
					'admin'   => $admin
				]);

            }else {

                $store = 0;

                $res = Delivery::where(function($query) use($store) {

                    if($store > 0)
                    {
                        $query->where('store_id',$store);
                    }


                })->leftjoin('city','delivery_boys.city_id','=','city.id')
				->leftjoin('delivery_type','delivery_boys.type_edriver','=','delivery_type.id')
				->select('city.name as city','delivery_type.icon as icon_driver','delivery_type.name as name_driver','delivery_boys.*')
                  ->where('city_id', "$city")->paginate(10);
 
                return View($this->folder.'index',[
                    'data' => $res,
                    'link' => env('admin').'/delivery/',
                    'array'		=> [],
                    'export' => env('admin').'/exportDboy/',
                    'form_url' => env('admin').'/exportData_staff'
                ]);

            }


		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

	public function report_dboy($id)
	{

		$admin = new Admin;

		if ($admin->hasperm('Repartidores')) {
			$res = new Delivery;
			return View($this->folder.'report',[
				'data' => $res->getReport($id),
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
				'data' => new Delivery,
				'type_delivery' => DeliveryType::where('status',0)->get(),
				'form_url' => env('admin').'/delivery',
				'citys'    => $city->getAll(0),
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
		$data = new Delivery;

		if($data->validate($Request->all(),'add'))
		{
			return redirect::back()->withErrors($data->validate($Request->all(),'add'))->withInput();
			exit;
		}

		$data->addNew($Request->all(),"add",'web');

		return redirect(env('admin').'/delivery')->with('message','New Record Added Successfully.');
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
			$city = new City;
			return View(
				$this->folder.'edit',
				[
					'data' => Delivery::find($id),
					'form_url' => env('admin').'/delivery/'.$id,
					'type_delivery' => DeliveryType::where('status',0)->get(),
					'citys'    => $city->getAll(0),
                    'admin' => $admin
					]
				);
		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

    public function pay($id)
    {
        $admin = new Admin;

		if ($admin->hasperm('Repartidores')) {

			return View(
				$this->folder.'pay',
				[
					'data' => Delivery::find($id),
					'form_url' => env('admin').'/delivery_pay/'.$id,
                    'admin' => $admin
					]
				);
		}else {
			return Redirect::to(env('admin').'/home')->with('error', 'No tienes permiso de ver la sección Repartidores');
		}
	}

	public function rate($id)
    {
        $admin = new Admin;
		$rate  = new Rate;
		return View(
		$this->folder.'rate',
		[
			'data' 		=> Delivery::find($id),
			'rate_data' => $rate->GetRate($id),
            'admin' => $admin
			]
		);
	}

	public function delivery_pay(Request $Request,$id)
	{
		$staff = new Delivery;

		$req = $staff->add_comm($Request->All(),$id);

		return redirect(env('admin').'/delivery')->with('message','Pago realizado con exito.');


	}
	/*
	|---------------------------------------
	|@update data in DB
	|---------------------------------------
	*/
	public function update(Request $Request,$id)
	{
		$data = new Delivery;

		if($data->validate($Request->all(),$id))
		{
			return redirect::back()->withErrors($data->validate($Request->all(),$id))->withInput();
			exit;
		}

		$data->addNew($Request->all(),$id,'web');

		return redirect(env('admin').'/delivery')->with('message','Record Updated Successfully.');
	}

	/*
	|---------------------------------------------
	|@Delete Data
	|---------------------------------------------
	*/
	public function delete($id)
	{
		try {
			
		$dboy = Delivery::find($id);

		if ($dboy) {

			if ( (($dboy->status === 0) || ($dboy->status_admin === 0) ) || (($dboy->status === 0) && ($dboy->status_admin === 0)) ) {
				return redirect(env('admin').'/delivery')->with('message', 'No se puede Borrar un conductor activo.');
			}
	
			$dboy->kms()->delete();
			$clanRequests = ClanRequest::where('dboy_id', $id)->get();
			
			foreach ($clanRequests as $clanRequest) {
				$clanRequest->delete();
			}

			$emergencyContacts = EmergencyContact::where('dboy_id', $id)->get();

			foreach ($emergencyContacts as $emergencyContact) {
				$emergencyContact->delete();
			}

			$dboy->group_member()->delete();
			$dboy->delete();
		}

		return redirect(env('admin').'/delivery')->with('message','Conductor eliminado con éxito.');
		} catch (\Throwable $th) {
			return redirect(env('admin').'/delivery')->with('error', 'Error al intentar eliminar el registro.');
		}
	}

	/*
	|---------------------------------------------
	|@Change Status
	|---------------------------------------------
	*/
	public function status($id)
	{
		$res 			= Delivery::find($id);
		$res->status 	= $res->status == 0 ? 1 : 0;
		$res->save();

		if ($id == 0) { // Activo
			$message = "El Conductor esta activo";
		}else {
			$message = "El conductor ha sido bloqueado";
		}

		$addServer = new NodejsServer;
		$return = array(
			'id'        => $res->id,
			'city_id'   => $res->city_id,
			'name'      => $res->name,
			'phone'     => $res->phone,
			'type_driver' => $res->type_driver,
			'max_range_km' => $res->max_range_km,
			'external_id'   => $res->external_id,
			'status'        => $res->status,
			'status_admin'  => $res->status_admin,
		);
		
		$addServer->updateStaffDelivery($return);
		
		return redirect(env('admin').'/delivery')->with('message','Status Updated Successfully.');
	}

	public function status_admin($id)
	{
		$res 				= Delivery::find($id);
		$res->status_admin 	= $res->status_admin == 0 ? 1 : 0;
		$res->save();

		if ($id == 0) { // Activo
			$message = "El Conductor esta activo";
		}else {
			$message = "El conductor ha sido bloqueado";
		}

		$addServer = new NodejsServer;
		$return = array(
			'id'        => $res->id,
			'city_id'   => $res->city_id,
			'name'      => $res->name,
			'phone'     => $res->phone,
			'type_driver' => $res->type_driver,
			'max_range_km' => $res->max_range_km,
			'external_id'   => $res->external_id,
			'status'        => $res->status,
			'status_admin'  => $res->status_admin,
		);
		
		$addServer->updateStaffDelivery($return);

		return redirect(env('admin').'/delivery')->with('message',$message);
	}

	public function getCity($id)
	{
		$res = User::find($id);
		return $res->name;
	}

	/*
	|---------------------------------------
	|@View Report
	|---------------------------------------
	*/
	public function exportDboy($id)
	{
		return Excel::download(new DeliveryExport($id), 'report.xlsx');
	}

	public function exportMaintenance() {
		$maintenances = CompletedBonus::latest()->get();
		return Excel::download(new MaintenancesExport($maintenances), 'mantenimientos.xlsx');
	}

	/*
	|---------------------------------------
	|@View Bonuses
	|---------------------------------------
	*/
	public function viewBonuses($id)
	{
		$dboy_id = $id;
		$kms      = new RegisterKm;
		$admin = new Admin;
		$delivery = Delivery::find($id);
		$getKm    = $kms->getKms($id);
		//$bonuses  = Bonus::where('status', 1)->get();
		
		/*$bonuses = Bonus::where('status', 1)
			->leftJoin('completed_bonuses', function ($join) use ($dboy_id) {
				$join->on('bonuses.id', '=', 'completed_bonuses.bonus_id')
					 ->where('completed_bonuses.dboy_id', '=', $dboy_id);
			})
			->whereNull('completed_bonuses.id')
			->get();*/

		$bonuses = Bonus::where('bonuses.status', 1) // Especifica la tabla 'bonuses' para evitar ambigüedades
			->leftJoin('completed_bonuses', function ($join) use ($dboy_id) {
				$join->on('bonuses.id', '=', 'completed_bonuses.bonus_id')
					 ->where('completed_bonuses.dboy_id', '=', $dboy_id);
			})
			->whereNull('completed_bonuses.id')
			->select('bonuses.id', 'bonuses.title', 'bonuses.description', 'bonuses.km_meta', 'bonuses.status', 'bonuses.image', 'bonuses.created_at', 'bonuses.updated_at', 'completed_bonuses.dboy_id', 'completed_bonuses.bonus_id')
			->get();

		$completedBonuses = CompletedBonus::where('dboy_id', $dboy_id)->get();

		return View($this->folder.'getBonus',[
			'data' => $delivery,
			'getkm' => $getKm['data']['km'],
			'bonuses' => $bonuses,
			'completedBonuses' => $completedBonuses,
			'link' => env('admin').'/delivery/',
			'admin'   => $admin,
			'form_url' => env('admin').'/delivery/completedBonuses',
		]);

	}

	public function completedBonuses(Request $request) {

		$completed = new CompletedBonus;
		$completed->dboy_id = $request->dboy_id;
		$completed->bonus_id = $request->bonuses_id;
		$completed->save();

		return redirect(env('admin').'/delivery/viewBonuses/' . $completed->dboy_id)->with('message', 'Servicio Cobrado');
	}
}
