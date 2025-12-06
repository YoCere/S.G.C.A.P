<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log personalizado para diferentes tipos de errores
            if (app()->environment('production')) {
                $this->logException($e);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // 1. Error 419 - Token CSRF (Sesión expirada)
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return $this->renderErrorPage(419, $exception, $request);
        }

        // 2. Error 422 - Validación
        if ($exception instanceof ValidationException) {
            return $this->renderValidationError($exception, $request);
        }

        // 3. Error 404 - No encontrado
        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return $this->renderErrorPage(404, $exception, $request);
        }

        // 4. Error 403 - Prohibido
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->renderErrorPage(403, $exception, $request);
        }

        // 5. Error 401 - No autenticado
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        // 6. Error HTTP genérico (incluye 500, 503, etc.)
        if ($exception instanceof HttpException) {
            return $this->renderHttpException($exception, $request);
        }

        // 7. CUALQUIER otra excepción → Error 500
        return $this->renderErrorPage(500, $exception, $request);
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'No autenticado. Por favor, inicia sesión.'], 401)
            : redirect()->guest(route('login'))->with('error', 'Por favor, inicia sesión para continuar.');
    }

    /**
     * Render HTTP exceptions with custom views
     */
    protected function renderHttpException(HttpException $e, $request)
    {
        $status = $e->getStatusCode();
        return $this->renderErrorPage($status, $e, $request);
    }

    /**
     * Render validation exceptions
     */
    protected function renderValidationError(ValidationException $e, $request)
    {
        // Si es AJAX/JSON, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Si no, redirigir de vuelta con errores
        if ($request->isMethod('GET')) {
            return redirect()->back()
                ->withInput($request->input())
                ->withErrors($e->errors());
        }

        // Para otros métodos, mostrar vista 422
        return $this->renderErrorPage(422, $e, $request);
    }

    /**
     * Render custom error page
     */
    protected function renderErrorPage($statusCode, Throwable $exception, $request)
    {
        // Definir iconos para cada error
        $icons = [
            401 => 'user-lock',
            403 => 'ban',
            404 => 'map-signs',
            419 => 'clock',
            422 => 'exclamation-circle',
            429 => 'tachometer-alt',
            500 => 'server',
            503 => 'tools',
        ];

        // Vista personalizada
        $view = "errors.{$statusCode}";
        
        // Si no existe la vista específica, usar 500
        if (!view()->exists($view)) {
            $view = 'errors.500';
            $statusCode = 500;
        }

        // Pasar datos a la vista
        $data = [
            'exception' => $exception,
            'iconName' => $icons[$statusCode] ?? 'exclamation-triangle',
        ];

        // Solo en local añadir información extra
        if (app()->environment('local')) {
            $data['debug'] = true;
        }

        return response()->view($view, $data, $statusCode);
    }

    /**
     * Log exceptions with Spanish messages
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
        ];

        if ($e instanceof HttpException) {
            \Log::error('Error HTTP ' . $e->getStatusCode() . ': ' . $this->getHttpErrorMessage($e->getStatusCode()), $context);
        } elseif ($e instanceof \Illuminate\Database\QueryException) {
            \Log::error('Error de Base de Datos: ' . $e->getMessage(), $context);
        } elseif ($e instanceof ValidationException) {
            \Log::warning('Error de Validación', $context);
        } else {
            \Log::error('Error Inesperado: ' . $e->getMessage(), $context);
        }
    }

    /**
     * Get HTTP error message in Spanish
     */
    protected function getHttpErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Solicitud incorrecta',
            401 => 'No autorizado',
            402 => 'Pago requerido',
            403 => 'Prohibido',
            404 => 'No encontrado',
            405 => 'Método no permitido',
            406 => 'No aceptable',
            408 => 'Tiempo de espera agotado',
            409 => 'Conflicto',
            410 => 'Desaparecido',
            411 => 'Longitud requerida',
            412 => 'Precondición fallida',
            413 => 'Solicitud demasiado grande',
            414 => 'URI demasiado largo',
            415 => 'Tipo de medio no soportado',
            416 => 'Rango no satisfactorio',
            417 => 'Expectativa fallida',
            418 => 'Soy una tetera',
            422 => 'Entidad no procesable',
            423 => 'Bloqueado',
            424 => 'Dependencia fallida',
            425 => 'Demasiado temprano',
            426 => 'Actualización requerida',
            428 => 'Precondición requerida',
            429 => 'Demasiadas solicitudes',
            431 => 'Campos de encabezado demasiado grandes',
            451 => 'No disponible por razones legales',
            500 => 'Error interno del servidor',
            501 => 'No implementado',
            502 => 'Puerta de enlace incorrecta',
            503 => 'Servicio no disponible',
            504 => 'Tiempo de espera de puerta de enlace',
            505 => 'Versión HTTP no soportada',
            506 => 'Variante también negocia',
            507 => 'Espacio insuficiente',
            508 => 'Bucle detectado',
            510 => 'No extendido',
            511 => 'Autenticación de red requerida',
        ];

        return $messages[$statusCode] ?? 'Error desconocido';
    }
}