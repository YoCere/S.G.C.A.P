<?php
// app/Helpers/optimization.php
// Funciones seguras de optimización para producción

if (!function_exists('memory_usage')) {
    /**
     * Obtiene el uso de memoria en formato legible
     * Solo para desarrollo/monitoreo
     */
    function memory_usage(): string
    {
        $size = memory_get_usage(true);
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}

if (!function_exists('optimize_query_cache')) {
    /**
     * Helper para cache de queries frecuentes
     * Usar en consultas como: optimize_query_cache('clientes_activos', 300, function() { ... })
     */
    function optimize_query_cache(string $key, int $minutes, callable $callback)
    {
        if (app()->environment('production') && config('cache.default') !== 'file') {
            return cache()->remember($key, $minutes * 60, $callback);
        }
        
        return $callback();
    }
}

if (!function_exists('asset_versioned')) {
    /**
     * Versión de assets para bust cache
     * Usar en vistas: <script src="{{ asset_versioned('js/app.js') }}"></script>
     */
    function asset_versioned(string $path): string
    {
        $version = app()->environment('production') 
            ? config('app.version', '1.0.0')
            : time(); // En desarrollo usa timestamp
        
        $assetPath = asset($path);
        
        return str_contains($assetPath, '?') 
            ? $assetPath . '&v=' . $version
            : $assetPath . '?v=' . $version;
    }
}