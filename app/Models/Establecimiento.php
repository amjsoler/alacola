<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Establecimiento extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = "id";

    /*
     * //////////
     * RELACIONES
     * //////////
     */
    //Usuario que administra este establecimiento
    public function administrador() : BelongsTo
    {
        return $this->belongsTo(User::class, "usuario_administrador", "id");
    }

    public function usuariosEncolados() : HasMany
    {
        return $this->hasMany(UsuarioEnCola::class, "establecimiento_cola", "id");
    }

    public function establecimientoGustado() : HasMany
    {
        return $this->hasMany(EstablecimientoFavorito::class, "establecimiento_id", "id");
    }

    /*
     * //////////
     * FUNCIONES
     * //////////
     */

    public static function dameMisEstablecimientos(User $user)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al dameMisEstablecimientos del Establecimiento",
                compact("user")
            );

            $establecimientos = $user->establecimientosAdministrados()
                ->with("usuariosEncolados", function($query){
                    $query->where("activo", 1);
                })
                ->get();

            if($establecimientos != null){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimientos;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al buscar los establecimientos en dameMisEstablecimientos del modelo Establecimiento",
                    compact("user")
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de dameMisEstablecimientos del Establecimiento model",
                array(
                    "params: " => compact("user"),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "params: " => compact("user"),
                    "response:" => $response
                )
            );
        }

        return $response;
    }

    /**
     * Devuelve los establecimientos que casen con la busqueda realizada
     *
     * @param string $cadenaBusqueda La cadena a buscar
     *
     * @return string[]
     *   0: OK
     *  -1: Excepcion
     *  -2: Error al buscar la cadena
     */
    public static function buscarEstablecimiento(string $cadenaBusqueda)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al buscarEstablecimiento del Establecimiento",
                compact("cadenaBusqueda")
            );

            $establecimientos = Establecimiento::where("nombre", "like", "%".$cadenaBusqueda."%")
                ->orwhere("direccion", "like", "%".$cadenaBusqueda."%")
                ->orwhere("descripcion", "like", "%".$cadenaBusqueda."%")
                ->with("usuariosEncolados", function($query){
                    $query->where("activo", 1);
                })
                ->get();

            if($establecimientos != null){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimientos;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al buscar el establecimiento en buscarEstablecimiento del modelo Establecimiento",
                    compact("cadenaBusqueda")
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de buscarEstablecimiento del Establecimiento model",
                array(
                    "params: " => compact("cadenaBusqueda"),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "params: " => compact("cadenaBusqueda"),
                    "response:" => $response
                )
            );
        }

        return $response;
    }

    public static function buscarEstablecimientosCercanos(string $latitud, string $longitud)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al buscarEstablecimientosCercanos del Establecimiento",
                compact("latitud", "longitud")
            );

            $idEstablecimientos = DB::select("select id, ( 6371 * acos(cos(radians($latitud)) * cos(radians(latitud)) * cos(radians(longitud) - radians($longitud)) + sin(radians($latitud)) * sin(radians(latitud)))) AS distancia from establecimientos having distancia < 10 order by distancia");

            $cadenaids = "";

            foreach($idEstablecimientos as $establecimiento)
            {
                $cadenaids .= $establecimiento->id . ",";
            }

            $establecimientos = Establecimiento::with("usuariosEncolados", function($query){
                $query->where("activo", 1);
            })
            ->whereIn("id", explode(",", $cadenaids))
            ->get();

            if($establecimientos){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimientos;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al crear el buscarEstablecimientosCercanos en crearEstablecimiento del modelo Establecimiento",
                    compact("latitud", "longitud")
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de buscarEstablecimientosCercanos del Establecimiento model",
                array(
                    "request: " => compact("latitud", "longitud"),
                    "response:" => $response
                ));
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                compact("latitud", "longitud")
            );
        }

        return $response;
    }

    /**
     * Crea un nuevo establecimiento y lo devuelve en la estructura típica de respuesta
     *
     * @param $nombre El nombre del nuevo establecimiento
     * @param $direccion La dirección del nuevo establecimiento
     * @param $descripcion La descripción del nuevo establecimiento
     * @param $usuarioAdmin El usuario que crea el nuevo establecimiento y el que será admin del mismo
     *
     * @return string[] Estructura de respuesta típica con el nuevo establecimiento en el data
     *   0: OK
     *  -1: Excepción
     *  -2: Error al almacenar el establecimiento
     */
    public static function crearEstablecimiento(string $nombre, string $direccion=null, string $descripcion=null, int $usuarioAdmin, string $latitud=null, string $longitud=null)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al crearEstablecimiento del Establecimiento",
                compact("nombre", "direccion", "descripcion", "usuarioAdmin", "latitud", "longitud")
            );

            $establecimientoAux = new Establecimiento();
            $establecimientoAux->nombre = $nombre;
            $establecimientoAux->direccion = $direccion;
            $establecimientoAux->descripcion = $descripcion;
            $establecimientoAux->usuario_administrador = $usuarioAdmin;
            $establecimientoAux->latitud = $latitud;
            $establecimientoAux->longitud = $longitud;

            if($establecimientoAux->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimientoAux;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al crear el establecimiento en crearEstablecimiento del modelo Establecimiento",
                    compact("nombre", "direccion", "descripcion", "usuarioAdmin")
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de crearEstablecimiento del Establecimiento model",
                array(
                    "params: " => compact("nombre", "direccion", "descripcion", "usuarioAdmin"),
                    "response:" => $response
                ));
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                compact("nombre", "direccion", "descripcion", "usuarioAdmin")
            );
        }

        return $response;
    }

    /**
     * //Función que almacena el nombre del logo del establecimiento
     *
     * @param string $nombreLogo El nombre del logo con extensión
     * @param int $establecimientoID El establecimiento al que asociar el logo
     *
     * @return string[] //Estructura típica de respuesta
     *   0: OK
     *  -1: Excepción
     *  -2: Error al almacenar el logo del establecimiento en BD
     */
    public static function almacenarLogoEstablecimientoEnBD(string $nombreLogo, int $establecimientoID)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al almacenarLogoEstablecimientoEnBD del Establecimiento",
                compact("nombreLogo", "establecimientoID")
            );

            $establecimientoAux = Establecimiento::find($establecimientoID);
            $establecimientoAux->logo = $nombreLogo;

            if($establecimientoAux->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimientoAux;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al almacenar el nombre del nuevo logo en almacenarUserLogoEnBD del modelo Establecimiento",
                    compact("nombreLogo", "establecimientoID")
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de almacenarLogoEstablecimientoEnBD del Establecimiento model",
                array(
                    "params: " => compact("nombreLogo", "establecimientoID"),
                    "response:" => $response
                ));
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                compact("nombreLogo", "establecimientoID")
            );
        }

        return $response;
    }

    /**
     * Función para modificar un establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento
     * @param string $nombre Nuevo nombre
     * @param string $direccion Nueva dirección
     * @param string $descripcion Nueva descripción
     *
     * @return string[] Respuesta
     *
     *   0: OK
     *  -1: Excepción
     *  -2: No se ha podido guardar/actualizar el establecimiento
     */
    public static function updateEstablecimiento(Establecimiento $establecimiento, string $nombre, string $direccion=null, string $descripcion=null, string $latitud=null, string $longitud=null)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al updateEstablecimiento del Establecimiento",
                compact(
                    "establecimiento",
                    "nombre",
                    "direccion",
                    "descripcion"
                )
            );

            $establecimiento->nombre = $nombre;
            $establecimiento->direccion = $direccion;
            $establecimiento->descripcion = $descripcion;
            $establecimiento->latitud = $latitud;
            $establecimiento->longitud = $longitud;

            if($establecimiento->save()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $establecimiento;
            }
            else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";

                Log::error("Error al modificar los datos del establecimiento",
                    compact(
                        "establecimiento",
                        "nombre",
                        "direccion",
                        "descripcion"
                    )
                );
            }

            //Log de salida
            Log::info(
                "Saliendo de updateEstablecimiento del Establecimiento model",
                array(
                    "params: " => compact(
                        "establecimiento",
                        "nombre",
                        "direccion",
                        "descripcion"
                    ),
                    "response:" => $response
                ));
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                compact(
                    "establecimiento",
                    "nombre",
                    "direccion",
                    "descripcion"
                )
            );
        }

        return $response;
    }

    /**
     * Función que devuelve los usuarios encolados en un establecimiento
     *
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return UsuarioEnCola [] Array de usuario en cola
     *   0: OK
     *  -1: Excepción
     *  -2: Error al leer los usuarios encolados
     */
    public static function dameUsuariosEncolados(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al dameUsuariosEncolados del Establecimiento",
                compact("establecimiento")
            );

            $usuariosEncolados = $establecimiento->usuariosEncolados()
                ->with("usuario:id,name")
                ->where("activo", 1)
                ->orderBy("momentoestimado", "asc")
                ->get();

            $response["status"] = 200;
            $response["code"] = 0;
            $response["message"] = "OK";
            $response["data"] = $usuariosEncolados;

            //Log de salida
            Log::info(
                "Saliendo del dameUsuariosEncolados del Establecimiento model",
                array(
                    "params: " => compact("establecimiento"),
                    "response:" => $response
                ));
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                compact("establecimiento")
            );
        }

        return $response;
    }

    /**
     * Función que elimina el establecimiento pasado como parámetro
     *
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return array[]
     *   0: OK
     *  -1: Excepción
     *  -2: Error al realizar la acción de borrado
     */
    public static function eliminarEstablecimiento(Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al eliminarEstablecimiento del Establecimiento",
                compact("establecimiento")
            );

            if($establecimiento->delete()){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }else{
                $response["status"] = 400;
                $response["code"] = -2;
                $response["message"] = "KO";
            }

            //Log de salida
            Log::info(
                "Saliendo del eliminarEstablecimiento del Establecimiento model",
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Función que devuelve true o false en función de si el usuario está encolado en el establecimiento pasado como parametro
     *
     * @param int $usuarioID El usuario a comprobar
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return string[]
     *   0: OK
     *  -1: Excepción
     */
    public static function comprobarUsuarioEnCola(int $usuarioID, Establecimiento $establecimiento)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al comprobarUsuarioEnCola del Establecimiento",
                array(
                    "usuarioID: " => $usuarioID,
                    "request: " => compact("usuarioID", "establecimiento"),
                )
            );

            //Acción
            $usuarioEnCola = $establecimiento->usuariosEncolados()
                ->where("usuario_en_cola", $usuarioID)
                ->where("activo", true)
                ->get();

            if($usuarioEnCola->count() > 0){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = true;
            }else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = false;
            }

            //Log de salida
            Log::info(
                "Saliendo del comprobarUsuarioEnCola del Establecimiento model",
                array(
                    "usuarioID: " => $usuarioID,
                    "request: " => compact("usuarioID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error(
                $e->getMessage(),
                array(
                    "usuarioID: " => $usuarioID,
                    "request: " => compact("usuarioID", "establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
