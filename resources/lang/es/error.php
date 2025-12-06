<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mensajes de Error en Español
    |--------------------------------------------------------------------------
    */

    'http' => [
        '400' => 'Solicitud incorrecta',
        '401' => 'No autorizado',
        '402' => 'Pago requerido',
        '403' => 'Prohibido',
        '404' => 'No encontrado',
        '405' => 'Método no permitido',
        '406' => 'No aceptable',
        '408' => 'Tiempo de espera agotado',
        '409' => 'Conflicto',
        '410' => 'Desaparecido',
        '411' => 'Longitud requerida',
        '412' => 'Precondición fallida',
        '413' => 'Solicitud demasiado grande',
        '414' => 'URI demasiado largo',
        '415' => 'Tipo de medio no soportado',
        '416' => 'Rango no satisfactorio',
        '417' => 'Expectativa fallida',
        '422' => 'Entidad no procesable',
        '423' => 'Bloqueado',
        '424' => 'Dependencia fallida',
        '425' => 'Demasiado temprano',
        '426' => 'Actualización requerida',
        '428' => 'Precondición requerida',
        '429' => 'Demasiadas solicitudes',
        '431' => 'Campos de encabezado demasiado grandes',
        '451' => 'No disponible por razones legales',
        '500' => 'Error interno del servidor',
        '501' => 'No implementado',
        '502' => 'Puerta de enlace incorrecta',
        '503' => 'Servicio no disponible',
        '504' => 'Tiempo de espera de puerta de enlace',
        '505' => 'Versión HTTP no soportada',
        '506' => 'Variante también negocia',
        '507' => 'Espacio insuficiente',
        '508' => 'Bucle detectado',
        '510' => 'No extendido',
        '511' => 'Autenticación de red requerida',
    ],

    'database' => [
        'connection' => 'Error de conexión a la base de datos',
        'query' => 'Error en la consulta a la base de datos',
        'model_not_found' => 'El recurso solicitado no fue encontrado',
        'constraint' => 'Error de restricción en la base de datos',
    ],

    'auth' => [
        'unauthenticated' => 'Por favor, inicia sesión para continuar',
        'unauthorized' => 'No tienes permisos para realizar esta acción',
        'throttle' => 'Demasiados intentos de inicio de sesión. Por favor, intenta nuevamente en :seconds segundos.',
    ],

    'validation' => [
        'required' => 'El campo :attribute es obligatorio',
        'email' => 'El campo :attribute debe ser un email válido',
        'unique' => 'El valor de :attribute ya está en uso',
        'confirmed' => 'La confirmación de :attribute no coincide',
        'min' => [
            'string' => 'El campo :attribute debe tener al menos :min caracteres',
            'numeric' => 'El campo :attribute debe ser al menos :min',
        ],
        'max' => [
            'string' => 'El campo :attribute no debe exceder :max caracteres',
            'numeric' => 'El campo :attribute no debe ser mayor que :max',
        ],
    ],

    'custom' => [
        'backup' => [
            'failed' => 'Error al crear el backup',
            'restore_failed' => 'Error al restaurar el backup',
            'file_not_found' => 'Archivo de backup no encontrado',
        ],
        'payment' => [
            'failed' => 'Error al procesar el pago',
            'insufficient_funds' => 'Fondos insuficientes',
            'transaction_failed' => 'Transacción fallida',
        ],
        'client' => [
            'not_found' => 'Cliente no encontrado',
            'inactive' => 'El cliente está inactivo',
        ],
        'property' => [
            'not_found' => 'Propiedad no encontrada',
            'no_service' => 'La propiedad no tiene servicio activo',
        ],
    ],

    'general' => [
        'server_error' => 'Error interno del servidor',
        'not_found' => 'Recurso no encontrado',
        'method_not_allowed' => 'Método no permitido',
        'maintenance' => 'Sitio en mantenimiento',
        'too_many_requests' => 'Demasiadas solicitudes',
        'session_expired' => 'Sesión expirada',
    ],

    'actions' => [
        'go_home' => 'Ir al Inicio',
        'go_back' => 'Volver Atrás',
        'try_again' => 'Intentar de Nuevo',
        'refresh' => 'Actualizar Página',
        'contact_support' => 'Contactar Soporte',
        'view_details' => 'Ver Detalles',
    ],

    'messages' => [
        'contact_admin' => 'Si el problema persiste, contacta al administrador del sistema.',
        'try_later' => 'Por favor, intenta nuevamente más tarde.',
        'check_url' => 'Verifica que la URL sea correcta.',
        'clear_cache' => 'Intenta limpiar la caché del navegador.',
        'disable_extensions' => 'Deshabilita extensiones del navegador que puedan interferir.',
    ],
];