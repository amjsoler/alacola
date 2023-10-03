<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class UsuarioEnCola extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = "id";

    protected $table = "usuarios_en_cola";

    //Un UserEnCola solo puede referenciar a un usuario
    public function usuario() : BelongsTo
    {
        return $this->belongsTo(User::class, "usuario_en_cola", "id");
    }

    //Un establecimiento en cola solo puede referenciar a un establecimiento
    public function establecimiento() : BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, "establecimiento_cola", "id");
    }

    /**
     * Función para que un usuario logueado se apunte a la cola de un establecimiento
     *
     * @param int $userID El id del usuario
     * @param Establecimiento $establecimiento El establecimiento al que se apunta el usuario
     *
     * @return UsuarioEnCola
     */
    public static function usuarioLogueadoSeApuntaACola(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al usuarioLogueadoSeApuntaACola del Establecimiento",
                array(
                    "request: " => compact("userID", "establecimiento"),
                )
            );

            //Acción
            $usuarioEncolado = new UsuarioEnCola();
            $usuarioEncolado->usuario_en_cola = $userID;
            $usuarioEncolado->establecimiento_cola = $establecimiento->id;
            $usuarioEncolado->momentoestimado = now();

            if($usuarioEncolado->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $usuarioEncolado;
            }else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = false;
            }

            //Log de salida
            Log::info(
                "Saliendo del usuarioLogueadoSeApuntaACola del Establecimiento model",
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Funci´no para que un usuario se desapunte de la cola de un establecimiento
     *
     * @param int $userID El id del usuario
     * @param Establecimiento $establecimiento El establecimiento del cual se desapunta
     *
     * @return string[]
     */
    public static function usuarioLogueadoSeDesapuntaDeLaCola(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al usuarioLogueadoSeDesapuntaDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("userID", "establecimiento"),
                )
            );

            //Acción
            $desencolarResult = UsuarioEnCola::where("usuario_en_cola", $userID)->where("establecimiento_cola", $establecimiento->id)
                ->where("activo", true)->first();

            $desencolarResult->activo = false;

            if($desencolarResult->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $desencolarResult;
            }else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }

            //Log de salida
            Log::info(
                "Saliendo del usuarioLogueadoSeDesapuntaDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Función que se usa para que un admin desapunte a un usuario de la cola
     *
     * @param UsuarioEnCola $usuarioEnCola el usuario a desencolar
     *
     * @return string[]
     */
    public static function adminDesapuntaUsuarioDeLaCola(UsuarioEnCola $usuarioEnCola)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al adminDesapuntaUsuarioDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnCola"),
                )
            );

            //Acción
            $usuarioEnCola->activo = false;

            if($usuarioEnCola->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }

            //Log de salida
            Log::info(
                "Saliendo del adminDesapuntaUsuarioDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnCola"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("usuarioEnCola"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    public static function usuarioInvitadoSeApunta(string $nombre_usuario_anonimo, int $establecimientoID)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al usuarioInvitadoSeApunta del UsuarioEnCola",
                array(
                    "request: " => compact("nombre_usuario_anonimo", "establecimientoID"),
                )
            );

            //Acción
            $usuarioEnCola = new UsuarioEnCola();
            $usuarioEnCola->nombre_usuario_anonimo = $nombre_usuario_anonimo;
            $usuarioEnCola->establecimiento_cola = $establecimientoID;

            if($usuarioEnCola->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $usuarioEnCola;
            }else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }

            //Log de salida
            Log::info(
                "Saliendo del usuarioInvitadoSeApunta del UsuarioEnCola",
                array(
                    "request: " => compact("nombre_usuario_anonimo", "establecimientoID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("nombre_usuario_anonimo", "establecimientoID"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Función para desapuntar a un usuario de la cola cuando no está dado de alta como usuario
     *
     * @param int $usuarioEnColaID El usuarioencola a deaspuntar
     *
     * @return string[]
     *   0: OK
     *  -1: Excepción
     *  -2: Error al intentar deapuntar al usuarioEnCola
     */
    public static function usuarioEnColaIDSeDesapunta(int $usuarioEnColaID, int $establecimientoID)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al usuarioEnColaIDSeDesapunta del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnColaID", "establecimientoID"),
                )
            );

            //Acción
            $usuarioEnCola = UsuarioEnCola::where("id", $usuarioEnColaID)
                ->where("establecimiento_cola", $establecimientoID)
                ->where("activo", true)
                ->where("usuario_en_cola", null)
                ->first();

            $usuarioEnCola->activo = false;

            if($usuarioEnCola->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("No se debería haber podido llegar hasta aquí, el validador tendría que haber capado antes",
                    array(
                        "request: " => compact("usuarioEnColaID", "establecimientoID"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo del usuarioEnColaIDSeDesapunta del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnColaID", "establecimientoID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("usuarioEnColaID", "establecimientoID"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
