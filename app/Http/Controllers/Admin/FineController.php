<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Property;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $query = Fine::with(['propiedad', 'deuda', 'usuario']);

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('propiedad', function($q) use ($search) {
                      $q->where('referencia', 'like', "%{$search}%")
                        ->orWhereHas('client', function($q) use ($search) {
                            $q->where('nombre', 'like', "%{$search}%")
                              ->orWhere('codigo_cliente', 'like', "%{$search}%"); // ✅ NUEVO
                        });
                  });
            });
        }

        // ✅ NUEVO: FILTRO POR CÓDIGO CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->whereHas('propiedad.client', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
            });
        }

        // Filtros existentes
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('activa')) {
            $query->where('activa', $request->activa);
        }

        $multas = $query->latest()->paginate(20);
        $estados = Fine::ESTADO_PENDIENTE;
        $tipos = Fine::obtenerTiposMulta();

        return view('admin.multas.index', compact('multas', 'estados', 'tipos'));
    }

    public function create()
    {
        $propiedades = Property::activas()->with('client')->get();
        $deudas = Debt::pendientes()->with('propiedad')->get();
        $tipos = Fine::obtenerTiposMulta();
        $montosBase = Fine::obtenerMontosBase();

        return view('admin.multas.create', compact('propiedades', 'deudas', 'tipos', 'montosBase'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'deuda_id' => 'nullable|exists:deudas,id',
            'tipo' => 'required|in:' . implode(',', array_keys(Fine::obtenerTiposMulta())),
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'required|string|max:1000',
            'fecha_aplicacion' => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            Fine::create(array_merge($validated, [
                'creado_por' => auth()->id(),
                'estado' => Fine::ESTADO_PENDIENTE,
                'activa' => true,
                'aplicada_automaticamente' => false
            ]));

            // Opcional: Actualizar estado de la propiedad si es multa grave
            if (in_array($validated['tipo'], [
                Fine::TIPO_CONEXION_CLANDESTINA, 
                Fine::TIPO_MANIPULACION_LLAVES
            ])) {
                Property::find($validated['propiedad_id'])
                    ->update(['estado' => 'cortado']);
            }
        });

        return redirect()->route('admin.multas.index')
            ->with('success', 'Multa creada exitosamente.');
    }

    public function show(Fine $multa)
    {
        try {
            // ✅ CARGAR TODAS LAS RELACIONES CON VERIFICACIÓN
            $multa->load([
                'propiedad.client',
                'propiedad.tariff',
                'deuda.propiedad.client',
                'deuda.tarifa',
                'usuario'
            ]);

            // ✅ VERIFICAR Y PREPARAR DATOS PARA LA VISTA
            $cliente = null;
            $propiedad = null;

            if ($multa->propiedad) {
                $propiedad = $multa->propiedad;
                $cliente = $propiedad->client;
            } elseif ($multa->deuda && $multa->deuda->propiedad) {
                $propiedad = $multa->deuda->propiedad;
                $cliente = $propiedad->client;
            }

            return view('admin.multas.show', compact('multa', 'cliente', 'propiedad'));

        } catch (\Exception $e) {
            \Log::error('Error en show de multa: ' . $e->getMessage());
            return redirect()->route('admin.multas.index')
                ->with('error', 'Error al cargar los detalles de la multa: ' . $e->getMessage());
        }
    }

    public function edit(Fine $multa)
    {
        $propiedades = Property::activas()->with('client')->get();
        $deudas = Debt::pendientes()->with('propiedad')->get();
        $tipos = Fine::obtenerTiposMulta();
        $montosBase = Fine::obtenerMontosBase();

        return view('admin.multas.edit', compact('multa', 'propiedades', 'deudas', 'tipos', 'montosBase'));
    }

    public function update(Request $request, Fine $multa)
    {
        // No permitir editar multas automáticas o pagadas
        if ($multa->aplicada_automaticamente || $multa->estado === Fine::ESTADO_PAGADA) {
            return redirect()->back()
                ->with('error', 'No se puede editar una multa automática o ya pagada.');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:' . implode(',', array_keys(Fine::obtenerTiposMulta())),
            'nombre' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'descripcion' => 'required|string|max:1000',
            'fecha_aplicacion' => 'required|date',
        ]);

        $multa->update($validated);

        return redirect()->route('admin.multas.index')
            ->with('success', 'Multa actualizada exitosamente.');
    }

    public function destroy(Fine $multa)
    {
        // En lugar de eliminar, archivamos
        $multa->archivar();

        return redirect()->route('admin.multas.index')
            ->with('success', 'Multa archivada exitosamente.');
    }

    // Acciones adicionales
    public function marcarPagada(Fine $multa)
    {
        $multa->marcarComoPagada();

        return redirect()->back()
            ->with('success', 'Multa marcada como pagada.');
    }

    public function anular(Fine $multa)
    {
        $multa->anular();

        return redirect()->back()
            ->with('success', 'Multa anulada exitosamente.');
    }

    public function restaurar(Fine $multa)
    {
        $multa->restaurar();

        return redirect()->back()
            ->with('success', 'Multa restaurada exitosamente.');
    }

    // API para obtener montos base
    public function obtenerMontoBase(Request $request)
    {
        $tipo = $request->tipo;
        $montos = Fine::obtenerMontosBase();

        return response()->json([
            'monto_base' => $montos[$tipo] ?? 0
        ]);
    }
}