<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleErrors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo procesar si hay un error
        if ($response->isServerError() || $response->isClientError()) {
            $statusCode = $response->getStatusCode();
            
            // Personalizar mensajes según el tipo de error
            if ($request->expectsJson()) {
                return $this->jsonErrorResponse($statusCode);
            }
        }

        return $response;
    }

    /**
     * JSON error responses in Spanish
     */
    protected function jsonErrorResponse(int $statusCode)
    {
        $messages = [
            400 => ['error' => 'Solicitud incorrecta', 'message' => 'La solicitud contiene datos inválidos.'],
            401 => ['error' => 'No autorizado', 'message' => 'Debes iniciar sesión para acceder a este recurso.'],
            403 => ['error' => 'Prohibido', 'message' => 'No tienes permisos para realizar esta acción.'],
            404 => ['error' => 'No encontrado', 'message' => 'El recurso solicitado no existe.'],
            405 => ['error' => 'Método no permitido', 'message' => 'El método HTTP no está permitido para esta ruta.'],
            419 => ['error' => 'Sesión expirada', 'message' => 'Tu sesión ha expirado. Por favor, recarga la página.'],
            422 => ['error' => 'Error de validación', 'message' => 'Los datos proporcionados no son válidos.'],
            429 => ['error' => 'Demasiadas solicitudes', 'message' => 'Has excedido el límite de solicitudes. Intenta más tarde.'],
            500 => ['error' => 'Error interno del servidor', 'message' => 'Ha ocurrido un error inesperado.'],
            503 => ['error' => 'Servicio no disponible', 'message' => 'El servicio está temporalmente no disponible.'],
        ];

        $response = $messages[$statusCode] ?? [
            'error' => 'Error',
            'message' => 'Ha ocurrido un error desconocido.'
        ];

        return response()->json($response, $statusCode);
    }
}