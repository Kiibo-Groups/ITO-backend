<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

class Offer extends Authenticatable
{
    protected $table = "offer";

    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */
    
    public function rules($type)
    {
        return [

        'code'      => 'required',

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

    public function addNew($data,$type)
    {
        $a                      = isset($data['lid']) ? array_combine($data['lid'], $data['l_code']) : [];
        $b                      = isset($data['lid']) ? array_combine($data['lid'], $data['l_desc']) : [];
        $add                    = $type === 'add' ? new Offer : Offer::find($type);
        $add->code              = isset($data['code']) ? $data['code'] : null;
        $add->description       = isset($data['description']) ? $data['description'] : null;
        if(isset($data['img']))
        {
            $filename   = time().rand(111,699).'.' .$data['img']->getClientOriginalExtension(); 
            $data['img']->move("upload/offers/", $filename);   
            $add->img = $filename;   
        }

        $add->min_cart_value    = isset($data['min_cart_value']) ? $data['min_cart_value'] : 0;
        $add->upto              = isset($data['upto']) ? $data['upto'] : null;
        $add->type              = isset($data['type']) ? $data['type'] : 0;
        $add->value             = isset($data['value']) ? $data['value'] : 0;
        $add->status            = isset($data['status']) ? $data['status'] : 0;
        $add->start_from        = isset($data['start_from']) ? date('Y-m-d',strtotime($data['start_from'])) : null;
        $add->valid_till        = isset($data['valid_till']) ? date('Y-m-d',strtotime($data['valid_till'])) : null;
        $add->unique_user       = isset($data['unique_user']) ? $data['unique_user'] : 0;
        $add->s_data            = serialize([$a,$b]);
        $add->save();

        
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll($store = 0,$from = 'app')
    {
        if ($from == 'app') {
            $req = Offer::orderBy('offer.id','DESC')->get();
            $data = [];
            $list = new OfferList;
            foreach ($req as $row) {

                $count = OfferList::where('offer_id',$row->id)->count();

                $data[] = [
                    'id'        => $row->id,
                    'img'       => ($row->img) ? Asset('upload/offers/'.$row->img) : '',
                    'code'      => $row->code,
                    'description' => $row->description,
                    'min_cart_value'  => $row->min_cart_value,
                    'type'      => $row->type,
                    'value'     => $row->value
                ];
            }

            return $data;
        }else {
            return Offer::orderBy('offer.id','DESC')->get();
        }
    }

    public function getOffer($user_id)
    {  
        $res  = Offer::where('status',0)->orderBy('id','DESC')->get();
        $data = [];

        foreach($res as $row)
        {
            $chk_list = OfferList::where('offer_id',$row->id)->where('user_id',$user_id)->first();

            if (!$chk_list) {
                
                $data[] = [
                    'id'        => $row->id,
                    'img'       => ($row->img) ? Asset('upload/offers/'.$row->img) : '',
                    'code'      => $row->code,
                    'description' => $row->description,
                    'min_cart_value'  => $row->min_cart_value,
                    'type'      => $row->type,
                    'value'     => $row->value
                ];
            }
        }

        return $data;
    }

    public function getSData($data,$id,$field)
    {
        $data = unserialize($data);

        return isset($data[$field][$id]) ? $data[$field][$id] : null;
    }
}
