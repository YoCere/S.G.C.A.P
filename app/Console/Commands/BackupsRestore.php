<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;

class BackupsRestore extends Command
{
    protected $signature = 'backups:restore {id} {--disk=local}';
    protected $description = 'Restaurar un backup desde un zip. Uso: php artisan backups:restore archivo.zip --disk=s3';

    public function handle()
    {
        $id = $this->argument('id');
        $diskName = $this->option('disk') ?: 'local';

        $this->info("=== INICIANDO RESTAURACIÓN ===");
        $this->info("Archivo: {$id}");
        $this->info("Disco: {$diskName}");
        $this->info("Sistema: " . PHP_OS);
        $this->info("PHP Version: " . PHP_VERSION);
        $this->info("Tiempo: " . now()->format('Y-m-d H:i:s'));

        $start = now()->format('Ymd-His');
        $logFile = storage_path("logs/restore-cli-{$start}.log");
        file_put_contents($logFile, "=== RESTORE LOG {$start} ===\n", FILE_APPEND);
        file_put_contents($logFile, "[".now()."] START RESTORE {$id} (disk={$diskName})\n", FILE_APPEND);
        file_put_contents($logFile, "[".now()."] Sistema: " . PHP_OS . ", PHP: " . PHP_VERSION . "\n", FILE_APPEND);

        try {
            $disk = Storage::disk($diskName);

            // Buscar archivo ZIP
            $this->info("Buscando el archivo en el disco...");
            $prefix = config('backup.backup.name', env('APP_NAME', 'Laravel'));
            $files = [];

            try {
                $files = $disk->allFiles($prefix);
            } catch (\Throwable $e) {
                try { 
                    $files = $disk->allFiles(''); 
                } catch (\Throwable $e2) { 
                    $files = []; 
                }
            }

            file_put_contents($logFile, "[".now()."] Archivos encontrados: " . count($files) . "\n", FILE_APPEND);
            
            $match = collect($files)->first(fn($p) => basename($p) === $id);

            if (!$match) {
                $errorMsg = "No se encontró el archivo {$id} en {$diskName}";
                $this->error($errorMsg);
                file_put_contents($logFile, "[".now()."] ERROR: {$errorMsg}\n", FILE_APPEND);
                return 1;
            }

            $this->info("Archivo encontrado: {$match}");
            file_put_contents($logFile, "[".now()."] Archivo encontrado: {$match}\n", FILE_APPEND);

            // Descargar ZIP
            $zipPath = storage_path("app/restore-temp-{$start}.zip");
            $this->info("Descargando ZIP a: {$zipPath}");
            file_put_contents($logFile, "[".now()."] Descargando a: {$zipPath}\n", FILE_APPEND);

            // Asegurar directorio
            $zipDir = dirname($zipPath);
            if (!is_dir($zipDir)) {
                mkdir($zipDir, 0755, true);
            }

            $stream = $disk->readStream($match);
            if (!$stream) {
                throw new Exception("No se pudo abrir el stream del archivo {$match}");
            }

            $out = fopen($zipPath, 'w');
            if (!$out) {
                fclose($stream);
                throw new Exception("No se pudo crear el archivo local {$zipPath}");
            }

            $bytesCopied = stream_copy_to_stream($stream, $out);
            fclose($out);
            fclose($stream);

            if ($bytesCopied === false) {
                throw new Exception("Error al copiar el archivo desde {$diskName}");
            }

            $this->info("Descargado: " . number_format($bytesCopied) . " bytes");
            file_put_contents($logFile, "[".now()."] Descargado: " . number_format($bytesCopied) . " bytes\n", FILE_APPEND);

            // Abrir ZIP
            $this->info("Abriendo archivo ZIP...");
            $zip = new ZipArchive();
            $zipStatus = $zip->open($zipPath);
            if ($zipStatus !== true) {
                throw new Exception("No se pudo abrir el zip {$zipPath}. Código error: {$zipStatus}");
            }

            $this->info("ZIP abierto. Archivos dentro: " . $zip->numFiles);
            file_put_contents($logFile, "[".now()."] ZIP abierto. Archivos: " . $zip->numFiles . "\n", FILE_APPEND);

            // Buscar archivo SQL dentro del ZIP
            $sqlEntry = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (Str::endsWith($name, '.sql') || Str::endsWith($name, '.sql.gz')) {
                    $sqlEntry = $name;
                    break;
                }
            }

            if (!$sqlEntry) {
                throw new Exception("No se encontró archivo SQL dentro del backup ZIP");
            }

            $this->info("Archivo SQL encontrado: {$sqlEntry}");
            file_put_contents($logFile, "[".now()."] SQL encontrado: {$sqlEntry}\n", FILE_APPEND);

            // Extraer SQL
            $sqlPath = storage_path("app/restore-sql-" . uniqid() . '-' . basename($sqlEntry));
            $this->info("Extrayendo SQL a: {$sqlPath}");
            file_put_contents($logFile, "[".now()."] Extrayendo a: {$sqlPath}\n", FILE_APPEND);

            $fp = $zip->getStream($sqlEntry);
            if (!$fp) {
                $zip->close();
                throw new Exception("No se pudo abrir el archivo SQL dentro del ZIP");
            }

            $out = fopen($sqlPath, 'w');
            if (!$out) {
                $zip->close();
                throw new Exception("No se pudo crear el archivo SQL {$sqlPath}");
            }

            $bytesCopied = 0;
            while (!feof($fp)) {
                $bytesCopied += fwrite($out, fread($fp, 1024 * 1024));
            }

            fclose($fp);
            fclose($out);
            $zip->close();

            $this->info("SQL extraído: " . number_format($bytesCopied) . " bytes");
            file_put_contents($logFile, "[".now()."] SQL extraído: " . number_format($bytesCopied) . " bytes\n", FILE_APPEND);

            // Si es .gz, descomprimir
            if (Str::endsWith($sqlPath, '.gz')) {
                $this->info("Descomprimiendo archivo .gz...");
                file_put_contents($logFile, "[".now()."] Descomprimiendo .gz...\n", FILE_APPEND);
                
                $decoded = gzdecode(file_get_contents($sqlPath));
                if ($decoded === false) {
                    throw new Exception("Error al descomprimir archivo .gz");
                }
                
                $newPath = substr($sqlPath, 0, -3);
                file_put_contents($newPath, $decoded);
                unlink($sqlPath);
                $sqlPath = $newPath;
                
                $this->info("Descompresión completada: {$sqlPath}");
                file_put_contents($logFile, "[".now()."] Descompresión OK: {$sqlPath}\n", FILE_APPEND);
            }

            // Preparar variables DB
            $isWin = strtoupper(substr(PHP_OS, 0, 3)) === "WIN";
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            $this->info("Configuración DB:");
            $this->info("- Host: {$dbHost}");
            $this->info("- Puerto: {$dbPort}");
            $this->info("- Base: {$dbName}");
            $this->info("- Usuario: {$dbUser}");
            $this->info("- Pass: " . ($dbPass ? '***' : '(vacía)'));
            file_put_contents($logFile, "[".now()."] DB Config: {$dbHost}:{$dbPort}, {$dbName}, user:{$dbUser}\n", FILE_APPEND);

            // Detectar mysql CLI
            $mysqlPath = null;
            if ($isWin) {
                // Windows - buscar mysql en ubicaciones comunes de XAMPP
                $possiblePaths = [
                    'mysql', // Si está en PATH
                    'C:\\xampp\\mysql\\bin\\mysql.exe',
                    'D:\\xampp\\mysql\\bin\\mysql.exe',
                    'C:\\Program Files\\xampp\\mysql\\bin\\mysql.exe',
                    'D:\\PROGRAMAS\\xamp\\mysql\\bin\\mysql.exe', // Tu ruta específica
                ];
                
                foreach ($possiblePaths as $path) {
                    if ($path === 'mysql') {
                        $output = shell_exec('where mysql 2>nul');
                        if ($output) {
                            $mysqlPath = trim(explode("\n", $output)[0]);
                            break;
                        }
                    } elseif (file_exists($path)) {
                        $mysqlPath = $path;
                        break;
                    }
                }
            } else {
                // Linux/Unix
                $output = shell_exec('which mysql');
                $mysqlPath = $output ? trim(explode("\n", $output)[0]) : null;
            }

            if ($mysqlPath) {
                $this->info("mysql CLI encontrado: {$mysqlPath}");
                file_put_contents($logFile, "[".now()."] mysql encontrado: {$mysqlPath}\n", FILE_APPEND);
            } else {
                $this->warn("mysql CLI no encontrado, usando fallback...");
                file_put_contents($logFile, "[".now()."] mysql NO encontrado, usando fallback\n", FILE_APPEND);
            }

            $restored = false;

            // Intentar con CLI mysql si está disponible
            if ($mysqlPath && filesize($sqlPath) < 50000000) { // Solo si es menor a 50MB
                $this->info("Intentando restauración rápida (CLI)...");
                file_put_contents($logFile, "[".now()."] Intentando CLI...\n", FILE_APPEND);

                if ($isWin) {
                    // Windows - Usar escapeshellarg para rutas con espacios
                    $sqlPathEscaped = escapeshellarg($sqlPath);
                    
                    if ($dbPass === null || $dbPass === '') {
                        // SIN contraseña
                        $cmd = "\"{$mysqlPath}\" -h{$dbHost} -P{$dbPort} -u{$dbUser} {$dbName} < {$sqlPathEscaped}";
                        $cmd = "cmd /c \"{$cmd}\"";
                    } else {
                        // Con contraseña - usar variable de entorno
                        $escapedPass = escapeshellarg($dbPass);
                        $envCmd = "set MYSQL_PWD={$escapedPass} && ";
                        $cmd = "\"{$mysqlPath}\" -h{$dbHost} -P{$dbPort} -u{$dbUser} {$dbName} < {$sqlPathEscaped}";
                        $cmd = "cmd /c \"{$envCmd}{$cmd}\"";
                    }
                } else {
                    // Linux/Unix
                    $sqlPathEscaped = escapeshellarg($sqlPath);
                    
                    if ($dbPass === null || $dbPass === '') {
                        $cmd = "{$mysqlPath} -h{$dbHost} -P{$dbPort} -u{$dbUser} {$dbName} < {$sqlPathEscaped} 2>&1";
                    } else {
                        $escapedPass = escapeshellarg($dbPass);
                        $cmd = "MYSQL_PWD={$escapedPass} {$mysqlPath} -h{$dbHost} -P{$dbPort} -u{$dbUser} {$dbName} < {$sqlPathEscaped} 2>&1";
                    }
                }

                $this->info("Comando CLI: " . substr($cmd, 0, 200) . "...");
                file_put_contents($logFile, "[".now()."] Comando CLI: " . $cmd . "\n", FILE_APPEND);
                
                exec($cmd, $output, $return);
                
                file_put_contents($logFile, "[".now()."] Exit code CLI: {$return}\n", FILE_APPEND);
                if ($output) {
                    file_put_contents($logFile, "[".now()."] Output CLI: " . implode("\n", $output) . "\n", FILE_APPEND);
                }

                if ($return === 0) {
                    $restored = true;
                    $this->info("✅ Restauración completada correctamente (CLI)");
                    file_put_contents($logFile, "[".now()."] ✅ RESTORE OK (CLI)\n", FILE_APPEND);
                } else {
                    $this->warn("CLI falló con código {$return}");
                }
            }

            // Si CLI falló o no está disponible → fallback DB::unprepared
            if (!$restored) {
                $this->info("Usando fallback DB::unprepared...");
                file_put_contents($logFile, "[".now()."] Usando fallback...\n", FILE_APPEND);

                try {
                    // Leer archivo SQL en partes para no sobrecargar memoria
                    $fileSize = filesize($sqlPath);
                    $this->info("Tamaño del archivo SQL: " . number_format($fileSize) . " bytes");
                    
                    if ($fileSize > 100000000) { // > 100MB
                        $this->warn("Archivo SQL muy grande. Considera usar CLI mysql para mejor rendimiento.");
                    }
                    
                    $sql = file_get_contents($sqlPath);
                    
                    // Ejecutar SQL
                    DB::unprepared($sql);
                    
                    $restored = true;
                    $this->info("✅ Restauración completada (fallback)");
                    file_put_contents($logFile, "[".now()."] ✅ RESTORE OK (fallback)\n", FILE_APPEND);
                } catch (\Exception $e) {
                    $this->error("Error en fallback: " . $e->getMessage());
                    file_put_contents($logFile, "[".now()."] ERROR fallback: " . $e->getMessage() . "\n", FILE_APPEND);
                    throw $e;
                }
            }

            // Limpiar archivos temporales
            if (file_exists($sqlPath)) {
                unlink($sqlPath);
            }
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            $this->info("Archivos temporales eliminados");
            file_put_contents($logFile, "[".now()."] Archivos temporales eliminados\n", FILE_APPEND);

            if ($restored) {
                $this->info("🎉 RESTAURACIÓN COMPLETADA CON ÉXITO");
                file_put_contents($logFile, "[".now()."] 🎉 RESTAURACIÓN COMPLETADA CON ÉXITO\n", FILE_APPEND);
                return 0;
            }

            throw new Exception("Fallo inesperado al restaurar");

        } catch (Exception $e) {
            $this->error("❌ ERROR: " . $e->getMessage());
            $this->error("Traza: " . $e->getTraceAsString());
            file_put_contents($logFile, "[".now()."] ❌ ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($logFile, "[".now()."] TRAZA: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            return 1;
        }
    }
}