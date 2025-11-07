<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

// Rutas públicas
Route::get("/", [WelcomeController::class, "welcome"])->name('welcome');
Route::get('/nosotros', [WelcomeController::class, 'nosotros'])->name('nosotros');
Route::get('/servicios', [WelcomeController::class, 'servicios'])->name('servicios');
Route::get('/tarifas', [WelcomeController::class, 'tarifas'])->name('tarifas');
Route::get('/contacto', [WelcomeController::class, 'contacto'])->name('contacto');

// Consulta de deudas
Route::get('/consultar-deuda', [WelcomeController::class, 'consultarDeuda'])->name('consultar-deuda');
Route::post('/consultar-deuda', [WelcomeController::class, 'buscarDeuda'])->name('buscar-deuda');

// Rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});