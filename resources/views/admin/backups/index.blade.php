@extends('layouts.admin-ultralight')

@section('title', 'Backups')

@section('content_header')
    <h1>Gestión de Backups</h1>
    <small class="text-muted">Control y descarga de copias de seguridad</small>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong><i class="fas fa-check-circle mr-1"></i>{{ session('info') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Top summary cards --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Último backup</h5>
                    <h3 class="card-text">
                        @if($lastBackup && isset($lastBackup->created_at))
                            {{ $lastBackup->created_at->format('d/m/Y H:i') }}
                        @else
                            <small class="text-light">Nunca</small>
                        @endif
                    </h3>
                    <p class="mb-0"><small class="text-light">Estado:
                        @if($lastBackup && isset($lastBackup->status) && $lastBackup->status == 'ok')
                            <span class="badge badge-light text-success">OK</span>
                        @elseif($lastBackup && isset($lastBackup->status) && $lastBackup->status == 'failed')
                            <span class="badge badge-light text-danger">Falló</span>
                        @else
                            <span class="badge badge-light text-warning">Sin datos</span>
                        @endif
                    </small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title">Tamaño último</h5>
                    <h3 class="card-text text-info">
                        @if($lastBackup && isset($lastBackup->size_in_mb) && is_numeric($lastBackup->size_in_mb)) 
                            {{ number_format($lastBackup->size_in_mb, 2) }} MB
                        @else 
                            <small class="text-muted">—</small> 
                        @endif
                    </h3>
                    <p class="mb-0"><small class="text-muted">Destino:
                        @if($lastBackup && isset($lastBackup->disk)) 
                            {{ $lastBackup->disk }} 
                        @else 
                            — 
                        @endif
                    </small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Retención</h5>
                    <h3 class="card-text text-primary">30 días</h3>
                    <p class="mb-0"><small class="text-muted">Política: diaria / semanal / mensual</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3 text-right">
            <div class="btn-group float-right" role="group" aria-label="Acciones backup">
                <button id="btn-run-backup" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-play-circle mr-1"></i>Forzar backup ahora
                </button>
                <button id="btn-clean-backups" class="btn btn-warning btn-sm">
                    <i class="fas fa-broom mr-1"></i>Limpiar (cleanup)
                </button>
            </div>
        </div>
    </div>

    {{-- Card with table --}}
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <div>
                <h3 class="card-title mb-0">Historial de Backups</h3>
                <small class="text-muted d-block">Lista de copias creadas por el sistema</small>
            </div>

            <div class="ml-auto">
                <form class="form-inline">
                    <label class="mr-2 mb-0 text-sm text-muted">Filtro:</label>
                    <select id="filter-disk" class="form-control form-control-sm" onchange="applyFilter()">
                        <option value="">Todos los destinos</option>
                        <option value="local" {{ request('disk') == 'local' ? 'selected' : '' }}>Local</option>
                        <option value="s3" {{ request('disk') == 's3' ? 'selected' : '' }}>S3</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if($backups->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="80">ID</th>
                                <th>Fecha / Hora</th>
                                <th>Tipo</th>
                                <th width="120">Tamaño</th>
                                <th width="140">Destino</th>
                                <th width="120" class="text-center">Estado</th>
                                <th width="200" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($backups as $backup)
                                <tr>
                                    <td class="text-muted">#{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ optional($backup->created_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ optional($backup->created_at)->diffForHumans() ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ ucfirst($backup->type ?? 'completo') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ isset($backup->size_in_mb) && is_numeric($backup->size_in_mb) ? number_format($backup->size_in_mb, 2) . ' MB' : '-' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-secondary">{{ $backup->disk ?? 'local' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if(isset($backup->status) && $backup->status == 'ok')
                                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>OK</span>
                                        @elseif(isset($backup->status) && $backup->status == 'failed')
                                            <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>FALLÓ</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>----</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Descargar --}}
                                            <a href="{{ route('admin.backups.download', basename($backup->path ?? $backup->id)) }}?disk={{ $backup->disk ?? 'local' }}" 
                                               class="btn btn-outline-primary" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </a>

                                            {{-- Restaurar --}}
                                            <button class="btn btn-outline-warning btn-restore" 
                                                    title="Restaurar" 
                                                    data-id="{{ basename($backup->path ?? $backup->id) }}" 
                                                    data-disk="{{ $backup->disk ?? 'local' }}">
                                                <i class="fas fa-undo-alt"></i>
                                            </button>

                                            {{-- Eliminar --}}
                                            <button class="btn btn-outline-danger btn-delete" 
                                                    title="Eliminar" 
                                                    data-id="{{ basename($backup->path ?? $backup->id) }}" 
                                                    data-disk="{{ $backup->disk ?? 'local' }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-hdd fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay backups registrados</h4>
                </div>
            @endif
        </div>

        @if($backups->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $backups->count() }} de {{ $backups->total() }} backups
                    </div>
                    @if($backups->hasPages())
                        {{ $backups->links() }}
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Modal log --}}
    <div class="modal fade" id="backupLogModal" tabindex="-1" role="dialog" aria-labelledby="backupLogModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalle del backup</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body pre-scrollable">
            <pre id="backup-log-content" class="small bg-light p-3" style="white-space:pre-wrap;"></pre>
          </div>
          <div class="modal-footer">
            
          </div>
        </div>
      </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .badge-pill {
            padding: 0.4em 0.6em;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
        .card .card-body h3 { margin: 0; }
        .btn-restore:hover {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-delete:hover {
            background-color: #dc3545;
            color: white;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

/* ============================================================
    FUNCIONES GLOBALES
   ============================================================ */

// fetchJson disponible globalmente (para confirmRestore)
window.fetchJson = function(url, options = {}) {
    options.credentials = 'same-origin';
    options.headers = options.headers || {};

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
        options.body = JSON.stringify(options.body);
        options.headers['Content-Type'] = 'application/json';
    }

    return fetch(url, options).then(async response => {
        const text = await response.text();
        let json;

        try { json = text ? JSON.parse(text) : null; } catch(e) { json = null; }

        if (!response.ok) {
            const errMsg = json?.message ?? `${response.status} ${response.statusText}`;
            throw new Error(errMsg);
        }

        return json ?? text;
    });
};


// LOG EN VIVO
let liveLogInterval = null;

// CONFIRMAR RESTORE - DEFINIDA GLOBALMENTE
window.confirmRestore = function(id, disk = 'local') {
    Swal.fire({
        title: '¿Restaurar este backup?',
        html: `
            <p>Esta acción reemplazará <strong>TODA</strong> la base de datos.</p>
            <p>Se generará un backup previo automáticamente.</p>
            <p><strong>Escribe <code>RESTORE</code> para confirmar:</strong></p>
            <input id="swal-input" class="swal2-input" placeholder="RESTORE">
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, restaurar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const val = document.getElementById("swal-input").value;
            if (val !== "RESTORE") {
                Swal.showValidationMessage("Debes escribir RESTORE para confirmar");
                return false;
            }

            return window.fetchJson("{{ url('admin/backups') }}/" + id + "/restore", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                    "Content-Type": "application/json"
                },
                body: { disk: disk }
            }).catch(e => Swal.showValidationMessage(e.message));
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(result => {
        if (result.isConfirmed && result.value?.log) {
            Swal.fire({
                icon: "success",
                title: "Restauración iniciada",
                html: `
                    <p>El proceso está ejecutándose en segundo plano.</p>
                    <button class="btn btn-primary btn-sm" onclick="openLiveRestoreLog('${result.value.log}')">
                        Ver progreso en vivo
                    </button>
                `,
                showConfirmButton: false,
                showCloseButton: true
            });
        }
    });
};

// LOG EN VIVO
window.openLiveRestoreLog = function(logFile) {
    const url = "{{ route('admin.backups.restore-log') }}" + '?file=' + encodeURIComponent(logFile);
    
    // Cerrar cualquier SweetAlert abierto
    if (typeof Swal !== 'undefined') Swal.close();
    
    // Abrir modal
    $('#backupLogModal').modal('show');
    const pre = document.getElementById('backup-log-content');
    pre.textContent = "Cargando log...";
    
    // Detener cualquier intervalo previo
    if (window.liveLogInterval) {
        clearInterval(window.liveLogInterval);
        window.liveLogInterval = null;
    }
    
    // Función para cargar el log - VERSIÓN MEJORADA CON MANEJO DE ERROR 401
    const loadLog = async () => {
        try {
            const response = await fetch(url, { 
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            // Manejar error 401 específicamente
            if (response.status === 401) {
                pre.textContent = "✅ ¡Restauración Completada con Éxito!\n\n";
                pre.textContent += "=".repeat(50) + "\n";
                pre.textContent += "🎉 ¡FELICITACIONES! 🎉\n";
                pre.textContent += "=".repeat(50) + "\n\n";
                pre.textContent += "La base de datos ha sido restaurada exitosamente.\n\n";
                pre.textContent += "📋 ¿Qué pasó?\n";
                pre.textContent += "• La restauración se completó correctamente\n";
                pre.textContent += "• La sesión actual expiró (ESTO ES NORMAL)\n";
                pre.textContent += "• Las credenciales se regeneraron\n\n";
                pre.textContent += "🔄 ¿Qué hacer ahora?\n";
                pre.textContent += "1. Cierra este modal\n";
                pre.textContent += "2. Recarga la página (F5 o botón de recargar)\n";
                pre.textContent += "3. Si te pide login, inicia sesión nuevamente\n\n";
                pre.textContent += "✅ ¡Todo está listo para continuar trabajando!\n\n";
                pre.textContent += "Este mensaje se cerrará en 15 segundos...";
                
                // Auto-cerrar modal después de 15 segundos
                setTimeout(() => {
                    if ($('#backupLogModal').is(':visible')) {
                        $('#backupLogModal').modal('hide');
                        
                        // Mostrar SweetAlert explicativo
                        Swal.fire({
                            title: '¡Restauración Exitosa! 🎉',
                            html: `
                                <div class="text-left">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Base de datos restaurada correctamente</strong>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Nota importante:</strong>
                                        <ul class="mb-0 mt-2 pl-3">
                                            <li>La sesión expiró (esto es normal)</li>
                                            <li>Necesitas recargar la página</li>
                                            <li>Si se solicita, inicia sesión nuevamente</li>
                                        </ul>
                                    </div>
                                    
                                    <p class="mt-3 mb-0">
                                        <small class="text-muted">
                                            <i class="fas fa-lightbulb mr-1"></i>
                                            Esto ocurre porque la restauración regenera las tablas de sesiones
                                        </small>
                                    </p>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: '¡Entendido! Recargaré la página',
                            confirmButtonColor: '#28a745',
                            allowOutsideClick: false,
                            willClose: () => {
                                // Sugerir recargar la página
                                Swal.fire({
                                    title: 'Recargar página',
                                    text: '¿Quieres recargar la página ahora?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, recargar',
                                    cancelButtonText: 'No, luego'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.reload();
                                    }
                                });
                            }
                        });
                    }
                }, 15000);
                
                return true; // Detener polling
            }
            
            // Manejar otros errores HTTP
            if (!response.ok) {
                if (response.status === 404) {
                    pre.textContent = "⏳ Esperando que el proceso inicie...\n";
                    pre.textContent += "Esto puede tardar unos segundos.\n\n";
                    pre.textContent += "⏱️ " + new Date().toLocaleTimeString();
                    return false; // Continuar polling
                }
                
                // Intentar obtener mensaje de error del JSON
                try {
                    const errorData = await response.json();
                    pre.textContent = "Error del servidor: " + (errorData.error || errorData.message || `HTTP ${response.status}`);
                } catch {
                    pre.textContent = "Error del servidor: HTTP " + response.status;
                }
                return true;
            }
            
            // Procesar respuesta exitosa
            const data = await response.json();
            
            if (data.error) {
                pre.textContent = "Error: " + data.error;
                if (data.tip) {
                    pre.textContent += "\n💡 " + data.tip;
                }
                return true;
            }
            
            // Mostrar contenido
            if (data.content) {
                pre.textContent = data.content;
                
                // Agregar información adicional si el proceso terminó
                if (data.completed || 
                    pre.textContent.includes("✅") || 
                    pre.textContent.includes("🎉") ||
                    pre.textContent.includes("RESTORE OK") ||
                    pre.textContent.includes("RESTAURACIÓN COMPLETADA")) {
                    
                    // Agregar mensaje final amigable
                    pre.textContent += "\n\n" + "=".repeat(50) + "\n";
                    pre.textContent += "✅ PROCESO FINALIZADO - ¡ÉXITO! ✅\n";
                    pre.textContent += "=".repeat(50) + "\n\n";
                    pre.textContent += "📌 Próximos pasos:\n";
                    pre.textContent += "1. Puedes cerrar esta ventana\n";
                    pre.textContent += "2. La base de datos ya está actualizada\n";
                    pre.textContent += "3. Si encuentras problemas de sesión, recarga la página\n\n";
                    pre.textContent += "🎯 ¡Listo para continuar!";
                    
                    return true; // Detener polling
                }
                
                if (pre.textContent.includes("❌ ERROR")) {
                    pre.textContent += "\n\n" + "=".repeat(50) + "\n";
                    pre.textContent += "❌ PROCESO FINALIZADO - CON ERRORES ❌\n";
                    pre.textContent += "=".repeat(50) + "\n\n";
                    pre.textContent += "⚠️  La restauración encontró problemas.\n";
                    pre.textContent += "💡 Revisa los logs detallados arriba.\n\n";
                    pre.textContent += "🛠️  Contacta con soporte si necesitas ayuda.";
                    
                    return true; // Detener polling
                }
                
            } else {
                // Si no hay contenido todavía
                pre.textContent = "⏳ Proceso de restauración en ejecución...\n";
                pre.textContent += "Esto puede tardar varios minutos.\n\n";
                pre.textContent += "📊 Información:\n";
                if (data.file_size) {
                    pre.textContent += "• Tamaño del log: " + (data.file_size / 1024).toFixed(2) + " KB\n";
                }
                if (data.last_modified) {
                    pre.textContent += "• Última actualización: " + data.last_modified + "\n";
                }
                pre.textContent += "\n⏱️ " + new Date().toLocaleTimeString();
            }
            
            return data.completed || false;
            
        } catch (error) {
            // Error de red o JavaScript
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                pre.textContent = "🌐 Error de conexión\n\n";
                pre.textContent += "No se puede conectar con el servidor.\n";
                pre.textContent += "Posibles causas:\n";
                pre.textContent += "• La sesión expiró (normal después de restore)\n";
                pre.textContent += "• Problemas de red temporales\n";
                pre.textContent += "• El servidor está reiniciando\n\n";
                pre.textContent += "💡 Intenta recargar la página en unos segundos.";
            } else {
                pre.textContent = "❌ Error inesperado: " + error.message;
            }
            
            return true; // Detener polling
        }
    };
    
    // Cargar inmediatamente
    loadLog().then(shouldStop => {
        if (!shouldStop) {
            // Iniciar polling cada 3 segundos
            window.liveLogInterval = setInterval(async () => {
                const shouldStop = await loadLog();
                if (shouldStop && window.liveLogInterval) {
                    clearInterval(window.liveLogInterval);
                    window.liveLogInterval = null;
                }
            }, 3000);
        }
    });
    
    // Limpiar intervalo cuando se cierre el modal
    $('#backupLogModal').on('hidden.bs.modal', () => {
        if (window.liveLogInterval) {
            clearInterval(window.liveLogInterval);
            window.liveLogInterval = null;
        }
    });
};


