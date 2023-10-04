<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class EstablecimientoFavorito extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = "id";

    protected $table = "establecimientos_favoritos";

    ////////////////
    // RELACIONES //
    ////////////////

    /**
     * Devuelve el usuario al que pertenece este me gusta
     *
     * @return BelongsTo Usuario
     */
    public function usuario() : BelongsTo
    {
        return $this->belongsTo(User::class, "usuario_id", "id");
    }

    /**
     * Devuelve el establecimiento gustado
     *
     * @return BelongsTo Establecimiento
     */
    public function establecimiento() : BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, "establecimiento_id", "id");
    }

    /////////////////////////
    // FUNCIONES ESTÁTICAS //
    /////////////////////////

    /**
     * Función que devuelve un listado de establecimientos favoritos del usuario pasado como param
     *
     * @param User $usuario El usuario
     *
     * @return EstablecimientoFavorito[] Colección de establecimientoFavorito
     *   0: OK
     *  -1: Excepción
     */
    public static function dameEstablecimientosFavoritosDadoUsuario(User $usuario)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al dameEstablecimientosFavoritosDadoUsuario del EstablecimientoFavorito",
                array(
                    "request: " => compact("usuario")
                )
            );

            $establecimientosFavoritos = $usuario->establecimientosGustados()
                ->with("establecimiento", function($query){
                    $query->withCount("usuariosEncolados");
                })
                ->get();

            $response["code"] = 0;
            $response["data"] = $establecimientosFavoritos;

            //Log de salida
            Log::debug(
                "Saliendo del dameEstablecimientosFavoritosDadoUsuario del EstablecimientoFavorito model",
                array(
                    "request: " => compact("usuario"),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("usuario"),
                    "response:" => $response
                )
            );
        }

        return $response;
    }
}
