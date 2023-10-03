<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\EstablecimientoFavorito;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstablecimientoFavoritoController extends Controller
{
    /**
     * Método para mostrar la lista de establecimientos marcados como favoritos del usuario logueado
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     *
     *   0: OK
     * -11: Excepción
     * -12: Error al leer los establecimientos en la parte del modelo
     */
    public function establecimientosFavoritos()
    {
        $response = [
            "status" => "",
            "statusText" => "",
            "data" => [],
            "code" => ""
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al establecimientosFavoritos de EstablecimientoFavoritoController",
                array(
                    "userID: " => auth()->user()->id
                )
            );

            $establecimientosFavoritos = EstablecimientoFavorito::dameEstablecimientosFavoritosDadoUsuario(auth()->user());

            if($establecimientosFavoritos["code"] == 0){
                $response["data"] = $establecimientosFavoritos["data"];
                $response["status"] = 200;
                $response["statusText"] = "ok";
                $response["code"] = 0;
            }else{
                $response["status"] = 400;
                $response["statusText"] = "ko";
                $response["code"] = -12;
            }

            //Log de salida
            Log::debug("Saliendo del establecimientosFavoritos del EstablecimientoFavoritoController",
                array(
                    "userID: " => auth()->user()->id,
                    "response" => $response
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
                    "response" => $response
                )
            );
        }

        return response()->json($response["data"], $response["status"]);
    }

    /**
     * Función para poder guardar un establecimiento como favorito
     *
     * @param Establecimiento $establecimiento El establecimiento a guardar
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     *   0: OK
     * -11: Excepción
     * -12: El usuario ya tenía almacenado en me gustas el establecimiento
     * -13: Error al guardar el establecimiento como favorito
     */
    public function meGustaElEstablecimiento(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "statusText" => "",
            "code" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al meGustaElEstablecimiento de EstablecimientoFavoritoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            //ACCIÓN
            //Primero comprobamos que al usuario no le guste todavía el establecimiento
            $comprobarMeGusta = User::elUsuarioTieneAlEstablecimientoComoFavorito(auth()->user()->id, $establecimiento);

            if($comprobarMeGusta["code"] == 0 && $comprobarMeGusta["data"] == false){
                $meGustaEstablecimiento = User::aUsuarioLeGustaUnEstablecimiento(auth()->user()->id, $establecimiento);

                if($meGustaEstablecimiento["code"] == 0){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";
                    $response["data"] = $meGustaEstablecimiento["data"];
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["statusText"] = "ko";

                    Log::error("Error al guardar el establecimiento como favorito para un usuario",
                        array(
                            "userID: " => auth()->user()->id,
                            "request: " => $establecimiento,
                            "response: " => $response
                        )
                    );

                }
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";

                Log::error("",
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => $establecimiento,
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug("Saliendo del meGustaElEstablecimiento del EstablecimientoFavoritoController",
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
            $response["statusText"] = "ko";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $establecimiento,
                    "response: " => $response
                )
            );
        }

        return response()->json($response["data"], $response["status"]);
    }

    /**
     * Función que se usa para que un usuario quite de su lista de favoritos a un establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento que se va a dejar de seguir
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     *   0: OK
     * -11: Excepción
     * -12: El usuario no tenía como favorita la empresa
     * -13: Error en el metodo de modelo de eliminación
     */
    public function yaNoMeGustaElEstablecimiento(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try {
            //Log de entrada
            Log::debug("Entrando al yaNoMeGustaElEstablecimiento de EstablecimientoFavoritoController",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            //ACCIÓN
            //Primero comprobamos que el usuario tenga en favoritos el establecimiento
            $establecimientoGustado = User::elUsuarioTieneAlEstablecimientoComoFavorito(auth()->user()->id, $establecimiento);

            if($establecimientoGustado["code"] == 0 && $establecimientoGustado["data"] == true){
                $quitarMeGustaResult = User::aUsuarioNoLeGustaUnEstablecimiento(auth()->user()->id, $establecimiento);

                if($quitarMeGustaResult["code"] == 0){
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";
                }else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["statusText"] = "KO";

                    Log::error("Error al borrar el establecimiento de la lista de favoritos del usuario",
                        array(
                            "userID: " => auth()->user()->id,
                            "request: " => compact("establecimiento"),
                            "response: " => $response
                        )
                    );
                }
            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";

                Log::error("El usuario no debería poder darle a no me gusta a un establecimiento que no tiene en su lista de favoritos",
                    array(
                        "userID: " => auth()->user()->id,
                        "request: " => compact("establecimiento"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug("Saliendo del yaNoMeGustaElEstablecimiento del EstablecimientoFavoritoController",
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
            $response["statusText"] = "ko";

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => $establecimiento,
                    "response: " => $response
                )
            );
        }

        return response()->json(
            $response["data"],
            $response["code"]
        );
    }
}
