<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ApuntarseComoInvitadoRequest extends FormRequest
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
        Log::debug("Entrando a validación del ApuntarseComoInvitadoRequest", array($this->request->all()));
    }

    protected function failedValidation(Validator $validator)
    {
        Log::debug("Saliendo del validador de ApuntarseComoInvitadoRequest. Status: KO", array($this->request->all()));

        parent::failedValidation($validator);
    }

    protected function passedValidation()
    {
        Log::debug("Saliendo del validador de ApuntarseComoInvitadoRequest. Status: OK", array($this->request->all()));

        parent::passedValidation();
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "nombre_usuario_anonimo" => "required|string|max:100"
        ];
    }

    public function messages()
    {
        return [
            'nombre_usuario_anonimo' => [
                'required' => "Debes especificar un nombre de usuario",
                "string" => "El nombre de usuario no es válido ¿Contiene algún caracter extraño?",
                "max" => "El nombre de usuario no puede superar los 100 caracteres"
            ]
        ];
    }
}
