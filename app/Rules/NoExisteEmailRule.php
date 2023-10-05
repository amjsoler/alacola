<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class NoExisteEmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        Log::debug("Entrando al validate de la regla EmailNoExisteEnUsers");

        $userRequest = User::dameUsuarioDadoCorreo($value);

        //Si no se ha encontrado el usuario...
        if($userRequest["code"] != -2){
            $fail("Este email ya está en uso");
        }

        Log::debug("Saliendo del validate de la regla EmailNoExisteEnUsers");
    }
}
