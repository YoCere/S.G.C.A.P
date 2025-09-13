<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ClientController;

Route::middleware(['auth'])
    ->prefix('admin')
    
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('admin.home');
        Route::resource('clients', ClientController::class)->names('admin.clients');
    });