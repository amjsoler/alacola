<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function establecimientosGustados() : HasMany
    {
        return $this->hasMany(EstablecimientoFavorito::class, "usuario_id", "id");
    }

    public function establecimientosAdministrados() : HasMany
    {
        return $this->hasMany(Establecimiento::class, "usuario_administrador", "id");
    }

    /**
     * Función que devuelve true o false según si el usuario tiene como favorito a la empresa pasada como param.
     *
     * @param int $userId El usuario
     * @param Establecimiento $establecimientoId El establecimiento
     *
     * @return string[] Array de datos con estructura estandar.
     * El data devolverá un bool según si el establecimiento pertenece a favoritos o no
     *
     *   0: OK
     *  -1: Excepción en la consulta
     */
    public static function elUsuarioTieneAlEstablecimientoComoFavorito(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al elUsuarioTieneAlEstablecimientoComoFavorito de User",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            //Acción
            $comprobacion = $establecimiento->establecimientoGustado()
                ->where("usuario_id", $userID)
                ->get();

            if($comprobacion->count() == 0){
                $response["code"] = 0;
                $response["data"] = false;
            }
            else{
                $response["code"] = 0;
                $response["data"] = true;
            }

            //Log de salida
            Log::debug("Saliendo del elUsuarioTieneAlEstablecimientoComoFavorito de User",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * FUnción encargada de registrar el me gusta de un usuario sobr eun establecimiento
     *
     * @param int $userID El usuario que inicia la acción
     * @param Establecimiento $establecimiento El establecimiento que le gusta
     *
     * @return string[]
     *   0: OK
     *  -1: Excepción
     *  -2: Error a la hora de insertar el registro
     */
    public static function aUsuarioLeGustaUnEstablecimiento(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al aUsuarioLeGustaUnEstablecimiento de User",
                array(
                    "userID:" => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            //Acción
            $meGustaEstablecimiento = new EstablecimientoFavorito();
            $meGustaEstablecimiento->usuario_id = $userID;
            $meGustaEstablecimiento->establecimiento_id = $establecimiento->id;

            if($meGustaEstablecimiento->save()){
                $response["code"] = 0;
                $response["data"] = $meGustaEstablecimiento;
            }
            else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug("Saliendo del aUsuarioLeGustaUnEstablecimiento de User",
                array(
                    "userID:" => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "userID:" => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response: " => $response
                )
            );
        }

        return $response;
    }

    /**
     * Función que se encarga de borrar los registros de un usuario que le gusta un establecimiento
     *
     * @param int $userID El usuario
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return string[]
     *   0: OK
     *  -1: Excepción
     *  -2: Error al realizar el borrado de registros
     */
    public static function aUsuarioNoLeGustaUnEstablecimiento(int $userID, Establecimiento $establecimiento)
    {
        $response = [
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::debug("Entrando al aUsuarioNoLeGustaUnEstablecimiento de User",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento")
                )
            );

            //Acción
            $resultDelete = $establecimiento->establecimientoGustado()
                ->where("usuario_id", $userID)
                ->delete();

            if($resultDelete){
                $response["code"] = 0;
            }
            else{
                $response["code"] = -2;
            }

            //Log de salida
            Log::debug("Saliendo del aUsuarioNoLeGustaUnEstablecimiento de User",
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response:" => $response
                )
            );
        }
        catch(Exception $e){
            $response["code"] = -1;

            Log::error($e->getMessage(),
                array(
                    "userID: " => auth()->user()->id,
                    "request: " => compact("establecimiento"),
                    "response:" => $response
                )
            );
        }

        return $response;
    }

    /**
     * FUnción que devuelve un usuario dado el correo o nada si no existe
     *
     * @param string $correo El correo a buscar
     *
     * @return string[]
     *
     *   0: OK
     *  -1: Excepción
     */
    public static function dameUsuarioDadoCorreo(string $correo)
    {
        $response = [
            "status" => "",
            "message" => "",
            "code" => "",
            "data" => ""
        ];

        try{
            //Log de entrada
            Log::info("Entrando al dameUsuarioDadoCorreo de User",
                array(
                    "request: " => $correo
                )
            );

            //Acción
            $usuario = User::where("email", $correo)->first();

            if($usuario){
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
                $response["data"] = $usuario;
            }
            else{
                $response["status"] = 200;
                $response["code"] = 0;
                $response["message"] = "OK";
            }

            //Log de salida
            Log::info("Saliendo del dameUsuarioDadoCorreo de User",
                array(
                    "request: " => $correo,
                    "response: " => $response
                )
            );
        }
        catch(Exception $e){
            $response["status"] = 400;
            $response["code"] = -1;
            $response["message"] = "KO";

            Log::error($e->getMessage(),
                array(
                    "request: " => $correo,
                    "response: " => $response
                )
            );
        }

        return $response;
    }
}
