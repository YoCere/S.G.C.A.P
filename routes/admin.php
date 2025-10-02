<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\DebtController;

Route::middleware(['auth'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.home');
        Route::resource('clients', ClientController::class)->names('admin.clients');

        //Tarifas routes
        Route::resource('tariffs', TariffController::class)->names('admin.tariffs');
        Route::put('/tariffs/{tariff}/deactivate', [TariffController::class, 'deactivate'])->name('admin.tariffs.deactivate');
        Route::put('/tariffs/{tariff}/activate', [TariffController::class, 'activate'])->name('admin.tariffs.activate');
        
        // Properties con rutas adicionales para corte/restauración
        Route::resource('properties', PropertyController::class)
            ->parameters(['properties' => 'property'])
            ->names('admin.properties');
          Route::put('/properties/{property}/cut', [PropertyController::class, 'cutService'])->name('admin.properties.cut');
        Route::put('/properties/{property}/restore', [PropertyController::class, 'restoreService'])->name('admin.properties.restore');
        
        // Debts
        Route::resource('debts', DebtController::class)
        ->parameters(['debts' => 'debt'])
        ->names('admin.debts')
        ->except(['edit', 'update']); // ❌ ELIMINAR edición

        // ✅ NUEVAS RUTAS PARA ANULACIÓN
        Route::resource('debts', DebtController::class)->names('admin.debts');
Route::post('/debts/{debt}/annul', [DebtController::class, 'annul'])->name('admin.debts.annul');
Route::post('/debts/{debt}/mark-as-paid', [DebtController::class, 'markAsPaid'])->name('admin.debts.mark-as-paid');
    });