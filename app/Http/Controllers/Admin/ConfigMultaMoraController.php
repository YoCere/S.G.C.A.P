<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigMultaMora;
use Illuminate\Http\Request;

class ConfigMultaMoraController extends Controller
{
    public function edit()
    {
    $config = ConfigMultaMora::first();
    if (!$config) {
        $config = ConfigMultaMora::create([
            'nombre' => 'Multa por mora estándar',
            'descripcion' => 'Configuración automática generada',
            'meses_gracia' => 3,
            'porcentaje_multa' => 10.00,
            'activo' => true,
        ]);
    }
    return view('admin.config-multas-mora.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'meses_gracia' => 'required|integer|min:1|max:12',
            'porcentaje_multa' => 'required|numeric|min:0|max:100',
            'activo' => 'boolean',
        ]);

        $config = ConfigMultaMora::firstOrNew([]);
        $config->fill($request->only([
            'nombre', 'descripcion', 'meses_gracia', 
            'porcentaje_multa', 'activo'
        ]));
        $config->save();

        return redirect()->route('admin.multas.index')
            ->with('success', 'Configuración de multa por mora actualizada correctamente');
    }
}