<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

class OfferList extends Authenticatable
{
    protected $table = "offer_list";

    /*
    |--------------------------------
    |Create/Update city
    |--------------------------------
    */

    public function addNew($data,$type)
    {
        $add                    = $type === 'add' ? new OfferList : OfferList::find($type);
        $add->user_id           = isset($data['user_id']) ? $data['user_id'] : 0;
        $add->offer_id          = isset($data['offer_id']) ? $data['offer_id'] : 0;
        $add->count             = 1;
        $add->save();

        return ['msg' => 'done'];
    }

    /*
    |--------------------------------------
    |Get all data from db
    |--------------------------------------
    */
    public function getAll($store = 0,$from = 'app')
    {
        if ($from == 'app') {
            $req = OfferList::orderBy('offer.id','DESC')->get();
            $data = [];

            foreach ($req as $row) {
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
            return OfferList::orderBy('offer.id','DESC')->get();
        }
    }

    public function getOffer($id)
    {
        $req = OfferList::where('offer_id',$id)->get();

        return [
            'data'  => $req,
            'count' => count($req)
        ];
    }

    public function getSData($data,$id,$field)
    {
        $data = unserialize($data);

        return isset($data[$field][$id]) ? $data[$field][$id] : null;
    }
}