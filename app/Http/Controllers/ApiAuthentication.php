<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiAuthenticationRegisterError;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

class ApiAuthentication extends Controller
{
    /**
     * Método para loguear a un usuario
     *
     * @param LoginRequest $request Inluye el email y la contraseña
     *
     * @return {access_token, token_type} Si la contraseña no coincide, devuelve un 401
     *   0: OK
     * -11: Excepción
     * -12: No se ha podido iniciar sesión. Quizá haya algún dato incorrecto
     * -13: No se ha podido leer el usuario dado el correo
     */
    public function login(LoginRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al login de ApiAuthentication",
                array(
                    "request: " => $request->all()
                )
            );

            if (Auth::attempt($request->only("email", "password"))) {
                //Si he conseguido iniciar sesión me traigo al user para crear el token
                $userResponse = User::dameUsuarioDadoCorreo($request->get("email"));

                if($userResponse["code"] == 0){
                    $user = $userResponse["data"];
                    $token = $user->createToken("authToken")->plainTextToken;

                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["data"] = ["access_token" => $token, "token_type" => "Bearer"];
                    $response["statusText"] = "ok";
                }else{
                    $response["code"] = -13;
                    $response["status"] = 401;
                    $response["data"] = "Unauthorized";
                    $response["statusText"] = "Unauthorized";
                }
            }else{
                $response["code"] = -12;
                $response["status"] = 401;
                $response["data"] = "Unauthorized";
                $response["statusText"] = "Unauthorized";
            }

            //Log de salida
            Log::debug("Saliendo del login del EstablecimientoController",
                array(
                    "request: " => $request->all(),
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
                    "request: " => $request->all(),
                    "repsonse: " => $response
                )
            );
        }

        return response()->json(
            $response["data"],
            $response["status"]
        );
    }

    /**
     * Método para registrar un nuevo usuario e iniciar su sesión
     *
     * @param RegisterRequest $request Incluye el name, email y password
     *
     * @return User El usuario recien creado junto con un token de inicio de sesión
     *   0: OK
     * -11: Excepción
     * -12: Error al crear el nuevo usuario en el modelo
     * -13: Error al intentar iniciar sesión
     */
    public function register(RegisterRequest $request)
    {
        $response = [
            "status" => "",
            "code" => "",
            "statusText" => "",
            "data" => []
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al register de ApiAuthentication",
                array(
                    "request: " => $request->all()
                )
            );

            //Creo el nuevo usuario
            $userResult = User::crearNuevoUsuario(
                $request->get("name"),
                $request->get("email"),
                Hash::make($request->get("password"))
            );

            if($userResult["code"] == 0){
                $user = $userResult["data"];

                //Inicio de sesión de usuario y devuelvo el token dentro del user
                $inicioSesion = Auth::attempt(['email' => $user->email, 'password' => $request->get("password")], true);

                if($inicioSesion){
                    $token = $user->createToken("authToken")->plainTextToken;
                    $user["access_token"] = $token;
                    $user["token_type"] = "Bearer";

                    //TODO: Enviar una notificación/correo con el link de verificación

                    $response["data"] = $user;
                    $response["code"] = 0;
                    $response["status"] = 200;
                    $response["statusText"] = "ok";

                } else{
                    $response["code"] = -13;
                    $response["status"] = 400;
                    $response["statusText"] = "ko";

                    Log::error("Fallo al inciiar sesión con el usuario recién creado, esto no debería fallar",
                    array(
                        "request: " => $request->all,
                        "response: " => $response)
                    );
                }

            }else{
                $response["code"] = -12;
                $response["status"] = 400;
                $response["statusText"] = "ko";

                Log::error("Fallo al crear el usuario, esto no debería fallar si el validador hace bien su trabajo",
                    array(
                        "request: " => $request->all,
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug("Saliendo del register del EstablecimientoController",
                array(
                    "request: " => $request->all(),
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
                    "request: " => $request->all(),
                    "repsonse: " => $response
                )
            );
        }

        return response()->json(
            $response["data"],
            $response["status"]
        );
    }
}
