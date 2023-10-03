<?php

namespace App\Http\Controllers;

use App\Events\PasaTurnoEstablecimiento;
use App\Http\Requests\ApuntarseComoInvitadoRequest;
use App\Models\Establecimiento;
use App\Models\UsuarioEnCola;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

/**
 * Clase encargada de encolar usuarios en los establecimientos
 */
class UsuarioEnColaController extends Controller
{
    /**
     * Función para encolar usuarios en el establecimiento pasado por parámetro
     *
     * @param Establecimiento $establecimiento El establecimiento donde se encola el usuario logueado
     *
     * @return \Illuminate\Http\RedirectResponse
     *   0: OK
     * -11: Excepción
     * -12: El usuario ya estaba encolado de forma activa en el establecimiento
     * -13: Error al guardar en BD el modelo
     */
    public function encolar(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al encolar  de UsuarioEnColaController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $establecimiento)
            );

            //Comprobar que no esté apuntado ya en ese establecimiento
            $comprobarEnCola = Establecimiento::comprobarUsuarioEnCola(auth()->user()->id, $establecimiento);

            //Usuario ya apuntado
            if($comprobarEnCola["code"] == 0 && $comprobarEnCola["data"] == false)
            {
                //ACCIÓN
                $apuntarseResult = UsuarioEnCola::usuarioLogueadoSeApuntaACola(auth()->user()->id, $establecimiento);

                if($apuntarseResult["code"] == 0){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["message"] = "OK";
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["message"] = "KO";
                }
            }
            else{
                Log::error("El usuario no debería haber podido llegar hasta aquí. Ha intentado apuntarse cuando ya estaba apuntado",
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => $establecimiento,
                        "response: " => $response
                    )
                );

                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";
            }

            //Log de salida
            Log::info(
                "Saliendo del encolar del UsuarioEnColaController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $establecimiento,
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $establecimiento,
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = redirect()->back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __( "usuariosencola.encolarko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __( "usuariosencola.encolarok"));
        }

        return $responseAux;
    }

    /**
     * Método para desencolar a un usuario de un establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento del que desencolar
     *
     * @return \Illuminate\Http\RedirectResponse
     *    0: OK
     *  -11: Excepción
     *  -12: El usuario no estaba encolado por lo que no se ha podido desencolar
     *  -13: Fallo en la consulta, no se ha podido eliminar al usuario de la cola del establecimiento
     */
    public function desencolar(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al desencolar  de UsuarioEnColaController",
                array(
                    "userID: " => auth()->user(),
                    "request: " => $establecimiento
                )
            );

            //Comprobar que esté apuntado ya en ese establecimiento
            $comprobarUsuarioEncolado = Establecimiento::comprobarUsuarioEnCola(auth()->user()->id, $establecimiento);

            if($comprobarUsuarioEncolado["code"] == 0 && $comprobarUsuarioEncolado["data"] == true){
                //Borramos al usuario de la cola del establecimiento
                $desencolarUsuarioResult = UsuarioEnCola::usuarioLogueadoSeDesapuntaDeLaCola(auth()->user()->id, $establecimiento);

                if($desencolarUsuarioResult["code"] == 0){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["message"] = "OK";

                    Log::info("Usuario en cola desencolado correctamente",
                        array(
                            "userID: " => auth()->user(),
                            "request: " => $establecimiento
                        )
                    );
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["message"] = "KO";

                    Log::info("No se ha podido quitar al usuario encolado de la cola",
                        array(
                            "userID: " => auth()->user(),
                            "request: " => $establecimiento
                        )
                    );
                }
            }else{
                //Error porque no está encolado en el establecimiento
                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";

                Log::error("El usuario no debería haber llegado al desencolar si no está encolado",
                    array(
                        "userID: " => auth()->user(),
                        "request: " => $establecimiento
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo del desencolar del UsuarioEnColaController",
                $response);
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user(),
                    "request: " => $establecimiento
                )
            );
        }

        //Montamos el response
        $responseAux = back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("usuariosencola.desencolarko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __("usuariosencola.desencolarok"));
        }

        return $responseAux;
    }

    /**
     * Función para que un admin desencole al usuario del establecimiento pasado como parametro
     *
     * @param Establecimiento $establecimiento El establecimiento del que desencolar
     * @param UsuarioEnCola $usuarioEnCola El usuario a desencolar
     *
     * @return \Illuminate\Http\RedirectResponse
     *   0: OK
     * -11: Excepción
     * -12: El usuario no estaba encolado
     * -13: No se ha podido guardar en bd el modelo
     */
    public function adminDesapunta(Establecimiento $establecimiento, UsuarioEnCola $usuarioEnCola)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al adminDesapunta  de UsuarioEnColaController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento", "usuarioEnCola")
                )
            );

            //¿Puede el usuario logueado realizar esta acción?
            try{
                $this->authorize("adminDesapuntaUser", [$usuarioEnCola, $establecimiento]);
            }catch(AuthorizationException $e){
                $response["message"] = "KO";
                $response["code"] = -10;
                $response["status"] = 403;

                Log::error(
                    $e->getMessage(),
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => compact("establecimiento"),
                        "response: " => $response
                    )
                );

                return redirect(route("noautorizado"));
            }

            //Acción
            if(UsuarioEnCola::adminDesapuntaUsuarioDeLaCola($usuarioEnCola)){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["message"] = "OK";
            }else{
                //No se ha podido desencolar al usuarioEnCola
                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";

                Log::error("El usuario no debería haber llegado al desencolar si no está encolado",
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => compact("establecimiento", "usuarioEnCola"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo del desencolar del UsuarioEnColaController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento", "usuarioEnCola"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento", "usuarioEnCola"),
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("usuariosencola.admindesencolarko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __("usuariosencola.admindesencolarok"));
        }

        return $responseAux;
    }

    /**
     * Funcion que permite pasar turno al admin
     *
     * @param Establecimiento $establecimiento el establecimiento que queremos pasar turno
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function adminPasaTurno(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al adminPasaTurno  de UsuarioEnColaController", array($establecimiento));

            //TODO: Authorization

            //Comprobar que esté apuntado ya en esa empresa
            $siguienteEnLaCola = UsuarioEnCola::where("establecimiento_cola", $establecimiento->id)
                ->where("activo", true)
                ->orderBy('momentoestimado', "asc")
                ->first();

            if($siguienteEnLaCola != null){
                //Borramos al usuario de la cola del establecimiento
                $siguienteEnLaCola->activo = false;
                if($siguienteEnLaCola->save()){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["message"] = "OK";

                    Log::info("Se ha pasado el turno satisfactoriamente",
                        array($response, $establecimiento));

                    PasaTurnoEstablecimiento::dispatch();
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["message"] = "KO";

                    Log::info("No se ha podido guardar el estado de no activo en el registro",
                        array($response, $establecimiento));
                }
            }else{
                //Error porque no está encolado en el establecimiento
                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";

                Log::error("El admin no debería haber llegado aquí si no  hay nadie encolado",
                    array($establecimiento, $response));
            }

            //Log de salida
            Log::info(
                "Saliendo del adminPasaTurno del UsuarioEnColaController",
                $response);
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(), array($establecimiento));
        }

        //Montamos el response
        $responseAux = back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("usuariosencola.adminpasaturnoko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __("usuariosencola.adminpasaturnook"));
        }

        return $responseAux;
    }

    public function encolarComoAnonimo(Establecimiento $establecimiento, ApuntarseComoInvitadoRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al encolarComoAnonimo  de UsuarioEnColaController",
                array(
                    "request: " => ["establecimiento" => $establecimiento, "request" => $request->except("_token")]
                )
            );

            $result = UsuarioEnCola::usuarioInvitadoSeApunta($request->get("nombre_usuario_anonimo"), $establecimiento->id);

            if($result["code"] == 0){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["message"] = "OK";
                $response["data"] = $result["data"];
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";

                Log::error(
                    "Error al apuntarse usuario anonimo a la cola. La llamada almodel no ha devuelto un code 0",
                    array(
                        "request: " => ["establecimiento" => $establecimiento, "request" => $request->except("_token")],
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo del encolarComoAnonimo del UsuarioEnColaController",
                array(
                    "request: " => ["establecimiento" => $establecimiento, "request" => $request->except("_token")],
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "request: " => ["establecimiento" => $establecimiento, "request" => $request->except("_token")],
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("usuariosencola.encolarcomoanonimoko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __("usuariosencola.encolarcomoanonimook"))
                ->withCookie("usuarioAnonimoID", $response["data"]["id"])
                ->withCookie("nombre_usuario_anonimo", $response["data"]["nombre_usuario_anonimo"]);
        }

        return $responseAux;
    }

    public function desencolarComoAnonimo(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "message" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::info("Entrando al desencolarComoAnonimo  de UsuarioEnColaController",
                array(
                    "request: " => ["establecimiento" => $establecimiento]
                )
            );

            $result = UsuarioEnCola::usuarioEnColaIDSeDesapunta(Cookie::get("usuarioAnonimoID"), $establecimiento->id);

            if($result["code"] == 0){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["message"] = "OK";
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["message"] = "KO";

                Log::error(
                    "Error al desapuntarse usuario anonimo a la cola. La llamada almodel no ha devuelto un code 0",
                    array(
                        "request: " => ["establecimiento" => $establecimiento],
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo del desencolarComoAnonimo del UsuarioEnColaController",
                array(
                    "request: " => ["establecimiento" => $establecimiento],
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "request: " => ["establecimiento" => $establecimiento],
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = back();

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("usuariosencola.desencolarcomoanonimoko"));
        }else{
            //Respuesta OK
            $responseAux->with("ok", __("usuariosencola.desencolarcomoanonimook"));
        }

        return $responseAux;
    }
}
