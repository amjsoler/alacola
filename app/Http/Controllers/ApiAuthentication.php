<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiAuthenticationRegisterError;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Psr\Log\LogLevel;

class ApiAuthentication extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
            "password" => "required"

        ]);

        if (!Auth::attempt($request->only("email", "password"))) {
            return response("unauthorized", 401);
        }

        //Si he conseguido iniciar sesión me traigo al user para crear el token
        $user = User::where("email", $request->get("email"))->firstOrFail();

        $token = $user->createToken("authToken")->plainTextToken;

        return response()->json([
            "access_token" => $token,
            "token_type" => "Bearer"
        ]);
    }

    public function register(Request $request)
    {
        try{
            //TODO: Comprobar en el validador si el email ya está registrado
            $request->validate([
                "name" => "required|max:100",
                "email" => "required|email",
                "password" => "required|confirmed"
            ]);

            //Compruebo si el correo ya existe
            if (User::where("email", $request->get("email"))->count() > 0) {
                return response()->json([
                    "message" => "El correo ya está registrado",
                    "errors" => [
                        "email" => ["El correo ya está registrado"]
                    ]
                ], 422);
            }

            //Creo el nuevo usuario
            $user = new User();
            $user->name = $request->get("name");
            $user->email = $request->get("email");
            $user->password = Hash::make($request->get("password"));

            if($user->save()){
                //Inicio de sesión de usuario y devuelvo el token dentro del user
                $inicioSesion = Auth::attempt(['email' => $user->email, 'password' => $request->get("password")], true);

                if($inicioSesion){
                    $token = $user->createToken("authToken")->plainTextToken;
                    $user["access_token"] = $token;
                }

                Log::log(LogLevel::DEBUG, "Respuesta de attempt login tras registro", [$inicioSesion]);

                //TODO: Enviar una notificación/correo con el link de verificación

                return response()->json($user, 200);
            }else{
                throw new ApiAuthenticationRegisterError("Error al guardar los registros del nuevo usuario");
            }
        }catch(ApiAuthenticationRegisterError $exception){
            Log::error($exception->getMessage());

            return response()->json("", 500);
        }
    }
}
