<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

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

        $this->renderable(function (Throwable $e, $request) {
            return $this->renderCustomException($e, $request);
        });
    }

    /**
     * Render custom exceptions in Spanish
     */
    protected function renderCustomException(Throwable $e, $request)
    {
        // Personalizar mensajes según el tipo de excepción
        if ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [
                'exception' => $e,
                'message' => 'El recurso solicitado no fue encontrado.'
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [
                'exception' => $e,
                'message' => 'La página que buscas no existe.'
            ], 404);
        }

        if ($e instanceof TokenMismatchException) {
            return response()->view('errors.419', [
                'exception' => $e,
                'message' => 'Tu sesión ha expirado. Por favor, recarga la página.'
            ], 419);
        }

        if ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }

        if ($e instanceof QueryException) {
            $errorCode = $e->getCode();
            
            $messages = [
                1045 => 'Error de acceso a la base de datos. Verifica las credenciales.',
                1049 => 'La base de datos no existe.',
                2002 => 'No se puede conectar al servidor de base de datos.',
                23000 => 'Error de integridad referencial. Datos relacionados no permiten esta operación.',
                '42S02' => 'La tabla no existe en la base de datos.',
                '42S22' => 'La columna no existe en la tabla.',
            ];

            $message = $messages[$errorCode] ?? 'Error en la consulta a la base de datos.';

            return response()->view('errors.500', [
                'exception' => $e,
                'message' => $message,
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }

        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $viewName = "errors.{$statusCode}";

            if (view()->exists($viewName)) {
                return response()->view($viewName, [
                    'exception' => $e,
                    'message' => $this->getHttpErrorMessage($statusCode)
                ], $statusCode);
            }
        }

        // Error general
        return response()->view('errors.500', [
            'exception' => $e,
            'message' => $this->getErrorMessage($e)
        ], 500);
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
     * Get user-friendly error messages
     */
    protected function getErrorMessage(Throwable $e): string
    {
        $defaultMessages = [
            401 => 'No autorizado. Por favor, inicia sesión.',
            403 => 'Acceso prohibido. No tienes permisos.',
            404 => 'Página no encontrada.',
            419 => 'Sesión expirada. Recarga la página.',
            429 => 'Demasiadas solicitudes. Espera un momento.',
            500 => 'Error interno del servidor.',
            503 => 'Servicio en mantenimiento.',
        ];

        $statusCode = method_exists($e, 'getStatusCode') 
            ? $e->getStatusCode() 
            : 500;

        return $defaultMessages[$statusCode] ?? 'Ha ocurrido un error inesperado.';
    }

    /**
     * Get HTTP error message in Spanish
     */
    protected function getHttpErrorMessage(int $statusCode): string
    {
        $messages = [
            400 => 'Solicitud incorrecta.',
            401 => 'No autorizado.',
            402 => 'Pago requerido.',
            403 => 'Prohibido.',
            404 => 'No encontrado.',
            405 => 'Método no permitido.',
            406 => 'No aceptable.',
            408 => 'Tiempo de espera agotado.',
            409 => 'Conflicto.',
            410 => 'Desaparecido.',
            411 => 'Longitud requerida.',
            412 => 'Precondición fallida.',
            413 => 'Solicitud demasiado grande.',
            414 => 'URI demasiado largo.',
            415 => 'Tipo de medio no soportado.',
            416 => 'Rango no satisfactorio.',
            417 => 'Expectativa fallida.',
            418 => 'Soy una tetera.',
            422 => 'Entidad no procesable.',
            423 => 'Bloqueado.',
            424 => 'Dependencia fallida.',
            425 => 'Demasiado temprano.',
            426 => 'Actualización requerida.',
            428 => 'Precondición requerida.',
            429 => 'Demasiadas solicitudes.',
            431 => 'Campos de encabezado demasiado grandes.',
            451 => 'No disponible por razones legales.',
            500 => 'Error interno del servidor.',
            501 => 'No implementado.',
            502 => 'Puerta de enlace incorrecta.',
            503 => 'Servicio no disponible.',
            504 => 'Tiempo de espera de puerta de enlace.',
            505 => 'Versión HTTP no soportada.',
            506 => 'Variante también negocia.',
            507 => 'Espacio insuficiente.',
            508 => 'Bucle detectado.',
            510 => 'No extendido.',
            511 => 'Autenticación de red requerida.',
        ];

        return $messages[$statusCode] ?? 'Error desconocido.';
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
        } elseif ($e instanceof QueryException) {
            \Log::error('Error de Base de Datos: ' . $e->getMessage(), $context);
        } elseif ($e instanceof ValidationException) {
            \Log::warning('Error de Validación', $context);
        } else {
            \Log::error('Error Inesperado: ' . $e->getMessage(), $context);
        }
    }
}