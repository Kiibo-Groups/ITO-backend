<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;
use Mail;
class AppUser extends Authenticatable
{
   protected $table = 'app_user';

   protected $guarded = ['id'];
   /*
    |----------------------------------------------------------------
    |   Validation Rules and Validate data for add & Update Records
    |----------------------------------------------------------------
    */
    
    public function rules($type)
    {
        return [
            'name'      => 'required',
            'email'     => 'required|unique:app_user,email,'.$type,
            'phone'      => 'required|unique:app_user,phone,',
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

   public function addNew($data,$type)
   {
       if ($type == 'add') {
            $count = AppUser::where('email',$data['email'])->count();
            $urlIfe = '';
                $urlComprobante = '';
                $path = '/' . 'upload/ife/';

                if (isset($data['file_ife'])) {
        
                    $imagenBase64 = $data['file_ife'];
        
                    $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);
        
                    $imagenDecodificada = base64_decode($image);
        
                    $imageName =  time() . '.png';
        
                    file_put_contents(public_path($path . $imageName), $imagenDecodificada);
        
                    $urlIfe = $imageName;
                }

                $pathc = '/' . 'upload/comprobante/';

                if (isset($data['file_comprobante'])) {
        
                    $imagenBase64c = $data['file_comprobante'];
        
                    $imagec = substr($imagenBase64c, strpos($imagenBase64c, ",")+1);
        
                    $imagenDecodificadac = base64_decode($imagec);
        
                    $imageNamec =  time() . '.png';
        
                    file_put_contents(public_path($pathc . $imageNamec), $imagenDecodificadac);
        
                    $urlComprobante = $imageNamec;
                }

            if($count == 0)
            {
                if (isset($data['phone']) && $data['phone'] != 'null') {
                    $count_p = AppUser::where('phone',$data['phone'])->count();

                    if ($count_p == 0) {
                        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                            $add                = new AppUser;
                            $add->name          = ucwords($data['name']);
                            $add->last_name     = isset($data['last_name']) ? ucwords($data['last_name']) : '';
                            $add->image_pic     = isset($data['image_pic']) ? $data['image_pic'] : '';
                            $add->email         = $data['email'];
                            $add->phone         = isset($data['phone']) ? $data['phone'] : 'null';
                            $add->birth_date    = isset($data['birth_date']) ? $data['birth_date'] : 'null';
                            $add->gender        = isset($data['gender']) ? $data['gender'] : 'male';
                            $add->saldo         = isset($data['saldo']) ? $data['saldo'] : 0;
                            $add->password      = md5($data['password']);
                            $add->pswfacebook   = (isset($data['userId']) && $data['type_sign'] == 'facebook') ? $data['userId'] : 0;
                            $add->googleId      = (isset($data['userId']) && $data['type_sign'] == 'google') ? $data['userId'] : 0;
                            $add->appleId       = (isset($data['userId']) && $data['type_sign'] == 'apple') ? $data['userId'] : 0;
                            $add->status        = 1; // Bloqueado
                            $add->imagen_ife    = $urlIfe;
                            $add->imagen_comprobante = $urlComprobante;
                            $add->save();
    
                            return ['msg' => 'done','user_id' => $add->id];
                        }else {
                            return ['msg' => 'Opps! El Formato del Email es invalido'];
                        }
                    }else {
                        return ['msg' => 'Opps! Este número telefonico ya existe.'];
                    }
                }else {
                    if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                        $add                = new AppUser;
                        $add->name          = $data['name'];
                        $add->email         = $data['email'];
                        $add->phone         = isset($data['phone']) ? $data['phone'] : 'null';
                        $add->saldo         = isset($data['saldo']) ? $data['saldo'] : 0;
                        $add->password      = md5($data['password']);
                        $add->pswfacebook   = isset($data['pswfb']) ? $data['pswfb'] : 0;
                        $add->imagen_ife    = $urlIfe;
                        $add->imagen_comprobante = $urlComprobante;
                        $add->save();

                        return ['msg' => 'done','user_id' => $add->id];
                    }else {
                        return ['msg' => 'Opps! El Formato del Email es invalido'];
                    }
                } 
            }
            else
            {
                return ['msg' => 'Opps! Este correo electrónico ya existe.'];
            }
        }else {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $add                = AppUser::find($type);
                $add->name          = $data['name'];
                $add->email         = $data['email'];
                $add->phone         = isset($data['phone']) ? $data['phone'] : 'null';
                $add->saldo         = isset($data['saldo']) ? $data['saldo'] : 0;
                $add->password      = md5($data['password']);
                $add->pswfacebook   = isset($data['pswfb']) ? $data['pswfb'] : 0;
                $add->save();

                return ['msg' => 'done','user_id' => $add->id];
            }else {
                return ['msg' => 'Opps! El Formato del Email es invalido'];
            }
        }
   }

   public function SignPhone($data) 
   {
        $res = AppUser::where('id',$data['user_id'])->first();

        if(isset($res->id))
        {
            $res->phone = $data['phone'];
            $res->save();

            $return = ['msg' => 'done','user_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! Algo salió mal.'];
        }

        return $return;
   }

   public function chkUser($data)
   {
        
        if (isset($data['user_id']) && $data['user_id'] != 'null') {
            // Intentamos con el id
            $res = AppUser::find($data['user_id']);

            if (isset($res->id)) {
                /**
                 * Hasta este punto el usuario ya tiene una sesion iniciada, ya comprobo el numero telefonico
                 * y esta intentando registrarlo
                 * Comprobamos que el numero telefonico que intenta agregar no exista con otra cuenta
                 * en caso contrario se le pedira un nuevo numero telefonico
                */

                $req = AppUser::where('phone',$data['phone'])->first();
                if ($req) {
                    // El numero telefonico existe con otra cuenta
                    return ['msg' => 'phone_exist'];
                }else {
                    // Si el numero telefonico no existe lo Registramos
                    $res->phone = $data['phone'];
                    $res->save();
                    return ['msg' => 'user_exist', 'user_id' => $res->id];
                }

            }else {
                return ['msg' => 'not_exist'];
            }
        }else {
            /**
             * Hasta este punto el usuario ya se registro previamente
             * ingreso un numero telefonico y lo comprobo con codigo SMS
             * verificamos si el numero de telefono existe
            */

            $res = AppUser::where('phone',$data['phone'])->first();
            if ($res) {
                return ['msg' => 'user_exist', 'user_id' => $res->id];
            }else {
                return ['msg' => 'not_exist'];
            }
        }
   }

   public function login($data)
   {
        $flag = false;
        $dat = [];
        // Verificamos el email 
        $chkmail = AppUser::where('email',$data['email'])->first();
        if(isset($chkmail->id))
        {
            
            if ($data['type_sign'] == 'normal') { // Login normal 
                // Verificamos la contrasena
                $pass_cod = md5($data['password']);
                if ($pass_cod == $chkmail->password) {
                    $flag = true;
                    $dat = ['data' => 'done','user_id' => $chkmail->id];
                }
            }elseif ($data['type_sign'] == 'google') { // Login con google
                if ($chkmail->password == '') {
                    $chkmail->image_pic = $data['image_pic']; // Actualizamos imagen
                    $chkmail->googleId  = $data['userId'];
                    $chkmail->save();
                    $flag = true;
                    $dat = ['data' => 'done','user_id' => $chkmail->id];
                }else {
                    if ($chkmail->googleId == $data['userId']) {
                        $chkmail->image_pic = $data['image_pic']; // Actualizamos imagen
                        $chkmail->googleId  = $data['userId'];
                        $chkmail->save();
                        $flag = true;
                        $dat = ['data' => 'done','user_id' => $chkmail->id];
                    } 
                }
            }elseif ($data['type_sign'] == 'apple') { // Login con apple
                if ($chkmail->password == '') {
                    $chkmail->appleId  = $data['userId']; // Actualizamos el token
                    $chkmail->save();
                    $flag = true;
                    $dat = ['data' => 'done','user_id' => $chkmail->id];
                }else {
                    if ($chkmail->appleId == $data['userId']) { // Validamos el token
                        $chkmail->appleId  = $data['userId']; // Actualizamos el token
                        $chkmail->save();
                        $flag = true;
                        $dat = ['data' => 'done','user_id' => $chkmail->id];
                    }
                }
            }elseif ($data['type_sign'] == 'facebook') { // Login con facebook
                if ($chkmail->password == '') {
                    $chkmail->pswfacebook  = $data['userId']; // Actualizamos el token
                    $chkmail->save();
                    $flag = true;
                    $dat = ['data' => 'done','user_id' => $chkmail->id];
                }else {
                    if ($chkmail->pswfacebook == $data['userId']) { // Validamos el token
                        $chkmail->pswfacebook  = $data['userId']; // Actualizamos el token
                        $chkmail->save();
                        $flag = true;
                        $dat = ['data' => 'done','user_id' => $chkmail->id];
                    }
                }
            } 
        }

        if ($flag == true) {
            return $dat;
        }else {
            return [
                'data' => 'error',
                'msg' => 'Opps! Detalles de acceso incorrectos'
            ];
        }
   }

   public function Newlogin($data) 
   {
        $chk = AppUser::where('phone',$data['phone'])->first();

        if(isset($chk->id))
        {
            return ['msg' => 'done','user_id' => $chk->id];
        }
        else
        {
            return ['msg' => 'error','error' => 'not_exist'];
        }
   }

   public function loginfb($data) 
   {
    $chk = AppUser::where('email',$data['email'])->where('pswfacebook',$data['password'])->first();

    if(isset($chk->id))
    {
       return ['msg' => 'done','user_id' => $chk->id];
    }
    else
    {
       return ['msg' => 'Opps! Detalles de acceso incorrectos'];
    }
   }

   public function updateInfo($data,$id)
   {
      $count = AppUser::where('id','!=',$id)->where('email',$data['email'])->count();

     if($count == 0)
     {
        $add                = AppUser::find($id);
        $add->name          = $data['name'];
        $add->email         = $data['email'];
        $add->phone         = $data['phone'];
        
        if(isset($data['password']))
        {
          $add->password    = md5($data['password']);
        }

        $add->save();

        return ['msg' => 'done','user_id' => $add->id,'data' => $add];
     }
     else
     {
        return ['msg' => 'Opps! Este correo electrónico ya existe.'];
     }
   }

    public function forgot($data)
    {
        $res = AppUser::where('email',$data['email'])->first();

        if(isset($res->id))
        {
            $otp = rand(1111,9999);

            $res->otp = $otp;
            $res->save();

            $para       =   $data['email'];
            $asunto     =   'Codigo de acceso - Zendit';
            $mensaje    =   "Hola ".$res->name." Un gusto saludarte, se ha pedido un codigo de recuperacion para acceder a tu cuenta en Zendit";
            $mensaje    .=  ' '.'<br>';
            $mensaje    .=  "Tu codigo es: <br />";
            $mensaje    .=  '# '.$otp;
            $mensaje    .=  "<br /><hr />Recuerda, si no lo has solicitado tu has caso omiso a este mensaje y te recomendamos hacer un cambio en tu contrasena.";
            $mensaje    .=  "<br/ ><br /><br /> Te saluda el equipo de Zendit";
        
            $cabeceras = 'From: Zendit' . "\r\n";
            
            $cabeceras .= 'MIME-Version: 1.0' . "\r\n";
            
            $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            @mail($para, $asunto, utf8_encode($mensaje), $cabeceras);
    
            $return = ['msg' => 'done','user_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! Este correo electrónico no está registrado con nosotros.'];
        }

        return $return;
    }

    public function verify($data)
    {
        $res = AppUser::where('id',$data['user_id'])->where('otp',$data['otp'])->first();

        if(isset($res->id))
        {
            $return = ['msg' => 'done','user_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! OTP no coincide.'];
        }

        return $return;
    }

    public function updatePassword($data)
    {
        $res = AppUser::where('id',$data['user_id'])->first();

        if(isset($res->id))
        {
            $res->password      = bcrypt($data['password']);
            $res->shw_password  = $data['password'];
            $res->save();

            $return = ['msg' => 'done','user_id' => $res->id];
        }
        else
        {
            $return = ['msg' => 'error','error' => '¡Lo siento! Algo salió mal.'];
        }

        return $return;
    }

    public function countOrder($id)
    {
        return Commaned::where('user_id',$id)->where('status','>',0)->count();
    }

    public function addMoney($amount,$user,$use_mon)
    {
        $res = AppUser::where('id',$user)->first(); 
        
        if ($use_mon == true) { // El usuario ha utilizado su dinero en monedero
            // Limpiamos primero
            $res->monedero = 0;
            $res->save();   
        }

        // Agregamos el nuevo pedido al monedero
        $amount = ($res->monedero + $amount);
        $res->monedero = $amount;
        $res->save();
    }

    public function updateImage($request)
    {
        $user = AppUser::find($request->user_id);

        if (!$user) {
            return ['data' => [], 'msg' => 'Usuario no encontrado'];
        }

        $url = null;

        try {
            $fileToDelete = public_path($user->image_pic);
            if (file_exists($fileToDelete)) {
                unlink($fileToDelete);
            }
        } catch (\Throwable $th) {
        }


        $path = '/' . 'upload/user_profile/';

        if ($request->has('camera_file')) {

            $imagenBase64 = $request->input('camera_file');

            $image = substr($imagenBase64, strpos($imagenBase64, ",")+1);

            $imagenDecodificada = base64_decode($image);

            $imageName =  time() . '.png';

            file_put_contents(public_path($path . $imageName), $imagenDecodificada);

            $url = $imageName;
        }

        if (!is_Null($url)) {
            $user->fill([
               'image_pic' => $url
            ])->save();

            return ['data' => [$url], 'msg' => 'OK'];
        }

        return ['data' => [], 'msg' => 'No se puedo subir la imagen'];
    }
}
