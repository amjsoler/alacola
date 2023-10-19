<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountVerify extends Model
{
    use HasFactory;

    protected $primaryKey = "id";

    protected $table = "account_verify";


    ////////////////
    // RELACIONES //
    ////////////////

    public function usuario() : HasOne
    {
        return $this->hasOne(User::class, "user", "id");
    }

    ///////////////////////
    // MÉTODOS ESTÁTICOS //
    ///////////////////////

    /**
     * Método para crear un nuevo token de verificación
     *
     * @param int $userID El usuario al que se asocia el token
     *
     * @return AccountVerify Nuevo token creado para la verificación de la cuenta
     *  0: OK
     * -1: Excepción
     * -2: No se ha podido guardar el nuevo token
     */
    public static function crearTokenDeVerificación(int $userID, $validez)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al crearTokenDeVerificación de AccountVerify",
                array(
                    "request: " => compact("userID")
                )
            );

            //Acción
            $nuevoAccountVerify = new AccountVerify();
            $nuevoAccountVerify->user = $userID;
            $nuevoAccountVerify->token = Hash::make(now());
            $nuevoAccountVerify->valido_hasta = $validez;

            if($nuevoAccountVerify->save()){
                $response["code"] = 0;
                $response["data"] = $nuevoAccountVerify;
            }
            else{
                $response["code"] = -2;

                Log::error("Fallo al crear el token",
                    array(
                        "request: " => compact("userID"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug("Saliendo del crearTokenDeVerificación de AccountVerify",
                array(
                    "request: " => compact("userID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("userID"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Método que devuelve el token pasado como parámetro en caso de que todavía sea válido
     *
     * @param string $token El token a buscar
     *
     * @return AccountVerify
     *   0: OK
     *  -1: Excepción
     */
    public static function consultarToken(string $token)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al consultarToken de AccountVerify",
                array(
                    "request: " => compact("token")
                )
            );

            //Acción
            $token = AccountVerify::where("token", "=", $token)
                ->where("valido_hasta", ">", now())
                ->first();

            $response["code"] = 0;
            $response["data"] = $token;

            //Log de salida
            Log::debug("Saliendo del consultarToken de AccountVerify",
                array(
                    "request: " => compact("token"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("token"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
