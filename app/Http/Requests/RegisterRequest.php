<?php

namespace App\Http\Requests;

use App\Rules\NoExisteEmailRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Log de entrada para el validador de StoreEstablecimientoRequest
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        Log::debug("Entrando a validación del RegisterRequest",
            array(
                "request:" => $this->request->all()
            )
        );
    }

    /**
     * Función que se llama cuando la validación falla
     *
     * @param Validator $validator
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        Log::debug("Saliendo del validador de RegisterRequest. Status: KO",
            array(
                "request:" => $this->request->all()
            )
        );

        parent::failedValidation($validator);
    }

    /**
     * Función que se llama cuando la validación pasa
     * @return void
     */
    protected function passedValidation()
    {
        Log::debug("Saliendo del validador de RegisterRequest. Status: OK",
            array(
                "request:" => $this->request->all()
            )
        );

        parent::passedValidation();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name"=> "required|max:100",
            "email"=> ["required", "email", new NoExisteEmailRule],
            "password"=> "required|confirmed",
        ];
    }

    public function messages()
    {
        return [
            "name.required" => "El nombre no puede estar vacío",
            "name.max" => "El nombre no puede superar los 100 caracteres",
            "email.required" => "El email no puede estar vacío",
            "email.email" => "El email no es válido",
            "email.NoExisteEmailRule" => "Este email ya está registrado",
            "password.required" => "La contraseña no puede estar vacía",
            "password.confirmed" => "Las contraseñas no coinciden"
        ];
    }
}
