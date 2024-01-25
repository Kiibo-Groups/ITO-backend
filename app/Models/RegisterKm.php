<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterKm extends Model {

    protected $table = 'register_kms';

    protected $guarded = ['id'];

    public function createKms($dboy_id = null, $km = null, $commaned_id) {

        if ( $commaned_id === 0) {
            $query = RegisterKm::where('dboy_id', $dboy_id)->where('commaned_id', $commaned_id)->count();

            if ($query > 0) {
                return ['data' => [], 'msg' => 'Kilometraje inicial ya existe'];
            }
        }
        if ($dboy_id && $km) {
            $data = new RegisterKm;
            $data->dboy_id = $dboy_id;
            $data->commaned_id = $commaned_id;
            $data->km = $km;
            $data->save();

            return ['data' => $data, 'msg' => 'Kilometraje registrado con éxito'];
        }
    }

    public function getKms($dboy_id = null) {

        if ($dboy_id) {

            $req = RegisterKm::where('dboy_id', $dboy_id)->latest()->get();
            $data = [];
            $km = 0;

            foreach ($req as $key => $value) {
                // Sumamos los KM recorridos
                $km = $km + $value->km; 
            }

            $data = [
                'km' => $km
            ];

            if ($data) {
                return ['data' => $data, 'msg' => 'OK'];
            }

        }

        return ['data' => [], 'msg' => 'Kms no encontrados'];
    }
}