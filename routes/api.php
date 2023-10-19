<?php

use App\Http\Controllers\ApiAuthentication;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\EstablecimientoFavoritoController;
use App\Http\Controllers\UsuarioEnColaController;
use Illuminate\Support\Facades\Route;

//TODO: Revisar todos los metodos de los controladores para ver donde devolver error al log cuando no pase lo que se quiere

//rutas de autenticación
Route::post("/login",
    [ApiAuthentication::class, "login"]);

Route::post("/register",
    [ApiAuthentication::class, "register"]);

//TODO: Route::get("/verificar-usuario");
//TODO: Forgot password

Route::get("/verificar-cuenta", [ApiAuthentication::class, "mandarCorreoVerificacionCuenta"])
->middleware("auth:sanctum");

Route::group(["middleware" => "auth:sanctum"], function(){
    ///// Rutas de usuario /////
    Route::get('/usuario', function () {
        return auth()->user();
    });

    //ESTABLECIMIENTOS con sesión de usuario
    Route::get("/establecimientos/{establecimiento}/apuntarse",
    [UsuarioEnColaController::class, "encolar"]);

    Route::get("/establecimientos/{establecimiento}/desapuntarse",
    [UsuarioEnColaController::class, "desencolar"]);

    Route::get("/mis-establecimientos", [EstablecimientoController::class, "misEstablecimientos"]);

    Route::post("/establecimientos",
    [EstablecimientoController::class, "store"]);

    Route::patch("/establecimientos/{establecimiento}",
        [EstablecimientoController::class, "update"]
    )->middleware("can:update,establecimiento");

//TODO: Borrar imagen logo del establecimiento

    Route::delete("/establecimientos/{establecimiento}",
        [EstablecimientoController::class, "destroy"]
    )->middleware("can:delete,establecimiento");

    ///// Favoritos /////
    Route::get("/establecimientos/favoritos",
        [EstablecimientoFavoritoController::class, "establecimientosFavoritos"]);

    Route::get("/establecimientos/{establecimiento}/marcar-favorito",
        [EstablecimientoFavoritoController::class, "meGustaElEstablecimiento"]);

    Route::get("/establecimientos/{establecimiento}/desmarcar-favorito",
        [EstablecimientoFavoritoController::class, "yaNoMeGustaElEstablecimiento"]);

    ///// Rutas de ADMIN /////
    Route::get("/establecimientos/{establecimiento}/pasar-turno",
        [UsuarioEnColaController::class, "adminPasaTurno"]
    )->middleware("can:delete,establecimiento");

    Route::get("/establecimientos/{establecimiento}/admin-desapunta-usuario/{usuarioEnCola}",
    [UsuarioEnColaController::class, "adminDesapunta"]
    )->middleware("can:delete,establecimiento");
    ////////////////////////////////////
});

//TODO: Intentar hacer login con un token expirado a ver qué devuelve laravel

//TODO: Configurar endpoint para ir limpiando los personal token de sesión (tarea cron)

//Rutas de ESTABLECIMIENTO sin sesión de usuario
Route::post("establecimientos/buscar",
    [EstablecimientoController::class, "buscarEstablecimientos"]);

Route::get("establecimientos/{establecimiento}",
    [EstablecimientoController::class, "show"]);

//Rutas de usuario sin sesión
Route::post("establecimientos/{establecimiento}/apuntarse-como-invitado",
    [UsuarioEnColaController::class, "encolarComoAnonimo"]);
