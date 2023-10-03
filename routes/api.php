<?php

use App\Http\Controllers\ApiAuthentication;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\EstablecimientoFavoritoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//rutas de autenticación
Route::post("/login", [ApiAuthentication::class, "login"]);
Route::post("/register", [ApiAuthentication::class, "register"]);

Route::group(["middleware" => "auth:sanctum"], function(){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //ESTABLECIMIENTOS con sesión de usuario
    Route::get("/establecimientos/favoritos", [EstablecimientoFavoritoController::class, "establecimientosFavoritos"]);
    Route::get("/establecimientos/{establecimiento}/marcar-favorito", [EstablecimientoFavoritoController::class, "meGustaElEstablecimiento"]);
    Route::get("/establecimientos/{establecimiento}/desmarcar-favorito", [EstablecimientoFavoritoController::class, "yaNoMeGustaElEstablecimiento"]);
});

//TODO: Configurar endpoint para ir limpiando los personal token de sesión (tarea cron)

Route::get("buscar-establecimientos", [EstablecimientoController::class, "buscarEstablecimientos"]);
Route::get("establecimientos/{establecimiento}", [EstablecimientoController::class, "verEstablecimiento"]);
