<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class GuardarNuevaContrasena extends FormRequest
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
        Log::debug("Entrando a validación del GuardarNuevaContrasena", array($this->request->all()));
    }

    protected function failedValidation(Validator $validator)
    {
        Log::debug("Saliendo del validador de GuardarNuevaContrasena. Status: KO", array($this->request->all()));

        parent::failedValidation($validator);
    }

    protected function passedValidation()
    {
        Log::debug("Saliendo del validador de GuardarNuevaContrasena. Status: OK", array($this->request->all()));

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
            "token" => '',
            "password" => "required|confirmed",
        ];
    }
}
