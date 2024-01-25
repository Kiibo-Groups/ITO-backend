<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EmergencyContact extends Model {

    protected $table = 'emergency_contacts';

    protected $guarded = ['id'];


    public function emergencyContacts($dboy_id = null, $user_id = null) {
      
        $query = EmergencyContact::query();

        if ($dboy_id) {
            $query = $query->where('dboy_id', $dboy_id);
        }

        if ($user_id) {
            $query = $query->where('user_id', $user_id);
        }

        $query = $query->get();

        if ($query->isEmpty()) {
            return ['data' => [], 'msg' => 'Registros no encontrados'];
        }

        $data = [];
        foreach ($query as $key) {
            $data[] = [
                'id'        => $key->id,
                'user_id' => $key->user_id,
                'dboy_id'      => $key->dboy_id,
                'photo'      => Asset($key->photo), 
                'phone'      => $key->phone,
                'name'      => $key->name,
                'message'      => $key->message,
                'created_at' => $key->created_at,
                'updated_at' => $key->updated_at
            ];
        }

        return ['data' => $data, 'msg' => 'OK'];

    }

    public function createEmergencyContacts($request) {

        $path = '/' . 'upload/emergency_contacts/';

        $url = null;

        if ($request->has('photo')) {
            $imagenBase64 = $request->input('photo');

            $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);

            $imagenDecodificada = base64_decode($image);

            $imageName =  time() . '.png';

            file_put_contents(public_path($path . $imageName), $imagenDecodificada);

            $url = $path . $imageName;

        }

        $data = new EmergencyContact;

        if ($request->has('user_id')) {
            $data->user_id = $request->user_id;
        }

        if ($request->has('dboy_id')) {
            $data->dboy_id = $request->dboy_id;
        }
       
        $data->phone = $request->phone;
        $data->name = $request->name;
        $data->photo = $url;
        $data->message = $request->message;
        	
        if ( $data->save() ) {
            return ['data' => $data, 'msg' => 'Registro creado con éxito'];
        }

        return ['data' => [], 'msg' => 'No se pudo crear el registro'];
    }
}