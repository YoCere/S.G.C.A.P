<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;

class BackupController extends Controller
{
    use AuthorizesRequests;

    protected $backupPathFallback = 'laravel-backups';

    public function __construct()
    {
        $this->middleware('auth');

        // proteger métodos con permisos (ajusta según tu Gate/Policy)
        $this->middleware('can:admin.backups.index')->only('index');
        $this->middleware('can:admin.backups.run')->only('run');
        $this->middleware('can:admin.backups.clean')->only('clean');
        $this->middleware('can:admin.backups.download')->only('download');
        $this->middleware('can:admin.backups.log')->only('log');
        $this->middleware('can:admin.backups.destroy')->only('destroy');
    }

    /**
     * Detecta carpeta de backups en disco local usando heurística simple.
     */
    protected function detectLocalBackupFolder(): string
    {
        $disk = Storage::disk('local');

        // 1) buscar carpeta con nombre configurado (config('backup.name'))
        $candidate = config('backup.name', env('APP_NAME', 'Laravel'));
        if ($disk->exists($candidate) || in_array($candidate, $disk->directories('/'))) {
            return $candidate;
        }

        // 2) fallback común 'laravel-backups'
        if ($disk->exists($this->backupPathFallback) || in_array($this->backupPathFallback, $disk->directories('/'))) {
            return $this->backupPathFallback;
        }

        // 3) buscar cualquier carpeta que contenga .zip
        foreach ($disk->directories('') as $dir) {
            $files = $disk->files($dir);
            foreach ($files as $file) {
                if (Str::endsWith($file, '.zip')) {
                    return $dir;
                }
            }
        }

        // 4) por defecto usar fallback
        return $this->backupPathFallback;
    }

