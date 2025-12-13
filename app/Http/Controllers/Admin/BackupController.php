<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Jobs\RestoreBackupJob;
use Carbon\Carbon;

class BackupController extends Controller
{
    public function index(Request $request)
    {
        // Obtener backups directamente del almacenamiento
        $disk = $request->get('disk', 's3');
        $storage = Storage::disk($disk);
        
        $prefix = config('backup.backup.name', env('APP_NAME', 'Laravel'));
        
        try {
            $files = $storage->allFiles($prefix);
        } catch (\Exception $e) {
            $files = [];
            Log::warning("Error al listar backups en disco {$disk}: " . $e->getMessage());
        }
        
        // Procesar archivos de backup
        $backups = collect($files)
            ->filter(function ($file) {
                // Filtrar solo archivos .zip (backups)
                return preg_match('/\.zip$/', $file);
            })
            ->map(function ($file) use ($storage, $disk) {
                try {
                    $lastModified = $storage->lastModified($file);
                    $size = $storage->size($file);
                    
                    return (object) [
                        'id' => basename($file),
                        'path' => $file,
                        'disk' => $disk,
                        'size_in_mb' => round($size / 1024 / 1024, 2),
                        'size_bytes' => $size,
                        'created_at' => Carbon::createFromTimestamp($lastModified),
                        'updated_at' => Carbon::createFromTimestamp($lastModified),
                        'status' => 'ok', // Asumimos que está OK si existe
                        'type' => 'completo',
                    ];
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter() // Eliminar nulos
            ->sortByDesc('created_at')
            ->values();
        
        // Obtener último backup
        $lastBackup = $backups->first();
        
        // Paginación manual
        $page = $request->get('page', 1);
        $perPage = 20;
        $total = $backups->count();
        $paginated = $backups->slice(($page - 1) * $perPage, $perPage)->values();
        
        // Crear paginador personalizado
        $backups = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        
        return view('admin.backups.index', compact('backups', 'lastBackup'));
    }

    public function restore($id, Request $request)
{
    $request->validate([
        'disk' => 'nullable|string|in:local,s3'
    ]);

    $disk = $request->input('disk', 'local');
    $logName = 'restore-' . date('Ymd-His') . '.log';
    $logPath = storage_path('logs/' . $logName);

    // Crear archivo de log inmediatamente
    file_put_contents($logPath, "=== RESTORE STARTED ===\n");
    file_put_contents($logPath, "[" . now() . "] Iniciando restauración de: {$id}\n", FILE_APPEND);
    file_put_contents($logPath, "[" . now() . "] Disco: {$disk}\n", FILE_APPEND);
    file_put_contents($logPath, "[" . now() . "] Usuario: " . auth()->id() . "\n", FILE_APPEND);

    // Ejecutar restauración DE FORMA SÍNCRONA
    // Primero crea un backup previo (opcional)
    try {
        file_put_contents($logPath, "[" . now() . "] Creando backup previo...\n", FILE_APPEND);
        \Artisan::call('backup:run');
        file_put_contents($logPath, "[" . now() . "] Backup previo creado\n", FILE_APPEND);
    } catch (\Exception $e) {
        file_put_contents($logPath, "[" . now() . "] Advertencia: No se pudo crear backup previo: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    // Ejecutar comando de restauración
    file_put_contents($logPath, "[" . now() . "] Ejecutando comando de restauración...\n", FILE_APPEND);
    
    $output = [];
    $return = 0;
    exec("php " . base_path("artisan") . " backups:restore \"{$id}\" --disk=\"{$disk}\" 2>&1", $output, $return);
    
    // Guardar output en el log
    $outputText = implode("\n", $output);
    file_put_contents($logPath, "[" . now() . "] Comando ejecutado. Exit code: {$return}\n", FILE_APPEND);
    file_put_contents($logPath, "[" . now() . "] Output:\n{$outputText}\n", FILE_APPEND);

    if ($return === 0) {
        file_put_contents($logPath, "[" . now() . "] ✅ RESTAURACIÓN COMPLETADA CON ÉXITO\n", FILE_APPEND);
        Log::info("Backup restaurado exitosamente: {$id} desde {$disk}");
        
        return response()->json([
            'success' => true,
            'message' => 'Restauración completada exitosamente.',
            'log' => $logName,
        ]);
    } else {
        file_put_contents($logPath, "[" . now() . "] ❌ RESTAURACIÓN FALLÓ\n", FILE_APPEND);
        Log::error("Error restaurando backup: {$id} desde {$disk}", ['output' => $outputText]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error durante la restauración. Revisa los logs.',
            'log' => $logName,
            'error' => $outputText,
        ], 500);
    }
}

public function restoreLog(Request $request)
{
    try {
        $file = $request->get('file');
        
        if (!$file) {
            return response()->json([
                'error' => 'No se especificó archivo de log',
                'type' => 'no_file'
            ], 400);
        }
        
        // Validar que el nombre del archivo sea seguro
        if (!preg_match('/^[a-zA-Z0-9\-_.]+\.log$/', $file)) {
            return response()->json([
                'error' => 'Nombre de archivo inválido',
                'type' => 'invalid_filename'
            ], 400);
        }
        
        $path = storage_path('logs/' . $file);
        
        if (!file_exists($path)) {
            return response()->json([
                'error' => 'Log no encontrado. El proceso puede estar en ejecución o ha finalizado.',
                'type' => 'not_found',
                'tip' => 'Revisa la carpeta storage/logs/ o espera unos segundos',
                'file' => $file
            ], 404);
        }
        
        $content = file_get_contents($path);
        
        // Verificar si el proceso ya terminó
        $isCompleted = str_contains($content, '✅') || 
                       str_contains($content, '🎉') || 
                       str_contains($content, 'RESTORE OK') ||
                       str_contains($content, 'RESTAURACIÓN COMPLETADA') ||
                       str_contains($content, '❌ ERROR');
        
        return response()->json([
            'content' => $content,
            'completed' => $isCompleted,
            'file_size' => filesize($path),
            'last_modified' => date('Y-m-d H:i:s', filemtime($path))
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error en restoreLog: ' . $e->getMessage());
        
        return response()->json([
            'error' => 'Error interno al leer el log',
            'type' => 'internal_error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function download($id)
    {
        $disk = request()->get('disk', 'local');
        $storage = Storage::disk($disk);
        
        // Buscar el archivo
        $prefix = config('backup.backup.name', env('APP_NAME', 'Laravel'));
        $files = $storage->allFiles($prefix);
        
        $file = collect($files)->first(function ($path) use ($id) {
            return basename($path) === $id;
        });
        
        if (!$file) {
            abort(404, 'Backup no encontrado');
        }
        
        // Registrar descarga
        Log::info("Backup descargado: {$id} desde {$disk} por usuario " . auth()->id());
        
        return $storage->download($file);
    }

    public function log($id)
    {
        $disk = request()->get('disk', 'local');
        $storage = Storage::disk($disk);
        
        $logFile = str_replace('.zip', '.log', $id);
        $prefix = config('backup.backup.name', env('APP_NAME', 'Laravel'));
        $files = $storage->allFiles($prefix);
        
        $file = collect($files)->first(function ($path) use ($logFile) {
            return basename($path) === $logFile;
        });
        
        if (!$file) {
            return response()->json(['error' => 'Log no encontrado'], 404);
        }
        
        return response($storage->get($file))
            ->header('Content-Type', 'text/plain');
    }

    public function destroy($id, Request $request)
    {
        $disk = $request->input('disk', 'local');
        $storage = Storage::disk($disk);
        
        $prefix = config('backup.backup.name', env('APP_NAME', 'Laravel'));
        $files = $storage->allFiles($prefix);
        
        $file = collect($files)->first(function ($path) use ($id) {
            return basename($path) === $id;
        });
        
        if ($file && $storage->exists($file)) {
            $storage->delete($file);
            
            // Eliminar log asociado si existe
            $logFile = str_replace('.zip', '.log', $file);
            if ($storage->exists($logFile)) {
                $storage->delete($logFile);
            }
            
            Log::info("Backup eliminado: {$id} desde {$disk} por usuario " . auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => 'Backup eliminado correctamente'
            ]);
        }
        
        abort(404, 'Backup no encontrado');
    }

    public function run()
    {
        try {
            \Artisan::call('backup:run');
            
            Log::info("Backup manual ejecutado por usuario " . auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => 'Backup iniciado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al ejecutar backup: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clean()
    {
        try {
            \Artisan::call('backup:clean');
            
            Log::info("Limpieza de backups ejecutada por usuario " . auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => 'Limpieza de backups ejecutada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error("Error al limpiar backups: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar backups: ' . $e->getMessage()
            ], 500);
        }
    }
}