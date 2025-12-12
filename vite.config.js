import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    
    // ✅ OPTIMIZACIONES SEGURAS
    build: {
        // Minificación segura (no afecta a Livewire)
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Elimina console.log en producción
            },
        },
        
        // Dividir chunks para mejor cache
        rollupOptions: {
            output: {
                manualChunks: {
                    // Solo separa librerías grandes y estables
                    vendor: ['axios', 'lodash'],
                    // Bootstrap separado (es grande)
                    bootstrap: ['bootstrap', '@popperjs/core'],
                },
            },
        },
        
        // Reporte de tamaño
        reportCompressedSize: true,
        
        // Límite de warning de chunk size
        chunkSizeWarningLimit: 500, // Reducido de 1000 a 500kb
    },
    
    // ✅ Configuración de servidor para desarrollo
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    
    // ✅ Optimización de resolución de módulos
    resolve: {
        alias: {
            // Agrega alias si tienes paths específicos
        },
    },
});