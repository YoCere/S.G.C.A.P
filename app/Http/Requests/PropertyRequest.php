<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'tarifa_id'  => 'required|exists:tarifas,id', // tablas en español
            'referencia' => 'required|string|max:255',
            'latitud'    => 'nullable|numeric|between:-90,90',
            'longitud'   => 'nullable|numeric|between:-180,180',
            'estado'     => 'required|in:activo,inactivo',
        ];
    }

    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'tarifa_id'  => 'tarifa',
        ];
    }
}
