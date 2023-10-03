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
        Log::info("Entrando a validación del StoreEstablecimientoRequest", array($this->request->all()));
    }

    protected function failedValidation(Validator $validator)
    {
        Log::info("Saliendo del validador de StoreEstablecimientoRequest. Status: KO", array($this->request->all()));

        parent::failedValidation($validator);
    }

    protected function passedValidation()
    {
        Log::info("Saliendo del validador de StoreEstablecimientoRequest. Status: OK", array($this->request->all()));

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
            'nombre' => 'required',
            'logo' =>'',
            'direccion' => ''
        ];
    }

    public function messages()
    {
        return [
            'nombre' => [
                'required' => __("establecimientos.validaciones.updatenombrerequired")
            ],
            'logo' => [],
            'direccion' => []
        ];
    }
}