/* ============================================================
    FUNCIONES DEL SISTEMA DE BACKUPS (run, clean, logs, delete)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    const baseAdminBackupsUrl = "{{ route('admin.backups.index') }}".replace(/\/+$/, '');
    const runUrl   = "{{ route('admin.backups.run') }}";
    const cleanUrl = "{{ route('admin.backups.clean') }}";

    // ======================================================
    // MANEJADOR DE BOTONES DE RESTAURAR (event delegation)
    // ======================================================
    document.addEventListener('click', function(e) {
        // Botones de restaurar
        if (e.target.classList.contains('btn-restore') || e.target.closest('.btn-restore')) {
            const button = e.target.classList.contains('btn-restore') ? 
                          e.target : e.target.closest('.btn-restore');
            
            const id = button.dataset.id;
            const disk = button.dataset.disk || 'local';
            
            e.preventDefault();
            
            if (id && typeof window.confirmRestore === 'function') {
                window.confirmRestore(id, disk);
            }
        }
        
        // Botones de eliminar
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            const button = e.target.classList.contains('btn-delete') ? 
                          e.target : e.target.closest('.btn-delete');
            
            const id = button.dataset.id;
            const disk = button.dataset.disk || 'local';
            
            e.preventDefault();
            
            if (id && typeof window.confirmDelete === 'function') {
                window.confirmDelete(id, disk);
            }
        }
        
        // Botón "Ver progreso en vivo" dentro de SweetAlert
        if ((e.target.classList.contains('btn-primary') && 
            e.target.textContent.includes('Ver progreso')) ||
            e.target.closest('.btn-primary')) {
            // Este botón usa onclick="openLiveRestoreLog()" así que no necesita manejo aquí
        }
    });

    // Forzar backup - ESTO FALTABA EN TU CÓDIGO
    const btnRun = document.getElementById('btn-run-backup');
    if (btnRun) {
        btnRun.addEventListener('click', function () {
            Swal.fire({
                title: '¿Ejecutar backup ahora?',
                text: 'Se creará un nuevo backup completo del sistema.',
                showCancelButton: true,
                confirmButtonText: 'Sí, iniciar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return window.fetchJson(runUrl, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                }
            }).then(r => {
                if (r.isConfirmed) {
                    Swal.fire({
                        title: "Backup iniciado",
                        text: "El proceso se está ejecutando en segundo plano.",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(() => location.reload(), 2500);
                }
            });
        });
    }

    // Forzar limpieza
    const btnClean = document.getElementById('btn-clean-backups');
    if (btnClean) {
        btnClean.addEventListener('click', function () {
            Swal.fire({
                title: '¿Eliminar backups viejos?',
                text: 'Se aplicarán las políticas de retención configuradas.',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return window.fetchJson(cleanUrl, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                }
            }).then(r => {
                if (r.isConfirmed) {
                    Swal.fire({
                        title: "Limpieza realizada",
                        text: "Los backups antiguos han sido eliminados.",
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(() => location.reload(), 2500);
                }
            });
        });
    }

    // Ver log tradicional
    window.openLogModal = function(id, disk = '') {
        let url = baseAdminBackupsUrl + '/' + id + '/log';
        if (disk) url += '?disk=' + encodeURIComponent(disk);

        fetch(url)
            .then(r => r.text())
            .then(text => {
                document.getElementById('backup-log-content').textContent = text;
                $('#backupLogModal').modal('show');
            });
    };

    // Eliminar backup - FUNCIÓN GLOBAL
    window.confirmDelete = function(id, disk = 'local') {
        Swal.fire({
            title: '¿Eliminar backup?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return window.fetchJson(baseAdminBackupsUrl + '/' + id, {
                    method: "DELETE",
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ disk: disk })
                });
            }
        }).then(res => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: "Eliminado",
                    text: "Backup eliminado correctamente.",
                    icon: "success",
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(() => location.reload(), 2000);
            }
        });
    };

    // Filtro por disco
    window.applyFilter = function() {
        const v = document.getElementById('filter-disk').value;
        const url = new URL(window.location.href);
        if (v) url.searchParams.set('disk', v);
        else   url.searchParams.delete('disk');

        window.location.href = url.toString();
    };

    // Auto cerrar alertas
    setTimeout(() => { 
        $('.alert').alert('close'); 
    }, 4000);

});
</script>
@stop