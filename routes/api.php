<?php

use App\Http\Controllers\ApiAuthentication;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\EstablecimientoFavoritoController;
use App\Http\Controllers\UsuarioEnColaController;
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
Route::post("/login", [ApiAuthentication::class, "login"]); //TODO
Route::post("/register", [ApiAuthentication::class, "register"]); //TODO
//TODO: Route::get("/verificar-usuario");
//TODO: Forgot password

Route::group(["middleware" => "auth:sanctum"], function(){
    //Rutas de usuario
    Route::get('/user', function (Request $request) { //TODO
        return $request->user();
    });

    //TODO: Usuario se apunta a un establecimiento con sesión
    //TODO: Usuario se desapunta de un establecimiento con sesión


    //ESTABLECIMIENTOS con sesión de usuario
    //TODO: Crear establecimiento
    //TODO: Actualizar establecimiento
    //TODO: Borrar establecimiento

    Route::get("/establecimientos/favoritos", [EstablecimientoFavoritoController::class, "establecimientosFavoritos"]); //TODO
    Route::get("/establecimientos/{establecimiento}/marcar-favorito", [EstablecimientoFavoritoController::class, "meGustaElEstablecimiento"]); //TODO
    Route::get("/establecimientos/{establecimiento}/desmarcar-favorito", [EstablecimientoFavoritoController::class, "yaNoMeGustaElEstablecimiento"]); //TODO

    //Rutas de ADMIN
    Route::get("/establecimientos/{establecimiento}/pasar-turno",
        [UsuarioEnColaController::class, "adminPasaTurno"]
    )->middleware("can:delete,establecimiento");
});

//TODO: Intentar hacer login con un token expirado a ver qué devuelve laravel

//TODO: Configurar endpoint para ir limpiando los personal token de sesión (tarea cron)

//Rutas de establecimiento sin sesión de usuario
Route::post("establecimientos/buscar",
    [EstablecimientoController::class, "buscarEstablecimientos"]);
Route::get("establecimientos/{establecimiento}",
    [EstablecimientoController::class, "show"]);

//Rutas de usuario sin sesión
Route::post("establecimientos/{establecimiento}/apuntarse-como-invitado",
    [UsuarioEnColaController::class, "encolarComoAnonimo"]);
