<?php

namespace App\Policies;

use App\Models\Establecimiento;
use App\Models\User;
use App\Models\UsuarioEnCola;
use Exception;
use Illuminate\Support\Facades\Log;

class UsuarioEnColaPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Función para ver si un usuario puede desapuntar a un usuario del establecimiento pasado como param
     *
     * @param Establecimiento $establecimiento El establecimiento
     * @param UsuarioEnCola $usuarioEnCola El usuario a desencolar
     *
     * @return bool Si puede realizar la acción o no
     */
    public function adminDesapuntaUser(User $user, UsuarioEnCola $usuarioEnCola, Establecimiento $establecimiento)
    {
        try {
            Log::info(
                "Entrando al adminDesapuntaUser del UsuarioEncolaPolicy",
                compact("establecimiento", "usuarioEnCola")
            );

            //Si el usuario logueado es admin del establecimiento entonces seguimos
            if($establecimiento->usuario_administrador === $user->id){
                if($usuarioEnCola->establecimiento_cola === $establecimiento->id){
                    $response = true;

                    Log::info(
                        "Saliendo del adminDesapuntaUser del UsuarioEncolaPolicy: Status KO",
                        compact("establecimiento", "usuarioEnCola")
                    );
                }
                else{
                    $response = false;

                    Log::info(
                        "Saliendo del adminDesapuntaUser del UsuarioEnColaPolicy: Status KO",
                        compact("establecimiento", "usuarioEnCola")
                    );
                }
            }else{
                $response = false;

                Log::info(
                    "Saliendo del adminDesapuntaUser del UsuarioEnColaPolicy: Status KO",
                    compact("establecimiento", "usuarioEnCola")
                );
            }
        }catch(Exception $e){
            $response = false;

            Log::error(
                $e->getMessage(),
                compact("establecimiento", "usuarioEnCola")
            );
        }

        return $response;
    }
}
