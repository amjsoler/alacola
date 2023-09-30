<?php

use App\Http\Controllers\ApiAuthentication;
use App\Http\Controllers\EstablecimientoController;
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
});

Route::get("buscar-establecimientos", [EstablecimientoController::class, "buscarEstablecimientos"]);
Route::get("establecimientos/{establecimiento}", [EstablecimientoController::class, "verEstablecimiento"]);
