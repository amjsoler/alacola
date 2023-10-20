<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarNuevaContrasena;
use App\Models\AccountVerify;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Authentication extends Controller
{
    /**
     * Método para verificar una cuenta de usuario dado un token
     *
     * @param string $token El token asociado a la cuenta de usuario
     *
     * @return null
     *   0: OK
     * -11: Excepción
     * -12: Token no encontrado o no valido
     * -13: Error al marcar la cuenta como verificada
     */
    public function verificarCuentaConToken(string $token)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al verificarCuentaConToken de Authentication",
            array(
                "request: " => compact("token")
            ));

            //Consulto el token y veo si todavía es válido
            $result = AccountVerify::consultarToken($token);

            $accountVerifyResult = $result["data"];

            if($accountVerifyResult){
                $resultMarcarVerificacion = User::marcarCuentaVerificada($accountVerifyResult->user);

                if($resultMarcarVerificacion["code"] == 0){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["statusText"] = "ko";
                }
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug("Saliendo del verificarCuentaConToken de Authentication",
                array(
                    "request: " => compact("token"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "ko";

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("token"),
                    "repsonse: " => $response
                )
            );
        }

        return view("cuentaUsuario/verificarCuenta", compact("response"));
    }

    public function guardarNuevaContrasena(GuardarNuevaContrasena $request)
    {
        //Comprobar token
        $resultToken = AccountVerify::consultarToken($request->get("token"));

        if($resultToken["data"]){
            //TODO: Cambiar la siguiente linea
            $user = User::find($resultToken["data"]->user)->first();

            $user->password = Hash::make($request->get("password"));

            $user->save();

            return "La contraseña se ha cambiado correctamente. Ya puedes cerrar esta ventana y volver a la app";
        }else{

        }
    }
}
