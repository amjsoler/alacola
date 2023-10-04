<?php

namespace App\Policies;

use App\Models\Establecimiento;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class UsuarioEnColaPolicy
{
    /**
     * Policy que determina si un usuario puede pasasr turno en un establecimiento
     *
     * @param User $user El usuario que inicia la acción
     * @param Establecimiento $establecimiento El establecimiento sobre el que se pretende pasar turno
     *
     * @return Response
     */
    public function delete(User $user, Establecimiento $establecimiento) : Response
    {
        try {
            Log::debug(
                "Entrando al pasaTurno del UsuarioEnColaPolicy",
                array(
                    "userID: " => $user->id,
                    "establecimiento" => $establecimiento
                )
            );

            //Si el usuario es el administrador del establecimiento, entonces sí puede pasar turno
            if($establecimiento->usuario_administrador == $user->id){
                $response = Response::allow();

                Log::debug(
                    "Saliendo del pasaTurno del UsuarioEnColaPolicy: Status OK",
                    array(
                        "userID: " => $user->id,
                        "establecimiento" => $establecimiento
                    )
                );
            }else{
                $response = Response::deny();

                Log::debug(
                    "Saliendo del pasaTurno del UsuarioEnColaPolicy: Status KO",
                    array(
                        "userID: " => $user->id,
                        "establecimiento" => $establecimiento
                    )
                );
            }
        }catch(Exception $e){
            Log::error(
                $e->getMessage(),
                array(
                    "userID: " => $user->id,
                    "establecimiento" => $establecimiento
                )
            );

            $response = Response::deny();
        }

        return $response;
    }
}
