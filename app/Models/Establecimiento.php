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

    protected $table = "establecimientos";

    /*
     * //////////
     * RELACIONES
     * //////////
     */

    /**
     * Devuelve el administrador del establecimiento
     *
     * @return BelongsTo User
     */
    public function administrador() : BelongsTo
    {
        return $this->belongsTo(User::class, "usuario_administrador", "id");
    }

    /**
     * Devuelve los usuarios encolados
     *
     * @return HasMany UsuarioEnCola[]
     */
    public function usuariosEncolados() : HasMany
    {
        return $this->hasMany(UsuarioEnCola::class, "establecimiento_cola", "id");
    }

    /**
     * Devuelve una lista de EstablecimeintoFavorito de este establecimeinto
     *
     * @return HasMany EstablecimientoFavorito[]
     */
    public function establecimientoGustado() : HasMany
    {
        return $this->hasMany(EstablecimientoFavorito::class, "establecimiento_id", "id");
    }

    /*
     * //////////
     * FUNCIONES
     * //////////
     */

    /**
     * Devuelve una lista de establecimientos dado un usuario
     *
     * @param User $user El usuario
     *
     * @return Establecimiento[]
     */
    public static function dameMisEstablecimientos(User $user)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al dameMisEstablecimientos del Establecimiento",
                array(
                    "userID: " => $user->id
                )
            );

            $establecimientos = $user->establecimientosAdministrados()
                ->withCount("usuariosEncolados")
                ->get();

            if($establecimientos != null){
                $response["code"] = 0;
                $response["data"] = $establecimientos;
            }
            else{
                $response["code"] = -2;

                Log::error("Error al buscar los establecimientos en dameMisEstablecimientos del modelo Establecimiento",
                    array(
                        "userID: " => $user->id
                    )
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de dameMisEstablecimientos del Establecimiento model",
                array(
                    "userID: " => $user->id,
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "userID: " => $user->id,
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Devuelve los establecimientos que casen con la busqueda realizada
     * Incluye un count de los usuariosEnCola relacionados
     *
     * @param string $cadenaBusqueda La cadena a buscar
     *
     * @return Establecimiento[] Incluye un count de los usuariosEnCola relacionados
     *   0: OK
     *  -1: Excepcion
     *  -2: Error al buscar la cadena
     */
    public static function buscarEstablecimiento(string $cadenaBusqueda)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al buscarEstablecimiento del Establecimiento",
                compact("cadenaBusqueda")
            );

            $establecimientos = Establecimiento::where("nombre", "like", "%".$cadenaBusqueda."%")
                ->orwhere("direccion", "like", "%".$cadenaBusqueda."%")
                ->orwhere("descripcion", "like", "%".$cadenaBusqueda."%")
                ->withCount("usuariosEncolados")
                ->get();

            if($establecimientos != null){
                $response["code"] = 0;
                $response["data"] = $establecimientos;
            }
            else{
                $response["code"] = -2;

                Log::error("Error al buscar el establecimiento en buscarEstablecimiento del modelo Establecimiento",
                    compact("cadenaBusqueda")
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de buscarEstablecimiento del Establecimiento model",
                array(
                    "request: " => compact("cadenaBusqueda"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("cadenaBusqueda"),
                    "response:" => $response
                )
            );
        }

        return $response;
    }

    /**
     * Pasada una latitud y longitud devuelve los establecimientos cercanos (10km TODO: Esto se parametrizará en el futuro)
     *
     * @param string $latitud La latitud
     * @param string $longitud La longitud
     *
     * @return Establecimiento[]
     */
    public static function buscarEstablecimientosCercanos(string $latitud, string $longitud)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al buscarEstablecimientosCercanos del Establecimiento",
                array(
                    "request: " => compact("latitud", "longitud")
                )
            );

            $idEstablecimientos = DB::select("select id, ( 6371 * acos(cos(radians($latitud)) * cos(radians(latitud)) * cos(radians(longitud) - radians($longitud)) + sin(radians($latitud)) * sin(radians(latitud)))) AS distancia from establecimientos having distancia < 10 order by distancia");

            $cadenaids = "";

            foreach($idEstablecimientos as $establecimiento)
            {
                $cadenaids .= $establecimiento->id . ",";
            }

            $establecimientos = Establecimiento::withCount("usuariosEncolados")
            ->whereIn("id", explode(",", $cadenaids))
            ->get();

            if($establecimientos){
                $response["code"] = 0;
                $response["data"] = $establecimientos;
            }
            else{
                $response["code"] = -2;

                Log::error("Error al buscarEstablecimientosCercanos del modelo Establecimiento",
                    array(
                        "request: " => compact("latitud", "longitud"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de buscarEstablecimientosCercanos del Establecimiento model",
                array(
                    "request: " => compact("latitud", "longitud"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("latitud", "longitud"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Crea un nuevo establecimiento y lo devuelve en la estructura típica de respuesta
     *
     * @param string $nombre El nombre del establecimiento
     * @param string|null $direccion La dirección del establecimiento
     * @param string|null $descripcion La descripción del establecimiento
     * @param int $usuarioAdmin El fundador del establecimiento
     * @param string|null $latitud Latitud de la posición del establecimiento
     * @param string|null $longitud Longitud de la posición del establecimiento
     *
     * @return Establecimiento Devuelve el establecimiento si lo ha conseguido crear
     *   0: OK
     *  -1: Excepción
     *  -2: Error al almacenar el establecimiento
     */
    public static function crearEstablecimiento(
        string $nombre,
        string $direccion=null,
        string $descripcion=null,
        int $usuarioAdmin,
        string $latitud=null,
        string $longitud=null)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al crearEstablecimiento del Establecimiento",
                array(
                    "request: " => compact("nombre", "direccion", "descripcion", "usuarioAdmin", "latitud", "longitud")
                )
            );

            $establecimientoAux = new Establecimiento();
            $establecimientoAux->nombre = $nombre;
            $establecimientoAux->direccion = $direccion;
            $establecimientoAux->descripcion = $descripcion;
            $establecimientoAux->usuario_administrador = $usuarioAdmin;
            $establecimientoAux->latitud = $latitud;
            $establecimientoAux->longitud = $longitud;

            if($establecimientoAux->save()){
                $response["code"] = 0;
                $response["data"] = $establecimientoAux;
            }
            else{
                $response["code"] = -2;

                Log::error("Error al crear el establecimiento en crearEstablecimiento del modelo Establecimiento",
                    array(
                        "request: " => compact("nombre", "direccion", "descripcion", "usuarioAdmin", "latitud", "longitud"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de crearEstablecimiento del Establecimiento model",
                array(
                    "request: " => compact("nombre", "direccion", "descripcion", "usuarioAdmin", "latitud", "longitud"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("nombre", "direccion", "descripcion", "usuarioAdmin", "latitud", "longitud"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * //Función que almacena la ruta del logo del establecimiento
     *
     * @param string $rutaLogo La ruta del logo almacenado
     * @param int $establecimientoID El establecimiento al que asociaremos el logo
     *
     * @return Bool si se ha podido guardar la ruta o no
     *   0: OK
     *  -1: Excepción
     *  -2: Error al almacenar el logo del establecimiento en BD
     */
    public static function almacenarLogoEstablecimientoEnBD(string $rutaLogo, int $establecimientoID)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al almacenarLogoEstablecimientoEnBD del Establecimiento",
                array(
                    "request: " => compact("rutaLogo", "establecimientoID")
                )
            );

            $establecimientoAux = Establecimiento::find($establecimientoID);
            $establecimientoAux->logo = $rutaLogo;

            if($establecimientoAux->save()){
                $response["code"] = 0;
                $response["data"] = true;
            }
            else{
                $response["code"] = -2;
                $response["data"] = false;

                Log::error("Error al almacenar el nombre del nuevo logo en almacenarUserLogoEnBD del modelo Establecimiento",
                    array(
                        "request: " => compact("rutaLogo", "establecimientoID"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de almacenarLogoEstablecimientoEnBD del Establecimiento model",
                array(
                    "request: " => compact("rutaLogo", "establecimientoID"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;
            $response["data"] = false;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("rutaLogo", "establecimientoID"),
                    "response: " => $response
                )
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
     * @param string $latitud La nueva latitud del establecimiento
     * @param string $longitud La nueva longitud del establecimiento
     *
     * @return Establecimiento El establecimiento con los valores modificados
     *   0: OK
     *  -1: Excepción
     *  -2: No se ha podido guardar/actualizar el establecimiento
     */
    public static function updateEstablecimiento(
        Establecimiento $establecimiento,
        string $nombre,
        string $direccion=null,
        string $descripcion=null,
        string $latitud=null,
        string $longitud=null)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al updateEstablecimiento del Establecimiento",
                array(
                    "request: " => compact("establecimiento", "nombre", "direccion", "descripcion", "latitud", "longitud")
                )
            );

            $establecimiento->nombre = $nombre;
            $establecimiento->direccion = $direccion;
            $establecimiento->descripcion = $descripcion;
            $establecimiento->latitud = $latitud;
            $establecimiento->longitud = $longitud;

            if($establecimiento->save()){
                $response["code"] = 0;
                $response["data"] = $establecimiento;
            }
            else{
                $response["code"] = -2;

                Log::error("Error al modificar los datos del establecimiento",
                    array(
                        "request: " => compact("establecimiento", "nombre", "direccion", "descripcion", "latitud", "longitud"),
                        "response: " => $response
                    )
                );
            }

            //Log de salida
            Log::debug(
                "Saliendo de updateEstablecimiento del Establecimiento model",
                array(
                    "request: " => compact("establecimiento", "nombre", "direccion", "descripcion", "latitud", "longitud"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("establecimiento", "nombre", "direccion", "descripcion", "latitud", "longitud"),
                    "response: " => $response
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
     * @return UsuarioEnCola+User{id,name}[] Array de usuario en cola
     *   0: OK
     *  -1: Excepción
     *  -2: Error al leer los usuarios encolados
     */
    public static function dameUsuariosEncolados(Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al dameUsuariosEncolados del Establecimiento",
                array(
                    "request: " => compact("establecimiento")
                )
            );

            $usuariosEncolados = $establecimiento->usuariosEncolados()
                ->with("usuario:id,name")
                ->orderBy("momentoestimado", "asc")
                ->get();

            $response["code"] = 0;
            $response["data"] = $usuariosEncolados;

            //Log de salida
            Log::debug(
                "Saliendo del dameUsuariosEncolados del Establecimiento model",
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Función que elimina el establecimiento pasado como parámetro
     *
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return void
     *   0: OK
     *  -1: Excepción
     *  -2: Error al realizar la acción de borrado
     */
    public static function eliminarEstablecimiento(Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al eliminarEstablecimiento del Establecimiento",
                array(
                    "request: " => compact("establecimiento")
                )
            );

            if($establecimiento->delete()){
                $response["code"] = 0;
            }else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug(
                "Saliendo del eliminarEstablecimiento del Establecimiento model",
                array(
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

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
     * @return Bool Si está encolado o no
     *   0: OK
     *  -1: Excepción
     */
    public static function comprobarUsuarioEnCola(int $usuarioID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al comprobarUsuarioEnCola del Establecimiento",
                array(
                    "request: " => compact("usuarioID", "establecimiento"),
                )
            );

            //Acción
            $usuarioEnCola = $establecimiento->usuariosEncolados()
                ->where("usuario_en_cola", $usuarioID)
                ->get();

            if($usuarioEnCola->count() > 0){
                $response["code"] = 0;
                $response["data"] = true;
            }else{
                $response["code"] = 0;
                $response["data"] = false;
            }

            //Log de salida
            Log::debug(
                "Saliendo del comprobarUsuarioEnCola del Establecimiento model",
                array(
                    "request: " => compact("usuarioID", "establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error(
                $e->getMessage(),
                array(
                    "request: " => compact("usuarioID", "establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
