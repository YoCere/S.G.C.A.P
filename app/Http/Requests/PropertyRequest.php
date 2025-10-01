<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; 
class PropertyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'tarifa_id'  => [
                'required',
                Rule::exists('tarifas','id')->whereNull('deleted_at'), // 👈 solo activas
            ],
            'referencia' => 'required|string|max:255',
            'barrio' => 'nullable|in:Centro,Aroma,Los Valles,Caipitandy,Primavera,Arboleda',
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
