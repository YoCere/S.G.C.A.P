@extends('adminlte::page')

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
                        @if($lastBackup && $lastBackup->created_at)
                            {{ $lastBackup->created_at->format('d/m/Y H:i') }}
                        @else
                            <small class="text-light">Nunca</small>
                        @endif
                    </h3>
                    <p class="mb-0"><small class="text-light">Estado:
                        @if($lastBackup && $lastBackup->status == 'ok')
                            <span class="badge badge-light text-success">OK</span>
                        @elseif($lastBackup && $lastBackup->status == 'failed')
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
                        @if($lastBackup && is_numeric($lastBackup->size_in_mb)) {{ number_format($lastBackup->size_in_mb, 2) }} MB
                        @else <small class="text-muted">—</small> @endif
                    </h3>
                    <p class="mb-0"><small class="text-muted">Destino:
                        @if($lastBackup) {{ $lastBackup->disk ?? 'local' }} @else — @endif
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
                                    <td class="text-muted">#{{ $backup->id }}</td>
                                    <td>
                                        <strong>{{ optional($backup->created_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ optional($backup->created_at)->diffForHumans() ?? '' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ ucfirst($backup->type ?? 'completo') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ is_numeric($backup->size_in_mb) ? number_format($backup->size_in_mb, 2) . ' MB' : '-' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-pill badge-secondary">{{ $backup->disk ?? 'local' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($backup->status == 'ok')
                                            <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>OK</span>
                                        @elseif($backup->status == 'failed')
                                            <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>FALLÓ</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>----</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- Descarga: agregamos disk en query --}}
                                            <a href="{{ route('admin.backups.download', $backup->id) }}?disk={{ $backup->disk }}" class="btn btn-outline-primary" title="Descargar">
                                                <i class="fas fa-download"></i>
                                            </a>

                                            

                                            <button class="btn btn-outline-danger"
                                                    title="Eliminar"
                                                    onclick="confirmDelete('{{ $backup->id }}', '{{ $backup->disk }}')">
                                                <i class="fas fa-trash-alt"></i>
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
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseAdminBackupsUrl = "{{ route('admin.backups.index') }}".replace(/\/+$/, '');
    const runUrl = "{{ route('admin.backups.run') }}";
    const cleanUrl = "{{ route('admin.backups.clean') }}";

    function fetchJson(url, options = {}) {
        options.credentials = options.credentials || 'same-origin';
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
                const err = (json && json.message) ? json.message : response.status + ' ' + response.statusText + (text ? ' — ' + text : '');
                throw new Error(err);
            }
            return json ?? text;
        });
    }

    // Forzar backup
    const btnRun = document.getElementById('btn-run-backup');
    if (btnRun) {
        btnRun.addEventListener('click', function () {
            Swal.fire({
                title: 'Ejecutar backup ahora?',
                showCancelButton: true,
                confirmButtonText: 'Sí, iniciar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetchJson(runUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: {}
                    }).catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('OK', 'Backup iniciado.', 'success');
                    setTimeout(() => location.reload(), 1200);
                }
            });
        });
    }

    // Forzar limpieza
    const btnClean = document.getElementById('btn-clean-backups');
    if (btnClean) {
        btnClean.addEventListener('click', function () {
            Swal.fire({
                title: 'Ejecutar limpieza ahora?',
                showCancelButton: true,
                confirmButtonText: 'Sí, limpiar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetchJson(cleanUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: {}
                    }).catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Limpieza completada', '', 'success');
                    setTimeout(() => location.reload(), 1200);
                }
            });
        });
    }

    // Abrir modal con log (pasa disk opcional)
    window.openLogModal = function (id, disk = '') {
        let url = baseAdminBackupsUrl + '/' + encodeURIComponent(id) + '/log';
        if (disk) url += '?disk=' + encodeURIComponent(disk);
        fetch(url, { credentials: 'same-origin' })
            .then(async r => {
                if (!r.ok) {
                    const txt = await r.text().catch(()=>null);
                    throw new Error(txt || r.statusText);
                }
                return r.text();
            })
            .then(text => {
                document.getElementById('backup-log-content').textContent = text || 'Sin detalles';
                $('#backupLogModal').modal('show');
            })
            .catch(err => {
                console.error('Error al obtener log:', err);
                Swal.fire('Error', 'No se pudo obtener el log: ' + err.message, 'error');
            });
    };

    // Confirmar eliminación (envía disk en body)
    window.confirmDelete = function (id, disk = 'local') {
        Swal.fire({
            title: 'Eliminar backup?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
        }).then((result) => {
            if (result.isConfirmed) {
                const url = baseAdminBackupsUrl + '/' + encodeURIComponent(id);
                fetchJson(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: { disk: disk }
                }).then(json => {
                    Swal.fire('Eliminado', json?.message || 'Backup eliminado', 'success');
                    setTimeout(() => location.reload(), 900);
                }).catch(err => {
                    console.error('Error al eliminar:', err);
                    Swal.fire('Error', 'No se pudo eliminar: ' + err.message, 'error');
                });
            }
        });
    };

    // Filtro simple
    window.applyFilter = function () {
        const v = document.getElementById('filter-disk').value;
        const url = new URL(window.location.href);
        if (v) url.searchParams.set('disk', v); else url.searchParams.delete('disk');
        window.location.href = url.toString();
    };

    // Auto-hide alerts
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
});
</script>
@stop