    /**
     * Devuelve colección combinada de backups (local + s3)
     */
    protected function gatherBackups()
    {
        $backups = collect();

        // --- Local ---
        try {
            $diskLocal = Storage::disk('local');
            $localFolder = $this->detectLocalBackupFolder();

            if ($diskLocal->exists($localFolder) || in_array($localFolder, $diskLocal->directories('/'))) {
                $localFiles = $diskLocal->files($localFolder);
            } else {
                // as fallback buscar zips en root storage/app
                $localFiles = array_filter($diskLocal->files('/'), fn($f) => Str::endsWith($f, '.zip'));
            }

            foreach ($localFiles as $path) {
                if (! Str::endsWith($path, '.zip')) continue;
                $size = null; $timestamp = null;
                try { $size = $diskLocal->size($path); } catch (\Throwable $e) { $size = null; }
                try { $timestamp = $diskLocal->lastModified($path); } catch (\Throwable $e) { $timestamp = null; }
                $createdAt = $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
                $basename = basename($path);

                $backups->push((object)[
                    'id' => $basename,
                    'path' => $path,
                    'file_name' => $basename,
                    'size' => $size,
                    'size_in_mb' => is_numeric($size) ? $size / 1024 / 1024 : null,
                    'disk' => 'local',
                    'created_at' => $createdAt,
                    'status' => 'ok',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error listando backups locales: ' . $e->getMessage(), ['exception' => $e]);
        }

        // --- S3 ---
        try {
            $diskS3 = Storage::disk('s3');
            // Spatie suele usar config('backup.name') as prefix
            $s3Prefix = config('backup.name', env('APP_NAME', 'Laravel'));
            $s3Files = [];
            try {
                $s3Files = $diskS3->allFiles($s3Prefix);
            } catch (\Throwable $e) {
                // si prefix no existe, intentar listar todo y filtrar zips
                try {
                    $s3Files = $diskS3->allFiles('');
                } catch (\Throwable $e2) {
                    $s3Files = [];
                }
            }

            foreach ($s3Files as $path) {
                if (! Str::endsWith($path, '.zip')) continue;
                $size = null; $timestamp = null;
                try { $size = $diskS3->size($path); } catch (\Throwable $e) { $size = null; }
                try { $timestamp = $diskS3->lastModified($path); } catch (\Throwable $e) { $timestamp = null; }
                $createdAt = $timestamp ? Carbon::createFromTimestamp($timestamp) : null;
                $basename = basename($path);

                $backups->push((object)[
                    'id' => $basename,
                    'path' => $path,
                    'file_name' => $basename,
                    'size' => $size,
                    'size_in_mb' => is_numeric($size) ? $size / 1024 / 1024 : null,
                    'disk' => 's3',
                    'created_at' => $createdAt,
                    'status' => 'ok',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error listando backups en S3: ' . $e->getMessage(), ['exception' => $e]);
        }

        // Ordenar por created_at desc (si null, dejar al final)
        $sorted = $backups->sortByDesc(fn($b) => $b->created_at ? $b->created_at->getTimestamp() : 0)->values();

        return $sorted;
    }

    /**
     * Index: lista paginada combinada.
     */
    public function index(Request $request)
    {
        $this->authorize('admin.backups.index');

        $all = $this->gatherBackups();

        // Filtrado por disk si se pasa ?disk=s3|local
        $filterDisk = $request->get('disk');
        if ($filterDisk) {
            $all = $all->filter(fn($b) => $b->disk === $filterDisk)->values();
        }

        // Paginación manual
        $perPage = 15;
        $page = max(1, (int)$request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        $paginated = new LengthAwarePaginator(
            $all->slice($offset, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $lastBackup = $all->first();

        return view('admin.backups.index', [
            'backups' => $paginated,
            'lastBackup' => $lastBackup,
        ]);
    }

    /**
     * Ejecutar backup manual (async-ish via Artisan)
     */
    public function run(Request $request)
    {
        $this->authorize('admin.backups.run');

        try {
            $start = now()->format('Ymd-His');
            $logPath = storage_path("logs/backup-{$start}.log");

            // Ejecutar
            ob_start();
            $exit = Artisan::call('backup:run', [
                // puedes pasar flags si quieres
            ]);
            $output = trim(ob_get_clean() ?: Artisan::output());
            file_put_contents($logPath, $output);

            Log::info("Backup manual ejecutado por user_id=" . optional($request->user())->id, ['output' => Str::limit($output, 2000)]);

            return response()->json([
                'ok' => true,
                'message' => 'Backup iniciado. Revisa historial o notificaciones.',
                'log' => $logPath,
                'exitcode' => $exit,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al ejecutar backup manual: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['ok' => false, 'message' => 'Error al iniciar backup: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ejecutar limpieza (backup:clean)
     */
    public function clean(Request $request)
    {
        $this->authorize('admin.backups.clean');

        try {
            $start = now()->format('Ymd-His');
            ob_start();
            $exit = Artisan::call('backup:clean');
            $output = trim(ob_get_clean() ?: Artisan::output());
            $logPath = storage_path("logs/backup-clean-{$start}.log");
            file_put_contents($logPath, $output);

            Log::info("Backup clean ejecutado por user_id=" . optional($request->user())->id, ['output' => Str::limit($output, 2000)]);

            return response()->json(['ok' => true, 'message' => 'Limpieza ejecutada correctamente.', 'log' => $logPath, 'exitcode' => $exit]);
        } catch (\Throwable $e) {
            Log::error('Error al ejecutar backup:clean: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['ok' => false, 'message' => 'Error al ejecutar limpieza: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descargar backup (intenta en disco solicitado o lo busca)
     */
    public function download(Request $request, $id)
    {
        $this->authorize('admin.backups.download');

        // se puede pasar ?disk=s3 o ?disk=local
        $diskParam = $request->get('disk');

        $possibleDisks = $diskParam ? [$diskParam] : ['local', 's3'];

        foreach ($possibleDisks as $diskName) {
            try {
                if (! in_array($diskName, ['local', 's3'])) continue;
                $disk = Storage::disk($diskName);
                // detect folder / files
                if ($diskName === 'local') {
                    $folder = $this->detectLocalBackupFolder();
                    $files = $disk->files($folder);
                } else {
                    $prefix = config('backup.name', env('APP_NAME', 'Laravel'));
                    $files = $disk->allFiles($prefix);
                }

                $match = collect($files)->first(fn($p) => basename($p) === (string)$id);
                if ($match && $disk->exists($match)) {
                    // stream file
                    $stream = $disk->readStream($match);
                    if ($stream === false) {
                        return abort(500, 'No se puede abrir el archivo para descarga.');
                    }
                    $basename = basename($match);

                    return response()->stream(function () use ($stream) {
                        fpassthru($stream);
                        if (is_resource($stream)) fclose($stream);
                    }, 200, [
                        'Content-Type' => 'application/zip',
                        'Content-Disposition' => 'attachment; filename="' . $basename . '"',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Error descargando backup (disk={$diskName}): " . $e->getMessage(), ['exception' => $e]);
            }
        }

        return abort(404, 'Backup no encontrado.');
    }

    /**
     * Mostrar log relacionado (intenta devolver el log más reciente)
     */
    public function log(Request $request, $id)
    {
        $this->authorize('admin.backups.log');

        // buscar logs que empiecen por backup- o backup-clean-
        $logFiles = collect(glob(storage_path('logs/backup-*.log')))->sortByDesc(fn($f) => filemtime($f))->values();

        if (! $logFiles->count()) {
            return response('Sin detalles', 200)->header('Content-Type', 'text/plain');
        }

        // devolver el log más reciente
        $content = file_get_contents($logFiles->first());

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Eliminar backup: buscar el archivo en el disco especificado o en ambos
     */
    public function destroy(Request $request, $id)
    {
        $this->authorize('admin.backups.destroy');

        $diskParam = $request->input('disk', null);
        $possibleDisks = $diskParam ? [$diskParam] : ['local', 's3'];

        foreach ($possibleDisks as $diskName) {
            try {
                if (! in_array($diskName, ['local', 's3'])) continue;
                $disk = Storage::disk($diskName);

                if ($diskName === 'local') {
                    $folder = $this->detectLocalBackupFolder();
                    $files = $disk->files($folder);
                } else {
                    $prefix = config('backup.name', env('APP_NAME', 'Laravel'));
                    $files = $disk->allFiles($prefix);
                }

                $match = collect($files)->first(fn($p) => basename($p) === (string)$id);
                if ($match && $disk->exists($match)) {
                    $disk->delete($match);
                    Log::info("Backup eliminado por user_id=" . optional($request->user())->id, ['file' => $match, 'disk' => $diskName]);
                    return response()->json(['ok' => true, 'message' => 'Backup eliminado', 'disk' => $diskName]);
                }
            } catch (\Throwable $e) {
                Log::error("Error al eliminar backup (disk={$diskName}): " . $e->getMessage(), ['exception' => $e]);
                return response()->json(['ok' => false, 'message' => 'Error al eliminar backup: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['ok' => false, 'message' => 'Backup no encontrado'], 404);
    }

    // helper
    private function humanFilesize($bytes, $decimals = 2)
    {
        if (! is_numeric($bytes)) return '-';
        $sz = ['B','KB','MB','GB','TB','PB'];
        $factor = floor((strlen((int)$bytes) - 1) / 3);
        if ($factor == 0) return $bytes . ' ' . $sz[0];
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sz[$factor];
    }
}
