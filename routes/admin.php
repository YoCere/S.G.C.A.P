<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\PropertyController;

Route::middleware(['auth'])
    ->prefix('admin')
    
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.home');
        Route::resource('clients', ClientController::class)->names('admin.clients');
        Route::resource('tariffs', TariffController::class)->names('admin.tariffs');
        Route::resource('properties', PropertyController::class)->parameters(['properties' => 'property']) ->names('admin.properties');
    });