<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class SearchEstablecimientoCercanoRequest extends FormRequest
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
        Log::debug("Entrando a validación del SearchEstablecimientoCercanoRequest",
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
        Log::debug("Saliendo del validador de SearchEstablecimientoCercanoRequest. Status: KO",
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
        Log::debug("Saliendo del validador de SearchEstablecimientoCercanoRequest. Status: OK",
            array(
                "request:" => $this->request->all()
            )
        );

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
            "latitud" => "required|max:100",
            "longitud" => "required|max:100"
        ];
    }

    public function messages()
    {
        return [
            "latitud" => [
                "requred" => "La latitud no puede estar vacía",
                "max" => "La latitud no puede superar los 100 caracteres"
            ],
            "longitud" => [
                "requred" => "La longitud no puede estar vacía",
                "max" => "La longitud no puede superar los 100 caracteres"
            ]
        ];
    }
}
