<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEstablecimientoCercanoRequest;
use App\Http\Requests\SearchEstablecimientoRequest;
use App\Http\Requests\StoreEstablecimientoRequest;
use App\Http\Requests\UpdateEstablecimientoRequest;
use App\Models\Establecimiento;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador encargado de la gestión de establecimientos.
 *
 * Se ha definido como resource puesto que se necesita un CRUD completo
 */
class EstablecimientoController extends Controller
{
    /**
     * Listado con buscador de establecimientos
     *
     * @response
     *   0: Ok
     * -11: Exception
     */
    //TODO
    public function index()
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al index de EstablecimientoController");

            //ACCIÓN
            $response["data"] = Establecimiento::all();
            $response["code"] = 0;
            $response["status"] = 200;
            $response["statusText"] = "OK";

            //Log de salida
            Log::debug("Saliendo del index del EstablecimientoController");
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error($e->getMessage(), array($response));
        }

        //Montamos el response
        $responseAux = view("establecimientos.listado")->with("response", $response);

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("establecimientos.indexko"));
        }else{
            //Respuesta OK
            //Nada
        }

        return $responseAux;
    }

    /**
     * Devuelve los establecimientos de un usuario
     *
     * @return Establecimiento[] Listado de establecimientos
     *   0: OK
     * -11: Excepción
     * -12: Error al leer los establecimientos del usuario
     */
    public function misEstablecimientos()
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al misEstablecimientos de EstablecimientoController",
                array(
                    "userID:" => auth()->user()->id
                )
            );

            //ACCIÓN
            $resultEstablecimientos = Establecimiento::dameMisEstablecimientos(auth()->user());

            if($resultEstablecimientos["code"] == 0){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "OK";
                $response["data"] = $resultEstablecimientos["data"];
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "KO";
            }

            //Log de salida
            Log::debug("Saliendo del misEstablecimientos del EstablecimientoController",
                array(
                    "userID:" => auth()->user()->id,
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID" => auth()->user()->id,
                    "response:" => $response
                )
            );
        }

        return response()->json($response["data"], $response["status"]);
    }

    /**
     * Mostrar el formulario de creación de establecimientos
     *
     * @response
     *   0: Ok
     * -11: Excepción
     */
    //TODO
    public function create()
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al create de EstablecimientoController", array("userID:" => auth()->user()->id));

            //ACCIÓN
            $response["code"] = 0;
            $response["status"] = 200;
            $response["statusText"] = "OK";

            //Log de salida
            Log::debug("Saliendo del create del EstablecimientoController", array("userID:" => auth()->user()->id, "response:" => $response));
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error($e->getMessage(), array("userID" => auth()->user()->id, "response:" => $response));
        }

        //Montamos el response
        $responseAux = view("establecimientos.createandedit")->with("response", $response);

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("establecimientos.createko"));
        }else{
            //Respuesta OK
        }

        return $responseAux;
    }

    /**
     * Almacena en base de datos el establecimiento pasado por parámetros
     *
     * @param StoreEstablecimientoRequest $request la información del establecimiento
     *
     * @response
     *
     *    0: Ok
     *  -11: Excepción
     *  -12: Error al almacenar el establecimiento
     *  -13: Error al almacenar el logo
     *  -14: Error al almacenar la ruta del logo en bd
     */
    public function store(StoreEstablecimientoRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al store de establecimientoController",
                array(
                    "userID: " =>auth()->user()->id,
                    "request:" => $request->all()
                )
            );

            //Creamos el establecimiento
            $respuestaModelo = Establecimiento::crearEstablecimiento(
                $request->get("nombre"),
                $request->get("direccion"),
                $request->get("descripcion"),
                auth()->user()->id,
                $request->get("latitud"),
                $request->get("longitud")
            );

            if($respuestaModelo["code"] == 0){
                //Se ha almacenado correctamente el nuevo establecimiento, pasando a almacenar el logo si lo hay
                if($request->file("logo")){
                    $rutaLogo = $this->almacenarLogo($request->file("logo"), $respuestaModelo["data"]["id"]);

                    if(!empty($rutaLogo)){
                        //Ahora guardo el logo en la base de datos
                        $statusAlmacenarLogoEnBD = Establecimiento::almacenarLogoEstablecimientoEnBD($rutaLogo, $respuestaModelo["data"]["id"]);

                        if($statusAlmacenarLogoEnBD["code"] == 0){
                            $response["data"] = $respuestaModelo["data"];
                            $response["data"]->logo = $rutaLogo;

                            $response["code"] = 0;
                            $response["status"] = 200;
                            $response["statusText"] = "ok";
                        }else{
                            $response["code"] = -14;
                            $response["status"] = 400;
                            $response["statusText"] = "ko";
                        }
                    }else{
                        $response["code"] = -13;
                        $response["status"] = 400;
                        $response["statusText"] = "ko";
                    }
                }else{
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";
                    $response["data"] = $respuestaModelo["data"];
                }
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug(
                "Saliendo del store del establecimientoControlador",
                array(
                    "userID:" => auth()->user()->id,
                    "request:" => $request->all(),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "ko";

            Log::error($e->getMessage(),
                array(
                    "userID:" => auth()->user()->id,
                    "request:" => $request->all(),
                    "response:" => $response
                )
            );
        }

        return response()->json(
            $response["data"],
            $response["status"]
        );
    }


    /**
     * Método que se encarga de devolver los datos del establecimiento junto con los usuarios encolados.
     * También comprueba si hay sesión, si el usuario está encolado y tiene un favorito
     *
     * @param Establecimiento $establecimiento El establecimiento a mostrar
     *
     * @return {usuariosEncolados: UsuarioEnCola[], establecimiento: Establecimiento, establecimientoFavorito: bool, usuarioEnCola: bool}
     *   0: OK
     * -11: Excepción
     * -12: No se ha podido leer los usuarios encolados
     */
    public function show(Establecimiento $establecimiento, Request $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al show del establecimientoscontroller",
                array(
                    "request:" => $establecimiento
                )
            );

            //Acción
            //Leemos todos los usuarios encolados en este establecimiento
            $usuariosEncolados = Establecimiento::dameUsuariosEncolados($establecimiento);

            if($usuariosEncolados["code"] == 0){
                $usuariosEncolados = $usuariosEncolados["data"];

                //Comprobamos si hay sesión, si es así, miramos si el usuario tiene como fav el establecimiento y si está encolado
                $establecimientoFavorito = null;
                $usuarioEnCola = null;

                if(auth()->user()){
                    $usuarioTieneFavoritoResult = User::elUsuarioTieneAlEstablecimientoComoFavorito(auth()->user()->id, $establecimiento);
                    if($usuarioTieneFavoritoResult["code"] == 0 &&
                    $usuarioTieneFavoritoResult["data"] == true){
                        $establecimientoFavorito = true;
                    }else if($usuarioTieneFavoritoResult["code"] == 0 &&
                    $usuarioTieneFavoritoResult["data"] == false){
                        $establecimientoFavorito = false;
                    }else{
                        //Aquí no se debería llegar
                        Log::error("la llamada a elUsuarioTieneAlEstablecimientoComoFavorito ha fallado",
                            array(
                                "request:" => $establecimiento
                            )
                        );
                    }

                    $usuarioEstaEnColaResult = Establecimiento::comprobarUsuarioEnCola(auth()->user()->id, $establecimiento);
                    if($usuarioEstaEnColaResult["code"] == 0 &&
                    $usuarioEstaEnColaResult["data"] == true){
                        $usuarioEnCola = true;
                    }else{
                        //Aquí no se debería llegar
                        Log::error("la llamada a comprobarUsuarioEnCola ha fallado",
                            array(
                                "request:" => $establecimiento
                            )
                        );
                    }
                }

                $response["data"] = compact(["usuariosEncolados", "establecimiento", "establecimientoFavorito", "usuarioEnCola"]);
                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "ok";
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug(
                "Saliendo del show del establecimientoControlador",
                array(
                    "request:" => $establecimiento,
                    "response:" => $response));
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "ko";

            Log::error(
                $e->getMessage(),
                array(
                "request:" => $establecimiento,
                "response:" => $response
            ));
        }

        return response()->json($response["data"], $response["status"]);
    }

    //TODO
    public function buscarEstablecimientosCercanos(SearchEstablecimientoCercanoRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al buscarEstablecimientosCercanos del establecimientoscontroller",
                array(
                    "request:" => $request->all())
            );

            //Acción
            $result = Establecimiento::buscarEstablecimientosCercanos(
                $request->get("latitud"),
                $request->get("longitud")
            );

            if($result["code"] == 0){

                $response["data"]["busqueda"]["resultado"] = $result["data"];
                $response["data"]["busqueda"]["cadenaBuscada"] = "";

                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "OK";
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "KO";
            }

            //Log de salida
            Log::debug(
                "Saliendo del buscarEstablecimientosCercanos del establecimientoControlador",
                array(
                    "request:" => $request->all(),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request:" => $request->all(),
                    "response:" => $response
                )
            );
        }

        //Montamos el response
        $responseAux = view("establecimientos.listado")->with("response", $response);

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("establecimientos.busquedako"));
        }else{
            //Respuesta OK
            //Nada
        }

        return $responseAux;
    }

    /**
     * Función para realizar una búsqueda de establecimientos
     *
     * @param SearchEstablecimientoRequest $request La cadena a buscar
     *
     * @return Establecimiento[] Listado de establecimientos. Incluye count con el # de usuarios encolados
     *   0: OK
     * -11: Escepción
     * -12: Error al buscar el establecimiento
     */
    public function buscarEstablecimientos(SearchEstablecimientoRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al buscarEstablecimientos del establecimientoscontroller",
                array(
                    "request:" => $request->all()
                )
            );

            //Acción
            $establecimientos = Establecimiento::buscarEstablecimiento($request->campobusqueda);

            if($establecimientos["code"] == 0){
                $response["data"] = $establecimientos["data"];
                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "ok";
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug(
                "Saliendo del buscarEstablecimientos del establecimientoControlador",
                array(
                    "request:" => $request->all(),
                    "response:" => $response));
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "ko";

            Log::error(
                $e->getMessage(),
                array(
                    "request:" => $request->all(),
                    "response:" => $response
                )
            );
        }

        return response()->json($response["data"], $response["status"]);
    }

    /**
     * FUnción para actualizar un establecimiento
     *
     * @param UpdateEstablecimientoRequest $request Los nuevos campos del establecimiento
     * @param Establecimiento $establecimiento El establecimiento a modificar
     *
     * @return Establecimiento El establecimiento con los campos actualizados
     *
     *   0: OK
     * -11: Excepción
     * -12: Error al actualizar los campos
     * -13: Error al almacenar la nueva ruta del logo
     */
    public function update(UpdateEstablecimientoRequest $request, Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug(
                "Entrando a update del EstablecimientosController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $request->all(),
                    "establecimiento: " => $establecimiento
                )
            );

            //ACCIÓN
            $respuestaUpdate = Establecimiento::updateEstablecimiento(
                $establecimiento,
                $request->nombre,
                $request->direccion,
                $request->descripcion,
                $request->latitud,
                $request->longitud);

            if($respuestaUpdate["code"] == 0){
                //Ahora almaceno el nuevo logo
                if(!empty($request->file("logo"))){
                    $nuevaRutaLogo = $this->almacenarLogo($request->file("logo"), $establecimiento->id);

                    if($nuevaRutaLogo){
                        $respuestaGuardarRutaLogo = Establecimiento::almacenarLogoEstablecimientoEnBD($nuevaRutaLogo, $establecimiento->id);

                        if($respuestaGuardarRutaLogo["code"] == 0){
                            $respuestaUpdate["data"]->logo = $nuevaRutaLogo;
                            $response["code"] = 0;
                            $response["status"] = 200;
                            $response["statusText"] = "ok";
                            $response["data"] = $respuestaUpdate["data"];
                        }else{
                            $response["code"] = -14;
                            $response["status"] = 400;
                            $response["statusText"] = "ko";
                        }
                    }else{
                        $response["code"] = -13;
                        $response["status"] = 400;
                        $response["statusText"] = "ko";
                    }
                }else{
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";
                    $response["data"] = $respuestaUpdate["data"];
                }
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug(
                "Saliendo del update del establecimientoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => ["nuevosCampos: " => $request->all(), "establecimiento: " => $establecimiento],
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
                    "userID: " => auth()->user()->id,
                    "request: " => ["nuevosCampos: " => $request->all(), "establecimiento: " => $establecimiento],
                    "response: " => $response
                )
            );
        }

        return response()->json($response["data"], $response["status"]);

    }

    /**
     * FUnción de eliminación del establecimiento pasado como param
     *
     * @param Establecimiento $establecimiento El establecimiento a borrar
     *
     * @return void
     *   0: OK
     * -11: Excepción
     * -12: Error al borrar el establecimiento
     */
    public function destroy(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al destroy de establecimientoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request:" => $establecimiento)
            );

            //ACCIÓN
            $resultadoBorradoModelo = Establecimiento::eliminarEstablecimiento($establecimiento);

            if($resultadoBorradoModelo["code"] == 0){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "ok";
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";
            }

            //Log de salida
            Log::debug(
                "Saliendo del destroy del establecimientoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
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
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return response()->json([], $response["status"]);
    }

    /**
     * Función encargada de almacenar el logo pasado por parámetros en el storage
     *
     * @param file $logo El logo a almacenar
     * @param int $establecimientoID El establecimiento
     *
     * @return string|null
     */
    public function almacenarLogo($logo, int $establecimientoID)
    {
        $path = null;

        try{
            $path = $logo->storeAs("public/establecimientos/" . $establecimientoID . "/images", "logo.". $logo->extension());
        }catch(Exception $e){
            Log::error($e->getMessage());
        }

        //Sustituyo el public por el storage para que en el cliente se muestren bien ya que esto será una ruta de la carpeta publica del storage
        $path = str_replace("public/", "storage/", $path);

        return $path;
    }
}
