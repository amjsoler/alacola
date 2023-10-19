<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateEstablecimientoRequest extends FormRequest
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
        Log::debug("Entrando a validación del UpdateEstablecimientoRequest", array($this->request->all()));
    }

    protected function failedValidation(Validator $validator)
    {
        Log::debug("Saliendo del validador de UpdateEstablecimientoRequest. Status: KO", array($this->request->all()));

        parent::failedValidation($validator);
    }

    protected function passedValidation()
    {
        Log::debug("Saliendo del validador de UpdateEstablecimientoRequest. Status: OK", array($this->request->all()));

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
            'nombre' => 'required|string|max:100',
            'logo' =>'mimes:jpg,png,jpeg|max:2048|dimensions:max_width=2048,max_height=2048',
            'direccion' => 'string|max:256',
            'descripcion' => 'string|max:5000',
            'latitud' => 'string|max:100',
            'longitud' => 'string|max:100'
        ];
    }

    public function messages()
    {
        return [
            'nombre' => [
                'required' => "Debes especificar un nombre para el establecimiento",
                "string" => "el nombre especificado no es válido ¿Contiene caracteres extraños?",
                "max" => "El nombre no puede superar los 100 caracteres"
            ],
            'logo' => [
                "mimes" => "El formato de imagen ha de ser JPG, JPEG o PNG",
                "max" => "La imagen no puede superer los 2MB",
                "dimensions" => "El ancho y el alto de la imagen no puede ser mayor a 2048px",
            ],
            'direccion' => [
                "string" => "La dirección no es válida ¿Contiene caracteres extraños?",
                "max" => "La dirección no puede superar los 256 caracteres"
            ],
            "descripcion" => [
                "string" => "La descripción no es válida ¿Contiene caracteres extraños?",
                "max" => "La descripción no puede superar los 5000 caracteres"
            ],
            "latitud" => [
                "string" => "La latitud debe ser una cadena válida ¿Contiene caracteres extraños?",
                "max" => "La latitud no puede superar los 100 caracteres"
            ],
            "longitud" => [
                "string" => "La longitud debe ser una cadena válida ¿Contiene caracteres extraños?",
                "max" => "La longitud no puede superar los 100 caracteres"
            ]
        ];
    }
}
