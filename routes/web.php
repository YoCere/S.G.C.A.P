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
    // Redirigir dashboard al panel de admin
    Route::get('/dashboard', function () {
        return redirect()->route('admin.home');
    })->name('dashboard');
});

// ============================================
// RUTAS PARA PRUEBAS DE ERRORES (SOLO LOCAL)
// ============================================

if (app()->environment('local')) {
    
    // Grupo de pruebas de errores
    Route::prefix('test-errors')->name('test.errors.')->group(function () {
        
        // 1. Error 401 - No autenticado
        Route::get('/401', function () {
            abort(401, 'No autenticado para acceder a este recurso');
        })->name('401');
        
        // 2. Error 403 - Prohibido
        Route::get('/403', function () {
            abort(403, 'No tienes permisos para esta acción');
        })->name('403');
        
        // 3. Error 404 - No encontrado
        Route::get('/404', function () {
            abort(404, 'Página no encontrada');
        })->name('404');
        
        // 4. Error 419 - Sesión expirada
        Route::get('/419', function () {
            // Simular error de token CSRF
            throw new \Illuminate\Session\TokenMismatchException('Token CSRF no válido');
        })->name('419');
        
       
        
        // 6. Error 429 - Demasiadas solicitudes
        Route::get('/429', function () {
            abort(429, 'Demasiadas solicitudes. Intenta más tarde.');
        })->name('429');
        
        // 7. Error 500 - Error interno del servidor
        
        
        if (app()->environment('local')) {
            // Probar error 500
            Route::get('/test-500', function() {
                throw new Exception('Esto es un error 500 de prueba');
            });
            
            // Probar error 422
            Route::get('/test-422', function() {
                $validator = validator([], ['email' => 'required|email']);
                throw new \Illuminate\Validation\ValidationException($validator);
            });
        }
        // 8. Error 503 - Servicio no disponible
        Route::get('/503', function () {
            abort(503, 'Servicio en mantenimiento');
        })->name('503');
        
        // 9. Error de base de datos
        Route::get('/db-error', function () {
            throw new \Illuminate\Database\QueryException(
                'SQLSTATE[HY000] [2002] Connection refused',
                [],
                new Exception('Error de conexión a la base de datos')
            );
        })->name('db-error');
        
        // 10. Error de validación
        Route::get('/validation-error', function () {
            $validator = \Illuminate\Support\Facades\Validator::make([], [
                'email' => 'required|email',
            ]);
            
            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }
        })->name('validation-error');
        
        // 11. Panel de pruebas
        Route::get('/panel', function () {
            return view('errors.test-panel');
        })->name('panel');
    });
}