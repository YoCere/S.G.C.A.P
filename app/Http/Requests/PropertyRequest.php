<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Property;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }

    public function rules(): array
    {
        $propertyId = $this->route('property') ? $this->route('property')->id : null;

        return [
            'cliente_id' => 'required|exists:clientes,id',
            'tarifa_id'  => [
                'required',
                Rule::exists('tarifas', 'id'), // ← PERMITE tarifas inactivas para mantener integridad
            ],
            'referencia' => [
                'required',
                'string',
                'max:255',
                Rule::unique('propiedades', 'referencia')->ignore($propertyId),
            ],
            // ❌ ELIMINADO: 'direccion' => 'required|string|max:255', (no existe en el formulario)
            'barrio' => 'nullable|in:Centro,Aroma,Los Valles,Caipitandy,Primavera,Arboleda,Fatima',
            'latitud'    => 'nullable|numeric|between:-90,90',
            'longitud'   => 'nullable|numeric|between:-180,180',
            // 🆕 ACTUALIZADO: Incluir nuevo estado
            'estado'     => 'required|in:pendiente_conexion,activo,inactivo,cortado,corte_pendiente',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'tarifa_id.required' => 'La tarifa es obligatoria',
            'tarifa_id.exists' => 'La tarifa seleccionada no existe',
            'referencia.required' => 'La referencia es obligatoria',
            'referencia.unique' => 'Esta referencia ya está en uso',
            // ❌ ELIMINADO: 'direccion.required' => 'La dirección es obligatoria',
            'barrio.in' => 'El barrio seleccionado no es válido',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado seleccionado no es válido',
        ];
    }

    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'tarifa_id'  => 'tarifa',
            // ❌ ELIMINADO: 'direccion' => 'dirección',
        ];
    }
}