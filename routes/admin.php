<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\DebtController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\FineController;
use App\Http\Controllers\Admin\CorteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\BackupController;

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        // Dashboard
        Route::get('/', [HomeController::class, 'index'])
            ->middleware('can:admin.home')
            ->name('admin.home');

        // Clients - ✅ ACTUALIZADO
        Route::resource('clients', ClientController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'show', 'destroy']) // ✅ AGREGADO 'destroy'
        ->names('admin.clients');
        

        // ✅ NUEVA RUTA PARA ACTIVAR CLIENTES
        Route::put('/clients/{client}/activate', [ClientController::class, 'activate'])
        ->name('admin.clients.activate');

        // Users - ✅ ACTUALIZADO
        Route::resource('users', UserController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'show', 'destroy']) // ✅ AGREGADO 'destroy'
            ->names('admin.users');

        // ✅ NUEVA RUTA PARA ACTIVAR USUARIOS
        Route::put('/users/{user}/activate', [UserController::class, 'activate'])
            ->name('admin.users.activate');

        // Roles 
        Route::resource('roles', RoleController::class)->names('admin.roles');
        // Rutas para activar/desactivar roles
        Route::put('/roles/{role}/desactivate', [RoleController::class, 'desactivate'])
        ->name('admin.roles.desactivate');
        Route::put('/roles/{role}/activate', [RoleController::class, 'activate'])
        ->name('admin.roles.activate');

        // Tarifas
        Route::resource('tariffs', TariffController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'show'])
            ->names('admin.tariffs');
        Route::put('/tariffs/{tariff}/deactivate', [TariffController::class, 'deactivate'])
            ->middleware('can:admin.tariffs.deactivate')
            ->name('admin.tariffs.deactivate');
        Route::put('/tariffs/{tariff}/activate', [TariffController::class, 'activate'])
            ->middleware('can:admin.tariffs.activate')
            ->name('admin.tariffs.activate');
        
        // Properties
        Route::resource('properties', PropertyController::class)
            ->parameters(['properties' => 'property'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'show'])
            ->names('admin.properties');
        Route::put('/properties/{property}/cut', [PropertyController::class, 'cutService'])
            ->middleware('can:admin.properties.cut')
            ->name('admin.properties.cut');
        Route::put('/properties/{property}/restore', [PropertyController::class, 'restoreService'])
            ->middleware('can:admin.properties.restore')
            ->name('admin.properties.restore');
        Route::put('/properties/{property}/cancel-cut', [PropertyController::class, 'cancelCutService'])
            ->middleware('can:admin.properties.cancel-cut')
            ->name('admin.properties.cancel-cut');
        // 🆕 NUEVA RUTA PARA SOLICITAR RECONEXIÓN
        Route::put('/properties/{property}/request-reconnection', [PropertyController::class, 'requestReconnection'])
            ->middleware('can:admin.properties.request-reconnection')
            ->name('admin.properties.request-reconnection');
        Route::get('/propiedades/buscar', [PropertyController::class, 'search'])
            ->middleware('can:admin.propiedades.search')
            ->name('admin.propiedades.search');
        
        // Deudas
        Route::resource('debts', DebtController::class)
            ->parameters(['debts' => 'debt'])
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->names('admin.debts');
        Route::post('/debts/{debt}/annul', [DebtController::class, 'annul'])
            ->middleware('can:admin.debts.annul')
            ->name('admin.debts.annul');
        Route::post('/debts/{debt}/mark-as-paid', [DebtController::class, 'markAsPaid'])
            ->middleware('can:admin.debts.mark-as-paid')
            ->name('admin.debts.mark-as-paid');

        // Pagos
        Route::resource('pagos', PagoController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'show'])
            ->names('admin.pagos');
        Route::get('/pagos/{pago}/print', [PagoController::class, 'print'])
            ->middleware('can:admin.pagos.print')
            ->name('admin.pagos.print');
        Route::put('/pagos/{pago}/anular', [PagoController::class, 'anular'])
            ->middleware('can:admin.pagos.anular')
            ->name('admin.pagos.anular');
        Route::get('/pagos/obtener-meses-pendientes/{propiedad}', [PagoController::class, 'obtenerMesesPendientesApi'])
            ->middleware('can:admin.pagos.obtenerMesesPendientes')
            ->name('admin.pagos.obtenerMesesPendientes');
        Route::get('/pagos/validar-meses', [PagoController::class, 'validarMeses'])
            ->middleware('can:admin.pagos.validar-meses')
            ->name('admin.pagos.validar-meses');
        Route::get('/properties/{propiedad}/deudaspendientes', [PagoController::class, 'obtenerDeudasPendientes'])
            ->middleware('can:admin.propiedades.deudaspendientes')
            ->name('admin.propiedades.deudaspendientes');
            Route::get('/pagos/obtener-multas-pendientes/{propiedadId}', [PagoController::class, 'obtenerMultasPendientesApi'])
            ->middleware('can:admin.pagos.obtenerMultasPendientes') 
            ->name('admin.pagos.obtenerMultasPendientes');

        // Multas
        Route::resource('multas', FineController::class)
            ->only(['index', 'create', 'store', 'edit', 'destroy','update', 'show'])
            ->names('admin.multas');
        Route::prefix('multas')->group(function () {
            Route::post('/{multa}/marcar-pagada', [FineController::class, 'marcarPagada'])
                ->middleware('can:admin.multas.marcar-pagada')
                ->name('admin.multas.marcar-pagada');
            Route::post('/{multa}/anular', [FineController::class, 'anular'])
                ->middleware('can:admin.multas.anular')
                ->name('admin.multas.anular');
            Route::post('/{multa}/restaurar', [FineController::class, 'restaurar'])
                ->middleware('can:admin.multas.restaurar')
                ->name('admin.multas.restaurar');
            Route::get('/obtener-monto-base', [FineController::class, 'obtenerMontoBase'])
                ->middleware('can:admin.multas.obtener-monto-base')
                ->name('admin.multas.obtener-monto-base');
            
        });
        
        // Cortes
        Route::prefix('cortes')->group(function () {
            Route::get('/pendientes', [CorteController::class, 'indexCortePendiente'])
                ->middleware('can:admin.cortes.pendientes')
                ->name('admin.cortes.pendientes');
            Route::get('/cortadas', [CorteController::class, 'indexCortadas'])
                ->middleware('can:admin.cortes.cortadas')
                ->name('admin.cortes.cortadas');
            Route::post('/marcar-cortado/{propiedad}', [CorteController::class, 'marcarComoCortado'])
                ->middleware('can:admin.cortes.marcar-cortado')
                ->name('admin.cortes.marcar-cortado');
            Route::post('/aplicar-multa/{deuda}', [CorteController::class, 'aplicarMultaReconexion'])
                ->middleware('can:admin.cortes.aplicar-multa')
                ->name('admin.cortes.aplicar-multa');
        });

        
       // Reportes
        Route::prefix('reportes')->group(function () {
            Route::get('/', [ReporteController::class, 'index'])
                ->middleware('can:admin.reportes.index')
                ->name('admin.reportes.index');
            
            Route::get('/morosidad', [ReporteController::class, 'morosidad'])
                ->middleware('can:admin.reportes.morosidad')
                ->name('admin.reportes.morosidad');
            
            Route::get('/clientes', [ReporteController::class, 'clientes'])
                ->middleware('can:admin.reportes.clientes')
                ->name('admin.reportes.clientes');
            
            Route::get('/propiedades', [ReporteController::class, 'propiedades'])
                ->middleware('can:admin.reportes.propiedades')
                ->name('admin.reportes.propiedades');
            
            Route::get('/trabajos-pendientes', [ReporteController::class, 'trabajosPendientes'])
                ->middleware('can:admin.reportes.trabajos')
                ->name('admin.reportes.trabajos-pendientes');
        });

        // Utilidades
        Route::get('/sincronizar-deudas', function() {
            $controller = app()->make(App\Http\Controllers\Admin\PagoController::class);
            $actualizadas = $controller->sincronizarDeudasConPagos();
            return "Deudas actualizadas: {$actualizadas}";
        })->middleware('can:admin.sincronizar-deudas');

        // ACCIONES EXTRA
        Route::post('backups/{id}/restore', [BackupController::class, 'restore'])
        ->name('admin.backups.restore');
    
    Route::get('backups/restore-log', [BackupController::class, 'restoreLog'])
        ->name('admin.backups.restore-log');
    
    // Descarga y log normal
    Route::get('backups/{id}/download', [BackupController::class, 'download'])
        ->name('admin.backups.download');
    
    Route::get('backups/{id}/log', [BackupController::class, 'log'])
        ->name('admin.backups.log');
    
    // RESOURCE (AL FINAL SIEMPRE)
    Route::resource('backups', BackupController::class)
        ->parameters(['backups' => 'id'])
        ->only(['index', 'destroy'])
        ->names('admin.backups');
    
    // Acciones extras
    Route::post('backups/run',   [BackupController::class, 'run'])->name('admin.backups.run');
    Route::post('backups/clean', [BackupController::class, 'clean'])->name('admin.backups.clean');

    // Configuración de multas por mora
Route::prefix('config-multas-mora')->group(function () {
    Route::get('/edit', [\App\Http\Controllers\Admin\ConfigMultaMoraController::class, 'edit'])
        ->name('admin.config-multas-mora.edit');
    Route::put('/', [\App\Http\Controllers\Admin\ConfigMultaMoraController::class, 'update'])
        ->name('admin.config-multas-mora.update');
});

});

