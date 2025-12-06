<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RestoreBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $idName;
    public $disk;
    public $userId;
    public $logName;

    public $timeout = 3600;

    public function __construct(string $idName, string $disk = 'local', $userId = null, string $logName = null)
    {
        $this->idName = $idName;
        $this->disk = $disk;
        $this->userId = $userId;
        $this->logName = $logName ?: ('restore-job-' . uniqid() . '.log');
    }

    public function handle()
    {
        $logPath = storage_path('logs/' . $this->logName);
        
        // Crear el archivo de log inmediatamente
        file_put_contents($logPath, "[" . now() . "] Job iniciado\n", FILE_APPEND);
        
        // El trabajo real lo hace el comando artisan
        // Este job solo sirve para registrar que se inició
        Log::info("Restore job dispatched", [
            'file' => $this->idName,
            'disk' => $this->disk,
            'log_file' => $this->logName
        ]);
    }


    public function failed(Exception $exception)
    {
        Log::error('RestoreBackupJob falló completamente', [
            'file' => $this->idName,
            'disk' => $this->disk,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}