<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

class Bonus extends Model {

    protected $table = 'bonuses';

    protected $guarded = ['id'];

    /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */

    public function validate($data,$type)
    { 
        $validator = Validator::make($data, [
            'title' => 'required', 
          ]);

        if($validator->fails())
        {
            return $validator;
        }
    }


    public function createBonuses($request) {

        $url = null;
        $path = '/' . 'upload/bonuses/';

        $data = new Bonus;
        $data->title = $request->title;
        $data->description = $request->description;
        $data->km_meta = $request->km_meta;
        $data->status = $request->status;

        if ($request->has('image')) {

            $imagenBase64 = $request->input('image');

            $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);

            $imagenDecodificada = base64_decode($image);

            $imageName =  time() . '.png';

            file_put_contents(public_path($path . $imageName), $imagenDecodificada);

            $url = $path . $imageName;
        }

        $data->image = $url;


        if ($data->save()) {
            return ['data' => [$data], 'msg' => 'Bono agregado con éxito'];
        }

        return ['data' => [], 'msg' => 'No se puedo agregar bono'];
    }

    public function getBonuses($dboy_id) {
        
        $query = Bonus::where('status', 1)
			->leftJoin('completed_bonuses', function ($join) use ($dboy_id) {
				$join->on('bonuses.id', '=', 'completed_bonuses.bonus_id')
					 ->where('completed_bonuses.dboy_id', '=', $dboy_id);
			})
			->whereNull('completed_bonuses.id')
            ->select('bonuses.id', 'bonuses.title', 'bonuses.description', 'bonuses.km_meta', 'bonuses.status', 'bonuses.image', 'bonuses.created_at', 'bonuses.updated_at', 'completed_bonuses.dboy_id', 'completed_bonuses.bonus_id')
			->get();

        if ($query) {

            $data = [];
            foreach ($query as $key) {
                $data[] = [
                    'id'        => $key->id,
                    'title' => $key->title,
                    'description'      => $key->description,
                    'image'      => Asset($key->image), 
                    'km_meta'      => $key->km_meta,
                    'status'      => $key->status,
                    'created_at' => $key->created_at,
                    'updated_at' => $key->updated_at
                ];
            }

            $completedBonuses = CompletedBonus::where('dboy_id', $dboy_id)->get();

            return ['data' => [
                'completedBonuses' => $completedBonuses,
                'services' => $data
            ], 'msg' => 'OK'];
        }

        return ['data' => [], 'msg' => 'Bonos no encontrados'];
    }

    public function getAll() {

        return Bonus::get();
       
    }

    public function addNew($data,$type)
    {
       
        $add                    = $type === 'add' ? new Bonus : Bonus::find($type);
        $add->title            = $data['title'];
        $add->description       = isset($data['description']) ? $data['description'] : null;  
        $add->km_meta         = isset($data['km_meta']) ? $data['km_meta'] : 0; 
        $add->status            = isset($data['status']) ? $data['status'] : 0;

        if(isset($data['image']))
        {
            $path = '/' . 'upload/bonuses/';
            $extension = $data['image']->getClientOriginalExtension();
            $filename   = time().rand(111,699).'.' . $extension; 
            $data['image']->move(public_path($path), $filename);   
            $add->image           = $path . $filename;
        }  

        $add->save();
        return ['msg' => 'done']; 
    }
}