<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchEstablecimientoCercanoRequest;
use App\Http\Requests\SearchEstablecimientoRequest;
use App\Http\Requests\StoreEstablecimientoRequest;
use App\Http\Requests\UpdateEstablecimientoRequest;
use App\Models\Establecimiento;
use App\Models\User;
use Exception;
use http\Env\Request;
use Illuminate\Auth\Access\AuthorizationException;
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

    //TODO
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

        //Montamos el response
        $responseAux = view("establecimientos.mis-establecimientos")->with("response", $response);

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("establecimientos.misestablecimientosko"));
        }else{
            //Respuesta OK
        }

        return $responseAux;
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
     *  -14: Error al almacenar el nombre del logo en bd
     */
    //TODO
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
                array("userID: " =>auth()->user()->id,
                    "request:" => $request->all()));

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
                    $statusArchivo = $this->almacenarLogo($request->file("logo"), $respuestaModelo["data"]["id"]);

                    if($statusArchivo !== false){
                        //Ahora guardo el logo en la base de datos
                        $statusAlmacenarLogoEnBD = Establecimiento::almacenarLogoEstablecimientoEnBD($statusArchivo, $respuestaModelo["data"]["id"]);

                        if($statusAlmacenarLogoEnBD["code"] == 0){
                            $response["code"] = 0;
                            $response["status"] = 200;
                            $response["statusText"] = "OK";
                        }else{
                            $response["code"] = -14;
                            $response["status"] = 400;
                            $response["statusText"] = "KO";
                        }
                    }else{
                        $response["code"] = -13;
                        $response["status"] = 400;
                        $response["statusText"] = "KO";
                    }
                }else{
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "OK";
                }
            }
            else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "KO";
            }

            //Log de salida
            Log::debug(
                "Saliendo del store del establecimientoControlador",
                array("userID:" => auth()->user()->id,
                    "request:" => $request->all(),
                    "response:" => $response));
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error($e->getMessage(), array("userID:" => auth()->user()->id,
                "request:" => $request->all(),
                "response:" => $response
                ));
        }

        //Montamos el response
        $responseAux = null;

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux = view("establecimientos.createandedit")->with("ko", __("establecimientos.storeko"));
        }else{
            //Respuesta OK
            $responseAux = redirect(route("establecimientos.show", $respuestaModelo["data"]->id))->with("ok", __("establecimientos.storeok"));
        }

        return $responseAux;
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
    public function show(Establecimiento $establecimiento)
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
                $establecimientoFavorito = false;
                $usuarioEnCola = false;

                if(auth()->check()){
                    $usuarioTieneFavoritoResult = User::elUsuarioTieneAlEstablecimientoComoFavorito(auth()->user()->id, $establecimiento);
                    if($usuarioTieneFavoritoResult["code"] == 0 &&
                    $usuarioTieneFavoritoResult["data"] == true){
                        $establecimientoFavorito = true;
                    }else{
                        //Aquí no se debería llegar
                        Log:error("la llamada a elUsuarioTieneAlEstablecimientoComoFavorito ha fallado",
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
     * Función para mostrar el formulario de edición de establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento a editar
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *   0: OK
     * -10: Sin autorización
     * -11: Excepción
     */
    //TODO
    public function edit(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al edit de EstablecimientoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            try{
                $this->authorize("update", $establecimiento);
            }catch(AuthorizationException $e){
                $response["statusText"] = "KO";
                $response["code"] = -10;
                $response["status"] = 403;

                Log::error($e->getMessage(),
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => compact("establecimiento"),
                        "response: " => $response
                    )
                );

                return redirect(route("noautorizado"));
            }

            //ACCIÓN
            $response["code"] = 0;
            $response["status"] = 200;
            $response["statusText"] = "OK";
            $response["data"] = $establecimiento;

            //Log de salida
            Log::debug("Saliendo del edit del EstablecimientoController",
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
            $response["statusText"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = view("establecimientos.createandedit")->with(compact("response"))->with("update", "update");

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux->with("ko", __("establecimientos.editko"));
        }else{
            //Respuesta OK
            //Nada
        }

        return $responseAux;
    }

    /**
     * FUnción para actualizar un establecimiento
     *
     * @param UpdateEstablecimientoRequest $request Los nuevos campos del establecimiento
     * @param Establecimiento $establecimiento El establecimiento a modificar
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     *   0: OK
     * -10: Sin autorización
     * -11: Excepción
     * -12: Error al actualizar los campos
     * -13: Error al almacenar la nueva ruta del logo
     */
    //TODO
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

            //¿Puede el usuario logueado realizar esta acción?
            try{
                $this->authorize("update", [Establecimiento::class, $establecimiento]);
            }catch(AuthorizationException $e){
                $response["statusText"] = "KO";
                $response["code"] = -10;
                $response["status"] = 403;

                Log::debug($e->getMessage(),
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => $request->all(),
                        "establecimiento: " => $establecimiento
                    )
                );

                return redirect(route("noautorizado"));
            }

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

                    $respuestaGuardarRutaLogo = Establecimiento::almacenarLogoEstablecimientoEnBD($nuevaRutaLogo, $establecimiento->id);

                    if($respuestaGuardarRutaLogo["code"] == 0){
                        $response["code"] = 0;
                        $response["status"] = 200;
                        $response["statusText"] = "OK";
                    }else{
                        $response["code"] = -13;
                        $response["status"] = 400;
                        $response["statusText"] = "KO";
                    }
                }else{
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "OK";
                }
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "KO";
            }

            //Log de salida
            Log::debug(
                "Saliendo del update del establecimientoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $request->all(),
                    "establecimiento: " => $establecimiento,
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -11;
            $response["status"] = 400;
            $response["statusText"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $request->all(),
                    "establecimiento: " => $establecimiento,
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = null;

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux = back()->withInput()->with("ko", __("establecimientos.updateko"));
        }else{
            //Respuesta OK
            $responseAux = redirect(route("establecimientos.show", $establecimiento->id))->with("ok", __("establecimientos.updateok"));
        }

        return $responseAux;

    }

    /**
     * FUnción de eliminación del establecimiento pasado como param
     *
     * @param Establecimiento $establecimiento El establecimiento a borrar
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *   0: OK
     * -10: Acción no autorizada para el usuario logueado
     * -11: Excepción
     * -12: Error al borrar el establecimiento
     */
    //TODO
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

            //¿Puede el usuario logueado realizar esta acción?
            try{
                $this->authorize("delete", $establecimiento);
            }catch(AuthorizationException $e){
                $response["statusText"] = "KO";
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

            //ACCIÓN
            $resultadoBorradoModelo = Establecimiento::eliminarEstablecimiento($establecimiento);

            if($resultadoBorradoModelo["code"] == 0){
                $response["code"] = 0;
                $response["status"] = 200;
                $response["statusText"] = "OK";
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "KO";
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
            $response["statusText"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        //Montamos el response
        $responseAux = null;

        if($response["code"] != 0){
            //Respuesta KO
            $responseAux = back()->with("ko", __("establecimientos.destroyko"));
        }else{
            //Respuesta OK
            $responseAux = redirect(route("establecimientos.index"))->with("ok", __("establecimientos.destroyok"));
        }

        return $responseAux;
    }

    //TODO
    public function almacenarLogo($logo, int $establecimientoID)
    {
        return $logo->storeAs("public/establecimientos/" . $establecimientoID . "/images", "logo.". $logo->extension());
    }
}
