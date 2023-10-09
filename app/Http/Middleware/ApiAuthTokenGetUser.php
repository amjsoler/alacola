<?php

namespace App\Http\Middleware;

use Closure;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthTokenGetUser
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::debug("Entrando al ApiAuthTokenGetUser");

        //Si viene el token entro
        if ($request->hasHeader("Authorization")) {
            Log::debug("Viene token de autorización");
            //Si el auth::user() no tiene nada entonces entro
            if (!Auth::check()) {
                $tokenAux = $request->header("Authorization");
                Log::debug("No hay user en el auth::user(). Voy a buscar el user que corresponde con el token: " . $tokenAux);

                $token = str_replace("Bearer ", "", $tokenAux);
                $token = explode("|", $token);

                $token = $token[1];
                $token = trim($token);

                Log::debug("La preparación del token ha resultado en: " . $token);

                $userID = DB::table("personal_access_tokens")
                    ->where("token", hash("sha256", $token))
                    ->value("tokenable_id");

                if ($userID > 0) {
                    Log::debug("El token está asociado al user: " . $userID);
                    $user = \App\Models\User::find($userID);

                    if ($user) {
                        Log::debug("Guardamos el user en el auth::user()");
                        Auth::setUser($user);
                    }
                }
            }
        }

        //Leo el user

        Log::debug("Saliendo al ApiAuthTokenGetUser");

        return $next($request);
    }
}
