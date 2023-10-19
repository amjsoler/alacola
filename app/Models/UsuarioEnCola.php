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

    ///////////////
    // Relations //
    ///////////////

    /**
     * El usuario asociado al UsuarioEnCola
     *
     * @return BelongsTo User
     */
    public function usuario() : BelongsTo
    {
        return $this->belongsTo(User::class, "usuario_en_cola", "id");
    }

    /**
     * El establecimiento asociado al usuarioEnCola
     *
     * @return BelongsTo Establecimiento
     */
    public function establecimiento() : BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, "establecimiento_cola", "id");
    }

    ///////////////////////
    // Métodos estáticos //
    ///////////////////////

    /**
     * Función para que un usuario logueado se apunte a la cola de un establecimiento
     *
     * @param int $userID El id del usuario
     * @param Establecimiento $establecimiento El establecimiento al que se apunta el usuario
     *
     * @return UsuarioEnCola
     *  0: OK
     * -1: Excepción
     * -2: Error al almacenar el usuarioEnCola
     */
    public static function usuarioLogueadoSeApuntaACola(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al usuarioLogueadoSeApuntaACola del Establecimiento",
                array(
                    "request: " => compact("userID", "establecimiento"),
                )
            );

            //Acción
            $usuarioEncolado = new UsuarioEnCola();
            $usuarioEncolado->usuario_en_cola = $userID;
            $usuarioEncolado->establecimiento_cola = $establecimiento->id;

            if($usuarioEncolado->save()){
                $response["code"] = 0;
                $response["data"] = $usuarioEncolado;
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del usuarioLogueadoSeApuntaACola del Establecimiento model",
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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
     * Función para que un usuario se desapunte de la cola de un establecimiento
     *
     * @param int $userID El id del usuario
     * @param Establecimiento $establecimiento El establecimiento del cual se desapunta
     *
     * @return bool Si se ha desapuntado o no
     */
    public static function usuarioLogueadoSeDesapuntaDeLaCola(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al usuarioLogueadoSeDesapuntaDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("userID", "establecimiento"),
                )
            );

            //Acción
            $desencolarResult = UsuarioEnCola::where("usuario_en_cola", $userID)
                ->where("establecimiento_cola", $establecimiento->id)
                ->first()
                ->delete();

            if($desencolarResult){
                $response["code"] = 0;
                $response["data"] = true;
            }else{
                $response["code"] = -2;
                $response["data"] = false;
            }

            //Log de salida
            Log::debug(
                "Saliendo del usuarioLogueadoSeDesapuntaDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("userID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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
     * @return void
     *  0: OK
     * -1: Excepción
     * -2: No se ha podido desapuntar al usuario
     */
    public static function adminDesapuntaUsuarioDeLaCola(UsuarioEnCola $usuarioEnCola)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al adminDesapuntaUsuarioDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnCola"),
                )
            );

            //Acción
            $actionResult = $usuarioEnCola->delete();

            if($actionResult){
                $response["code"] = 0;
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del adminDesapuntaUsuarioDeLaCola del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnCola"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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

    /**
     * Método para poder apuntar a un usuario anónimo a la cola de un establecimiento
     *
     * @param string $nombre_usuario_anonimo El nombre del usuario anónimo que se mostrará
     * @param int $establecimientoID El establecimiento al que se apunta
     *
     * @return UsuarioEnCola
     *  0: OK
     * -1: Excepción
     * -2: No se ha podido almacenar el nuevo UsuarioEnCola
     */
    public static function usuarioInvitadoSeApunta(string $nombre_usuario_anonimo, int $establecimientoID)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al usuarioInvitadoSeApunta del UsuarioEnCola",
                array(
                    "request: " => compact("nombre_usuario_anonimo", "establecimientoID"),
                )
            );

            //Acción
            $usuarioEnCola = new UsuarioEnCola();
            $usuarioEnCola->nombre_usuario_anonimo = $nombre_usuario_anonimo;
            $usuarioEnCola->establecimiento_cola = $establecimientoID;

            if($usuarioEnCola->save()){
                $response["code"] = 0;
                $response["data"] = $usuarioEnCola->fresh();
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del usuarioInvitadoSeApunta del UsuarioEnCola",
                array(
                    "request: " => compact("nombre_usuario_anonimo", "establecimientoID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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
     * @param int $establecimientoID El establecimiento en el que buscar
     *
     * @return  void
     *   0: OK
     *  -1: Excepción
     *  -2: Error al intentar deapuntar al usuarioEnCola
     */
    public static function usuarioEnColaIDSeDesapunta(int $usuarioEnColaID, int $establecimientoID)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al usuarioEnColaIDSeDesapunta del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnColaID", "establecimientoID"),
                )
            );

            //Acción
            $usuarioEnCola = UsuarioEnCola::where("id", $usuarioEnColaID)
                ->where("establecimiento_cola", $establecimientoID)
                ->where("usuario_en_cola", null)
                ->first();

            if($usuarioEnCola->delete()){
                $response["code"] = 0;
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del usuarioEnColaIDSeDesapunta del UsuarioEnCola",
                array(
                    "request: " => compact("usuarioEnColaID", "establecimientoID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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

    /**
     * Método para pasar turno en la cola de un establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return UsuarioEnCola El UsuarioEnCola desencolado
     */
    public static function adminPasaTurno(Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al adminPasaTurno del UsuarioEnCola",
                array(
                    "request: " => compact("establecimiento"),
                )
            );

            //Acción
            $usuarioEnCola = $establecimiento->usuariosEncolados()
                ->orderBy("momentoestimado", "asc")
                ->first();

            if($usuarioEnCola->delete()){
                $response["code"] = 0;
                $response["data"] = $usuarioEnCola;
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del adminPasaTurno del UsuarioEnCola",
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
