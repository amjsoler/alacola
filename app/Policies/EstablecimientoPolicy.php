<?php

namespace App\Policies;

use App\Models\Establecimiento;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class EstablecimientoPolicy
{
    /**
     * Determina si un usuario puede modificar el establecimiento seleccionado
     *
     * @param User $user
     * @param Establecimiento $establecimiento
     *
     * @return Response
     */
    public function update(User $user, Establecimiento $establecimiento) : Response
    {
        try {
            Log::debug(
                "Entrando al update del EstablecimientoPolicy",
                array(
                    "userID: " => $user->id,
                    "establecimiento" => $establecimiento
                )
            );

            //Si el usuario es el administrador del establecimiento, entonces sí puede modificarlo
            if($establecimiento->usuario_administrador == $user->id){
                $response = Response::allow();

                Log::debug(
                    "Saliendo del update del EstablecimientoPolicy: Status OK",
                    array(
                        "userID: " => $user->id,
                        "establecimiento" => $establecimiento
                    )
                );
            }else{
                $response = Response::deny();

                Log::debug(
                    "Saliendo del update del EstablecimientoPolicy: Status KO",
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

    /**
     * Determina si un usuario puede eliminar el establecimiento seleccionado
     *
     * @param User $user
     * @param Establecimiento $establecimiento
     *
     * @return Response
     */
    public function delete(User $user, Establecimiento $establecimiento) : Response
    {
        try {
            Log::debug(
                "Entrando al delete del EstablecimientoPolicy",
                array("userID: " => $user->id, "establecimiento: " => $establecimiento));

            //Si el usuario es el administrador del establecimiento, entonces sí puede eliminar
            if($establecimiento->usuario_administrador == $user->id){
                $response = Response::allow();

                Log::debug(
                    "Saliendo del delete del EstablecimientoPolicy: Status OK",
                    array("userID: " => $user->id, "establecimiento: " => $establecimiento));
            }else{
                $response = Response::deny();

                Log::debug(
                    "Saliendo del delete del EstablecimientoPolicy: Status KO",
                    array("userID: " => $user->id, "establecimiento: " => $establecimiento));
            }
        }catch(Exception $e){
            Log::error(
                $e->getMessage(),
                array("userID: " => $user->id, "establecimiento: " => $establecimiento));

            $response = Response::deny();
        }

        return $response;
    }

    /**
     * Función del policy de establecimiento para comprobar si el usuario logueado tiene permisos de admin
     *
     * @param User $usuario El usuario
     * @param Establecimiento $establecimiento El establecimiento
     *
     * @return bool Si es admin o no
     */
    public function herramientasAdminEstablecimiento(User $usuario, Establecimiento $establecimiento) : Response
    {
        try {
            Log::debug(
                "Entrando al herramientasAdminEstablecimiento del EstablecimientoPolicy",
                array(
                    "userID: " => $usuario->id,
                    "establecimiento: " => $establecimiento
                )
            );

            //Si el usuario es el administrador del establecimiento, entonces sí puede eliminar
            if($establecimiento->usuario_administrador == $usuario->id){
                $response = Response::allow();

                Log::debug(
                    "Saliendo del herramientasAdminEstablecimiento del EstablecimientoPolicy: Status OK",
                    array(
                        "userID: " => $usuario->id,
                        "establecimiento: " => $establecimiento
                    )
                );
            }else{
                $response = Response::deny();

                Log::debug(
                    "Saliendo del herramientasAdmin del EstablecimientoPolicy: Status KO",
                    array(
                        "userID: " => $usuario->id,
                        "establecimiento: " => $establecimiento
                    )
                );
            }
        }catch(Exception $e){
            Log::error(
                $e->getMessage(),
                array(
                    "userID: " => $usuario->id,
                    "establecimiento: " => $establecimiento
                )
            );

            $response = Response::deny();
        }

        return $response;
    }
}
