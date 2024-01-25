<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Auth;
use DB;
class DeliveryType extends Authenticatable
{
    protected $table = "delivery_type";

    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */

    public function rules($type)
    {
         

        $nameRule = 'required|unique:delivery_type,name';
        $typeRule = 'required|unique:delivery_type,type';
        
        if ($type !== 'add') {
            $nameRule .= ',' . $type;
            $typeRule .= ',' . $type;
        }

        return [
            'name' => $nameRule,
            'type' => $typeRule,
        ];
    }

    public function validate($data,$type)
    {

        $validator = Validator::make($data,$this->rules($type));
        if($validator->fails())
        {
            return $validator;
        }
    }

    /*
    |--------------------------------
    |Create/Update city
    |--------------------------------
    */

    public function addNew($data,$type,$from)
    {

        $add                    = $type === 'add' ? new DeliveryType : DeliveryType::find($type);
        $add->type              = isset($data['type']) ? $data['type'] : 0;
        $add->name              = isset($data['name']) ? $data['name'] : null;  
        $add->type_comm         = isset($data['type_comm']) ? $data['type_comm'] : 0;  
        $add->comm              = isset($data['comm']) ? $data['comm'] : null;  
        $add->status            = isset($data['status']) ? $data['status'] : 0;
        $add->vehicle_type      = isset($data['vehicle_type']) ? $data['vehicle_type'] : 'Normal';

        if(isset($data['icon']))
        {
            $path = 'upload/driver_type/';
            $extension = $data['icon']->getClientOriginalExtension();
            $filename   = time().rand(111,699) . '.'  . $extension;
            $data['icon']->move(public_path($path), $filename);   
            $add->icon           = $filename;
        }  

        $add->save();
        return ['msg' => 'done']; 
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll($type = 1)
    {
        if ($type == 1) {
            $req = DeliveryType::where('status',0)->orderBy('id','DESC')->get();
            $data = [];
            foreach ($req as $key) {
                $data[] = [
                    'id'        => $key->id,
                    'type_comm' => $key->type_comm,
                    'comm'      => $key->comm,
                    'icon'      => Asset('upload/driver_type/'.$key->icon), 
                    'name'      => $key->name,
                    'type'      => $key->type,
                    'vehicle_type' => $key->vehicle_type,
                    'created_at' => $key->created_at->diffForHumans(),
                ];
            }

            return $data;
        }else {
            return DeliveryType::orderBy('id','DESC')->get();
        }
    }

}