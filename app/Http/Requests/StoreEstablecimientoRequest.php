<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreEstablecimientoRequest extends FormRequest
{
    /**
     * Se usa el policy en vez de este método, por tanto aquí siempre devolvemos true
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
        Log::info("Entrando a validación del StoreEstablecimientoRequest", array("userID:" => auth()->user()->id,
            "request:" => $this->request->all()));
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
        Log::info("Saliendo del validador de StoreEstablecimientoRequest. Status: KO", array("userID:" => auth()->user()->id,
            "request:" => $this->request->all()));

        parent::failedValidation($validator);
    }

    /**
     * Función que se llama cuando la validación pasa
     * @return void
     */
    protected function passedValidation()
    {
        Log::info("Saliendo del validador de StoreEstablecimientoRequest. Status: OK", array("userID:" => auth()->user()->id,
            "request:" => $this->request->all()));

        parent::passedValidation();
    }

    /**
     * Reglas de validación
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => 'required|max:100',
            'logo' => 'mimes:jpg,jpeg,png',
            'direccion' => 'max:250',
            "descripcion" => 'max:5000'
        ];
    }

    /**
     * Mensajes de validación
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nombre.required' => __("establecimientos.validaciones.storenombrerequired"),
            'nombre.max' => __("establecimientos.validaciones.storenombremax"),
            'logo.mimes' => __("establecimientos.validaciones.storelogoimage"),
            'logo.max' => __("establecimientos.validaciones.storelogomax"),
            'logo.dimensions' => __("establecimientos.validaciones.storelogodimensions"),
            'direccion.max' => __("establecimientos.validaciones.storedireccionmax"),
            "descripcion.max" => __("establecimientos.validaciones.storedescripcionmax"),
        ];
    }
}
