<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\DebtController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\FineController; // ✅ NUEVO
use App\Http\Controllers\Admin\CorteController; // ✅ NUEVO

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.home');
        Route::resource('clients', ClientController::class)->names('admin.clients');

        // Tarifas routes
        Route::resource('tariffs', TariffController::class)->names('admin.tariffs');
        Route::put('/tariffs/{tariff}/deactivate', [TariffController::class, 'deactivate'])->name('admin.tariffs.deactivate');
        Route::put('/tariffs/{tariff}/activate', [TariffController::class, 'activate'])->name('admin.tariffs.activate');
        
        // Properties con rutas adicionales para corte/restauración
        Route::resource('properties', PropertyController::class)
            ->parameters(['properties' => 'property'])
            ->names('admin.properties');
        Route::put('/properties/{property}/cut', [PropertyController::class, 'cutService'])->name('admin.properties.cut');
        Route::put('/properties/{property}/restore', [PropertyController::class, 'restoreService'])->name('admin.properties.restore');
        
        // Búsqueda de propiedades
        Route::get('/propiedades/buscar', [PropertyController::class, 'search'])->name('admin.propiedades.search');
        
        // ✅ RUTA CORREGIDA PARA DEUDAS PENDIENTES
        Route::get('/propiedades/{propiedad}/deudaspendientes', [PagoController::class, 'obtenerDeudasPendientes'])->name('admin.propiedades.deudaspendientes');
        
        // Deudas
        Route::resource('debts', DebtController::class)
            ->parameters(['debts' => 'debt'])
            ->names('admin.debts')
            ->except(['edit', 'update']);
            
        Route::post('/debts/{debt}/annul', [DebtController::class, 'annul'])->name('admin.debts.annul');
        Route::post('/debts/{debt}/mark-as-paid', [DebtController::class, 'markAsPaid'])->name('admin.debts.mark-as-paid');

        // Pagos
        Route::resource('pagos', PagoController::class)->names('admin.pagos');
        Route::get('/pagos/{pago}/print', [PagoController::class, 'print'])->name('admin.pagos.print');
        Route::put('/pagos/{pago}/anular', [PagoController::class, 'anular'])->name('admin.pagos.anular');
        
        // ✅ NUEVO: CRUD COMPLETO DE MULTAS
        Route::resource('multas', FineController::class)->names('admin.multas');
        
        // ✅ NUEVO: ACCIONES ADICIONALES PARA MULTAS
        Route::prefix('multas')->group(function () {
            Route::post('/{multa}/marcar-pagada', [FineController::class, 'marcarPagada'])->name('admin.multas.marcar-pagada');
            Route::post('/{multa}/anular', [FineController::class, 'anular'])->name('admin.multas.anular');
            Route::post('/{multa}/restaurar', [FineController::class, 'restaurar'])->name('admin.multas.restaurar');
            Route::get('/obtener-monto-base', [FineController::class, 'obtenerMontoBase'])->name('admin.multas.obtener-monto-base');
        });
        
        // ✅ NUEVO: RUTAS PARA GESTIÓN DE CORTES
        Route::prefix('cortes')->group(function () {
            Route::get('/pendientes', [CorteController::class, 'indexCortePendiente'])->name('admin.cortes.pendientes');
            Route::get('/cortadas', [CorteController::class, 'indexCortadas'])->name('admin.cortes.cortadas');
            Route::post('/marcar-cortado/{propiedad}', [CorteController::class, 'marcarComoCortado'])->name('admin.cortes.marcar-cortado');
            Route::post('/aplicar-multa/{deuda}', [CorteController::class, 'aplicarMultaReconexion'])->name('admin.cortes.aplicar-multa');
            Route::put('/properties/{property}/cancel-cut', [PropertyController::class, 'cancelCutService'])
            ->name('admin.properties.cancel-cut');
        });
        
    });