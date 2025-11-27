{{-- resources/views/admin/properties/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Propiedades')

@section('content_header')
  <h1>Lista de Propiedades</h1>
@stop

@section('content')
  @if (session('info'))
    <div class="alert alert-success alert-dismissible fade show">
      <strong>{{ session('info') }}</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      <strong>{{ session('error') }}</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <!-- FILTROS AVANZADOS CON CÓDIGO CLIENTE -->
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
      </h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.properties.index') }}" method="GET" id="filterForm">
        <div class="row g-2">
          <!-- Búsqueda general -->
          <div class="col-12 col-sm-6 col-md-4">
            <div class="form-group mb-2">
              <label for="search" class="small font-weight-bold">Búsqueda General</label>
              <input type="text" name="search" id="search" class="form-control form-control-sm" 
                     placeholder="Referencia, cliente, cédula, código..." 
                     value="{{ request('search') }}">
            </div>
          </div>

          <!-- ✅ NUEVO: FILTRO POR CÓDIGO CLIENTE -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="form-group mb-2">
              <label for="codigo_cliente" class="small font-weight-bold">Código Cliente</label>
              <input type="text" name="codigo_cliente" id="codigo_cliente" class="form-control form-control-sm" 
                     placeholder="Ej: 48372" value="{{ request('codigo_cliente') }}">
            </div>
          </div>

          <!-- 🆕 ACTUALIZADO: Filtro por Estado incluye pendiente_conexion -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="form-group mb-2">
              <label for="estado" class="small font-weight-bold">Estado</label>
              <select name="estado" id="estado" class="form-control form-control-sm">
                <option value="">Todos</option>
                <option value="pendiente_conexion" {{ request('estado') == 'pendiente_conexion' ? 'selected' : '' }}>Pendiente Conexión</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="corte_pendiente" {{ request('estado') == 'corte_pendiente' ? 'selected' : '' }}>Corte Pendiente</option>
                <option value="cortado" {{ request('estado') == 'cortado' ? 'selected' : '' }}>Cortado</option>
                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
              </select>
            </div>
          </div>

          <!-- Filtro por Barrio -->
          <div class="col-12 col-sm-6 col-md-2">
            <div class="form-group mb-2">
              <label for="barrio" class="small font-weight-bold">Barrio</label>
              <select name="barrio" id="barrio" class="form-control form-control-sm">
                <option value="">Todos</option>
                <option value="Centro" {{ request('barrio') == 'Centro' ? 'selected' : '' }}>Centro</option>
                <option value="Aroma" {{ request('barrio') == 'Aroma' ? 'selected' : '' }}>Aroma</option>
                <option value="Los Valles" {{ request('barrio') == 'Los Valles' ? 'selected' : '' }}>Los Valles</option>
                <option value="Caipitandy" {{ request('barrio') == 'Caipitandy' ? 'selected' : '' }}>Caipitandy</option>
                <option value="Primavera" {{ request('barrio') == 'Primavera' ? 'selected' : '' }}>Primavera</option>
                <option value="Arboleda" {{ request('barrio') == 'Arboleda' ? 'selected' : '' }}>Arboleda</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row g-2">
          <!-- Filtro por Tarifa -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="form-group mb-2">
              <label for="tarifa_id" class="small font-weight-bold">Tarifa</label>
              <select name="tarifa_id" id="tarifa_id" class="form-control form-control-sm">
                <option value="">Todas</option>
                @foreach($tariffs as $tarifa)
                  <option value="{{ $tarifa->id }}" {{ request('tarifa_id') == $tarifa->id ? 'selected' : '' }}>
                    {{ $tarifa->nombre }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Ordenamiento -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="form-group mb-2">
              <label for="orden" class="small font-weight-bold">Ordenar por</label>
              <select name="orden" id="orden" class="form-control form-control-sm">
                <option value="reciente" {{ request('orden') == 'reciente' ? 'selected' : '' }}>Más recientes</option>
                <option value="antiguo" {{ request('orden') == 'antiguo' ? 'selected' : '' }}>Más antiguos</option>
                <option value="referencia" {{ request('orden') == 'referencia' ? 'selected' : '' }}>Referencia (A-Z)</option>
                <option value="cliente" {{ request('orden') == 'cliente' ? 'selected' : '' }}>Cliente (A-Z)</option>
                <option value="barrio" {{ request('orden') == 'barrio' ? 'selected' : '' }}>Barrio</option>
              </select>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="col-12 col-sm-12 col-md-6">
            <div class="d-flex flex-wrap gap-2 mt-4">
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search mr-1"></i> Aplicar Filtros
              </button>
              <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-undo mr-1"></i> Limpiar
              </a>
              
              @if(request()->anyFilled(['search', 'codigo_cliente', 'estado', 'barrio', 'tarifa_id', 'orden']))
                <span class="badge badge-success align-self-center">
                  <i class="fas fa-filter mr-1"></i>Filtros activos
                </span>
              @endif
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

 <!-- 🆕 ACTUALIZADO: Estadísticas incluyen pendientes_conexion -->
<div class="row mx-0 mb-4">
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-info"><i class="fas fa-home"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Total</span>
        <span class="info-box-number">{{ $totalPropiedades }}</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Pend. Conexión</span>
        <span class="info-box-number">{{ $estadisticas['pendientes_conexion'] ?? 0 }}</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Activas</span>
        <span class="info-box-number">{{ $estadisticas['activas'] ?? 0 }}</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Corte Pendiente</span>
        <span class="info-box-number">{{ $estadisticas['corte_pendiente'] ?? 0 }}</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Cortadas</span>
        <span class="info-box-number">{{ $estadisticas['cortadas'] ?? 0 }}</span>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-md-2 mb-3">
    <div class="info-box bg-light shadow-sm border">
      <span class="info-box-icon bg-secondary"><i class="fas fa-map-marker-alt"></i></span>
      <div class="info-box-content">
        <span class="info-box-text small">Con Ubicación</span>
        <span class="info-box-number">{{ $estadisticas['con_ubicacion'] ?? 0 }}</span>
      </div>
    </div>
  </div>
</div>

  <!-- BOTONES PRINCIPALES -->
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.create') }}">
        <i class="fas fa-plus-circle mr-1"></i>Nueva Instalacion
      </a>
      <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-warning btn-sm">
        <i class="fas fa-clock mr-1"></i>Ver Cortes Pendientes
      </a>
    </div>
    <div class="text-muted small">
      Mostrando {{ $properties->count() }} de {{ $properties->total() }} propiedades
    </div>
  </div>

  <!-- VISTA ESCRITORIO -->
<div class="d-none d-md-block">
  <div class="card">
      <div class="card-body p-0">
          @if($properties->count())
              <div class="table-responsive">
                  <table class="table table-hover table-striped mb-0">
                      <thead class="thead-light">
                          <tr>
                              <th width="50">#</th>
                              <th>Cliente</th>
                              <th>Referencia</th>
                              <th>Barrio</th>
                              <th>Tarifa</th>
                              <th width="100">Precio</th>
                              <th width="120">Estado</th>
                              <th width="350" class="text-center">Acciones</th> <!-- ✅ Aumentado ancho -->
                          </tr>
                      </thead>
                      <tbody>
                          @foreach($properties as $p)
                              <tr>
                                  <td class="text-muted">{{ $p->id }}</td>
                                  <td>
                                      <div class="d-flex flex-column">
                                          <strong class="text-primary">{{ $p->client->nombre ?? 'N/A' }}</strong>
                                          <small class="text-muted">
                                              <i class="fas fa-id-card mr-1"></i>{{ $p->client->ci ?? 'Sin CI' }}
                                          </small>
                                          <small class="text-success font-weight-bold">
                                              <i class="fas fa-barcode mr-1"></i>{{ $p->client->codigo_cliente ?? 'N/A' }}
                                          </small>
                                          <small class="text-{{ $p->client->estado_cuenta == 'activo' ? 'success' : 'warning' }} small">
                                              {{ $p->client->estado_cuenta ?? 'N/A' }}
                                          </small>
                                      </div>
                                  </td>
                                  <td>
                                      <strong>{{ $p->referencia }}</strong>
                                      @if($p->estado === 'cortado')
                                          <span class="badge badge-danger ml-1">CORTADO</span>
                                      @elseif($p->estado === 'corte_pendiente')
                                          <span class="badge badge-warning ml-1">PENDIENTE</span>
                                      @endif
                                  </td>
                                  <td>
                                      @if($p->barrio)
                                          <span class="badge badge-light border">{{ $p->barrio }}</span>
                                      @else
                                          <span class="text-muted small">—</span>
                                      @endif
                                  </td>
                                  <td>
                                      <div class="d-flex align-items-center">
                                          <span>{{ $p->tariff->nombre ?? '—' }}</span>
                                          @if($p->tariff && !$p->tariff->activo)
                                              <span class="badge badge-warning ml-1 small" title="Tarifa inactiva">INACTIVA</span>
                                          @endif
                                      </div>
                                  </td>
                                  <td>
                                      <strong class="text-success">Bs {{ number_format($p->tariff->precio_mensual ?? 0, 2) }}</strong>
                                  </td>
                                  <td>
                                      @if($p->estado === 'pendiente_conexion')
                                          <span class="badge badge-primary">Pendiente Conexión</span>
                                      @elseif($p->estado === 'activo')
                                          <span class="badge badge-success">Activo</span>
                                      @elseif($p->estado === 'corte_pendiente')
                                          {{-- 🆕 MOSTRAR EL TIPO DE TRABAJO PENDIENTE --}}
                                          @if($p->tipo_trabajo_pendiente === 'reconexion')
                                              <span class="badge badge-info">Reconexión Pendiente</span>
                                          @elseif($p->tipo_trabajo_pendiente === 'corte_mora')
                                              <span class="badge badge-warning">Corte Pendiente</span>
                                          @else
                                              <span class="badge badge-warning">Corte Pendiente</span>
                                          @endif
                                      @elseif($p->estado === 'cortado')
                                          <span class="badge badge-danger">Cortado</span>
                                      @else
                                          <span class="badge badge-secondary">Inactivo</span>
                                      @endif
                                  </td>
                                  <td>
                                      <div class="d-flex flex-wrap gap-1 justify-content-center">
                                          {{-- Ubicación --}}
                                          @if($p->latitud && $p->longitud)
                                              <button class="btn btn-info btn-sm"
                                                      data-toggle="modal"
                                                      data-target="#mapModal"
                                                      data-lat="{{ $p->latitud }}"
                                                      data-lng="{{ $p->longitud }}"
                                                      data-ref="{{ $p->referencia }}"
                                                      data-id="{{ $p->id }}"
                                                      data-id="{{ $p->client->codigo_cliente }}">
                                                  <i class="fas fa-map-marker-alt"></i>
                                              </button>
                                          @else
                                              <button class="btn btn-outline-secondary btn-sm" disabled title="Sin ubicación">
                                                  <i class="fas fa-map-marker-alt"></i>
                                              </button>
                                          @endif

                                          {{-- Ver Detalle --}}
                                          <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.properties.show', $p) }}" title="Ver detalle">
                                              <i class="fas fa-eye"></i>
                                          </a>

                                          {{-- Editar --}}
                                          <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.edit', $p) }}" title="Editar">
                                              <i class="fas fa-edit"></i>
                                          </a>

                                          {{-- 🆕 ACTUALIZADO: Botones según estado y rol --}}
                                          @php
                                              $user = auth()->user();
                                              $isAdmin = $user->hasRole('Admin');
                                              $isSecretaria = $user->hasRole('Secretaria');
                                              $isOperador = $user->hasRole('Operador');
                                          @endphp

                                          {{-- PROPIEDADES ACTIVAS --}}
                                          @if($p->estado === 'activo')
                                              {{-- Secretaria y Admin pueden solicitar corte --}}
                                              @if($isAdmin || $isSecretaria)
                                                  <form action="{{ route('admin.properties.cut', $p) }}" method="POST" class="d-inline">
                                                      @csrf @method('PUT')
                                                      <button class="btn btn-warning btn-sm" type="button" 
                                                              onclick="confirmCutService({{ $p->id }}, '{{ $p->referencia }}')" title="Solicitar corte">
                                                          <i class="fas fa-clock"></i>
                                                      </button>
                                                  </form>
                                              @endif
                                          {{-- PROPIEDADES PENDIENTES DE CONEXIÓN --}}
                                          @elseif($p->estado === 'pendiente_conexion')
                                              {{-- Operador y Admin pueden marcar como instalación completada --}}
                                              @if($isAdmin || $isOperador)
                                                  <form action="{{ route('admin.cortes.marcar-cortado', $p) }}" method="POST" class="d-inline">
                                                      @csrf
                                                      <button class="btn btn-danger btn-sm" type="button" 
                                                              onclick="confirmMarkAsCut({{ $p->id }}, '{{ $p->referencia }}')" title="Marcar instalación completada">
                                                          <i class="fas fa-check-circle"></i>
                                                      </button>
                                                  </form>
                                              @endif
                                          {{-- CORTES PENDIENTES --}}
                                          @elseif($p->estado === 'corte_pendiente')
                                              {{-- Operador y Admin pueden ejecutar corte físico --}}
                                              @if($isAdmin || $isOperador)
                                                  <form action="{{ route('admin.cortes.marcar-cortado', $p) }}" method="POST" class="d-inline">
                                                      @csrf
                                                      <button class="btn btn-danger btn-sm" type="button" 
                                                              onclick="confirmMarkAsCut({{ $p->id }}, '{{ $p->referencia }}')" title="Ejecutar corte físico">
                                                          <i class="fas fa-ban"></i>
                                                      </button>
                                                  </form>
                                              @endif
                                              
                                              {{-- ✅ NUEVO: Botón especial para forzar reconexión cuando hay pagos --}}
                                                @if($p->tipo_trabajo_pendiente === 'reconexion' && ($isAdmin || $isOperador))
                                                <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-success btn-sm" type="button"
                                                            onclick="confirmForceReconnection({{ $p->id }}, '{{ $p->referencia }}')" 
                                                            title="Forzar reconexión (cliente ya pagó)">
                                                        <i class="fas fa-bolt"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Botón para cancelar --}}
                                            @if($isAdmin)
                                                <form action="{{ route('admin.properties.cancel-cut', $p) }}" method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-secondary btn-sm" type="button" 
                                                            onclick="confirmCancelAction({{ $p->id }}, '{{ $p->referencia }}', '{{ $p->tipo_trabajo_pendiente }}')" 
                                                            title="{{ ucfirst($p->texto_accion_cancelar) }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                              
                                              {{-- Admin puede reconectar directamente --}}
                                              @if($isAdmin)
                                                  <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline">
                                                      @csrf @method('PUT')
                                                      <button class="btn btn-success btn-sm" type="button"
                                                              onclick="confirmRestoreService({{ $p->id }}, '{{ $p->referencia }}')" title="Reconectar servicio (Admin)">
                                                          <i class="fas fa-bolt"></i>
                                                      </button>
                                                  </form>
                                              @endif
                                          {{-- PROPIEDADES CORTADAS --}}
                                          {{-- ✅ REEMPLAZAR esta sección --}}
                                          @elseif($p->estado === 'cortado')
                                          @if($isAdmin || $isSecretaria)
                                              @php
                                                  $mesesAdeudados = $p->obtenerMesesAdeudados();
                                                  $mesesMora = count($mesesAdeudados);
                                              @endphp
                                              @if($mesesMora > 0)
                                                  <form action="{{ route('admin.properties.request-reconnection', $p) }}" method="POST" class="d-inline">
                                                      @csrf @method('PUT')
                                                      <button class="btn btn-success btn-sm" type="submit" 
                                                              title="Pagar {{ $mesesMora }} meses + multa y reconectar">
                                                          <i class="fas fa-plug"></i>
                                                      </button>
                                                  </form>
                                              @else
                                                  <button class="btn btn-outline-secondary btn-sm" disabled title="Sin deudas pendientes">
                                                      <i class="fas fa-plug"></i>
                                                  </button>
                                              @endif
                                          @endif

                                          @if($isAdmin)
                                              <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline">
                                                  @csrf @method('PUT')
                                                  <button class="btn btn-success btn-sm" type="button"
                                                          onclick="confirmRestoreService({{ $p->id }}, '{{ $p->referencia }}')" title="Reconectar servicio (Admin)">
                                                      <i class="fas fa-bolt"></i>
                                                  </button>
                                              </form>
                                          @endif
                                          @endif

                                      </div>
                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>
          @else
              <div class="text-center py-5">
                  <i class="fas fa-home fa-3x text-muted mb-3"></i>
                  <h4 class="text-muted">
                      @if(request()->anyFilled(['search', 'codigo_cliente', 'estado', 'barrio', 'tarifa_id']))
                          No se encontraron propiedades con los filtros aplicados
                      @else
                          No hay propiedades registradas
                      @endif
                  </h4>
                  @if(!request()->anyFilled(['search', 'codigo_cliente', 'estado', 'barrio', 'tarifa_id']))
                      <a href="{{ route('admin.properties.create') }}" class="btn btn-primary mt-2">
                          <i class="fas fa-plus-circle mr-1"></i>Crear Primera Propiedad
                      </a>
                  @else
                      <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary mt-2">
                          <i class="fas fa-undo mr-1"></i>Limpiar Filtros
                      </a>
                  @endif
              </div>
          @endif
      </div>

      @if($properties->count())
          <div class="card-footer">
              <div class="d-flex justify-content-between align-items-center">
                  <div class="text-muted small">
                      Mostrando {{ $properties->firstItem() }} - {{ $properties->lastItem() }} de {{ $properties->total() }} registros
                  </div>
                  {{ $properties->appends(request()->query())->links() }}
              </div>
          </div>
      @endif
  </div>
</div>

<!-- VISTA MÓVIL - ACTUALIZADA CON NUEVOS BOTONES -->
<div class="d-block d-md-none">
  @if($properties->count())
      <div class="list-group">
          @foreach($properties as $p)
              <div class="list-group-item">
                  <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                      <h6 class="mb-1 font-weight-bold">{{ $p->referencia }}</h6>
                      <div>
                          @if($p->estado === 'pendiente_conexion')
                              <span class="badge badge-primary small">Pendiente Conexión</span>
                          @elseif($p->estado === 'activo')
                              <span class="badge badge-success small">Activo</span>
                          @elseif($p->estado === 'corte_pendiente')
                              {{-- 🆕 MOSTRAR EL TIPO DE TRABAJO PENDIENTE --}}
                              @if($p->tipo_trabajo_pendiente === 'reconexion')
                                  <span class="badge badge-info small">Reconexión Pendiente</span>
                              @elseif($p->tipo_trabajo_pendiente === 'corte_mora')
                                  <span class="badge badge-warning small">Corte Pendiente</span>
                              @else
                                  <span class="badge badge-warning small">Corte Pendiente</span>
                              @endif
                          @elseif($p->estado === 'cortado')
                              <span class="badge badge-danger small">Cortado</span>
                          @else
                              <span class="badge badge-secondary small">Inactivo</span>
                          @endif
                      </div>
                  </div>
                  
                  <div class="mb-2">
                      <strong class="text-primary">{{ $p->client->nombre ?? 'N/A' }}</strong>
                      <div class="small text-muted">
                          <div><i class="fas fa-id-card mr-1"></i>{{ $p->client->ci ?? 'Sin CI' }}</div>
                          <div class="text-success font-weight-bold">
                              <i class="fas fa-barcode mr-1"></i>{{ $p->client->codigo_cliente ?? 'N/A' }}
                          </div>
                          <div class="text-{{ $p->client->estado_cuenta == 'activo' ? 'success' : 'warning' }}">
                              {{ $p->client->estado_cuenta ?? 'N/A' }}
                          </div>
                      </div>
                  </div>

                  <div class="mb-2">
                      @if($p->barrio)
                          <span class="badge badge-light border small">{{ $p->barrio }}</span>
                      @endif
                      <div class="small">
                          <strong>{{ $p->tariff->nombre ?? '—' }}</strong>
                          <span class="text-success">Bs {{ number_format($p->tariff->precio_mensual ?? 0, 2) }}</span>
                          @if($p->tariff && !$p->tariff->activo)
                              <span class="badge badge-warning ml-1 small">INACTIVA</span>
                          @endif
                      </div>
                  </div>

                  {{-- ✅ NUEVO: Alerta para propiedades cortadas en móvil --}}
                  @if($p->estado === 'cortado')
                      @php
                          $mesesAdeudados = $p->obtenerMesesAdeudados();
                          $mesesMora = count($mesesAdeudados);
                          $totalMeses = $mesesMora * ($p->tariff->precio_mensual ?? 0);
                      @endphp
                      @if($mesesMora > 0)
                          <div class="alert alert-warning p-2 mb-2">
                              <small>
                                  <i class="fas fa-exclamation-triangle mr-1"></i>
                                  <strong>SERVICIO CORTADO</strong><br>
                                  Debe {{ $mesesMora }} meses (Bs. {{ number_format($totalMeses, 2) }}) + Multa de reconexión
                              </small>
                          </div>
                      @endif
                  @endif

                  <div class="d-flex flex-wrap gap-1 justify-content-between">
                      @if($p->latitud && $p->longitud)
                          <button class="btn btn-info btn-sm"
                                  data-toggle="modal"
                                  data-target="#mapModal"
                                  data-lat="{{ $p->latitud }}"
                                  data-lng="{{ $p->longitud }}"
                                  data-ref="{{ $p->referencia }}"
                                  data-id="{{ $p->id }}">
                              <i class="fas fa-map-marker-alt"></i>
                          </button>
                      @else
                          <button class="btn btn-outline-secondary btn-sm" disabled>
                              <i class="fas fa-map-marker-alt"></i>
                          </button>
                      @endif

                      <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.properties.show', $p) }}">
                          <i class="fas fa-eye"></i>
                      </a>

                      <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.edit', $p) }}">
                          <i class="fas fa-edit"></i>
                      </a>

                      {{-- 🆕 ACTUALIZADO: Botones móviles según estado y rol --}}
                      @php
                          $user = auth()->user();
                          $isAdmin = $user->hasRole('Admin');
                          $isSecretaria = $user->hasRole('Secretaria');
                          $isOperador = $user->hasRole('Operador');
                      @endphp

                      {{-- PROPIEDADES ACTIVAS --}}
                      @if($p->estado === 'activo')
                          @if($isAdmin || $isSecretaria)
                              <form action="{{ route('admin.properties.cut', $p) }}" method="POST" class="d-inline">
                                  @csrf @method('PUT')
                                  <button class="btn btn-warning btn-sm" type="button" 
                                          onclick="confirmCutService({{ $p->id }}, '{{ $p->referencia }}')">
                                      <i class="fas fa-clock"></i>
                                  </button>
                              </form>
                          @endif
                      {{-- PROPIEDADES PENDIENTES DE CONEXIÓN --}}
                      @elseif($p->estado === 'pendiente_conexion')
                          @if($isAdmin || $isOperador)
                              <form action="{{ route('admin.cortes.marcar-cortado', $p) }}" method="POST" class="d-inline">
                                  @csrf
                                  <button class="btn btn-danger btn-sm" type="button" 
                                          onclick="confirmMarkAsCut({{ $p->id }}, '{{ $p->referencia }}')">
                                      <i class="fas fa-check-circle"></i>
                                  </button>
                              </form>
                          @endif
                      {{-- CORTES PENDIENTES --}}
                      @elseif($p->estado === 'corte_pendiente')
                          @if($isAdmin || $isOperador)
                              <form action="{{ route('admin.cortes.marcar-cortado', $p) }}" method="POST" class="d-inline">
                                  @csrf
                                  <button class="btn btn-danger btn-sm" type="button" 
                                          onclick="confirmMarkAsCut({{ $p->id }}, '{{ $p->referencia }}')">
                                      <i class="fas fa-ban"></i>
                                  </button>
                              </form>
                          @endif
                          
                          {{-- 🆕 CORREGIDO: Botón dinámico para cancelar según tipo de trabajo --}}
                          @if($isAdmin )
                              <form action="{{ route('admin.properties.cancel-cut', $p) }}" method="POST" class="d-inline">
                                  @csrf @method('PUT')
                                  <button class="btn btn-secondary btn-sm" type="button" 
                                          onclick="confirmCancelAction({{ $p->id }}, '{{ $p->referencia }}', '{{ $p->tipo_trabajo_pendiente }}')">
                                      <i class="fas fa-times"></i>
                                  </button>
                              </form>
                          @endif
                      {{-- PROPIEDADES CORTADAS --}}
                     {{-- ✅ REEMPLAZAR esta sección --}}
@elseif($p->estado === 'cortado')
@if($isAdmin || $isSecretaria)
    @php
        $mesesAdeudados = $p->obtenerMesesAdeudados();
        $mesesMora = count($mesesAdeudados);
    @endphp
    @if($mesesMora > 0)
        <form action="{{ route('admin.properties.request-reconnection', $p) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <button class="btn btn-success btn-sm" type="submit">
                <i class="fas fa-plug"></i>
            </button>
        </form>
    @else
        <button class="btn btn-outline-secondary btn-sm" disabled>
            <i class="fas fa-plug"></i>
        </button>
    @endif
@endif

                        @if($isAdmin)
                            <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <button class="btn btn-success btn-sm" type="button"
                                        onclick="confirmRestoreService({{ $p->id }}, '{{ $p->referencia }}')">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </form>
                        @endif
                        @endif

                      
                  </div>
              </div>
          @endforeach
      </div>

      <!-- Paginación móvil -->
      <div class="mt-3">
          {{ $properties->appends(request()->query())->links() }}
      </div>
  @else
      <div class="text-center py-5">
          <i class="fas fa-home fa-3x text-muted mb-3"></i>
          <h4 class="text-muted">
              @if(request()->anyFilled(['search', 'codigo_cliente', 'estado', 'barrio', 'tarifa_id']))
                  No se encontraron propiedades con los filtros aplicados
              @else
                  No hay propiedades registradas
              @endif
          </h4>
          @if(!request()->anyFilled(['search', 'codigo_cliente', 'estado', 'barrio', 'tarifa_id']))
              <a href="{{ route('admin.properties.create') }}" class="btn btn-primary mt-2">
                  <i class="fas fa-plus-circle mr-1"></i>Crear Primera Propiedad
              </a>
          @else
              <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary mt-2">
                  <i class="fas fa-undo mr-1"></i>Limpiar Filtros
              </a>
          @endif
      </div>
  @endif
</div>

  {{-- Modal con mapa --}}
  <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Ubicación de la Propiedad
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0" style="height: 500px;">
          <div id="leafletMap" style="height:100%; width:100%;"></div>
        </div>
        <div class="modal-footer">
          <small class="text-muted mr-auto" id="mapCoordinates"></small>
          <a id="gmapsLink" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-external-link-alt mr-1"></i>Abrir en Google Maps
          </a>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <style>
    .table td {
      vertical-align: middle;
    }
    .badge {
      font-size: 0.75em;
      font-weight: 500;
    }
    .btn-sm {
      margin: 1px;
      padding: 0.25rem 0.5rem;
    }
    #leafletMap {
      border-radius: 0 0 0.25rem 0.25rem;
    }
    .info-box {
      cursor: default;
      min-height: 80px;
    }
    .info-box-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 70px;
    }
    .card-outline {
      border-top: 3px solid #007bff;
    }
    .list-group-item {
      border-left: 3px solid #007bff;
    }
    @media (max-width: 576px) {
      .info-box {
        margin-bottom: 10px;
      }
      .btn-sm {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
      }
    }
  </style>
@stop

@section('js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    let leafletMap = null;
    let leafletMarker = null;

    // Inicializar Select2 para selects largos
    $(document).ready(function() {
      $('#cliente_id').select2({
        placeholder: "Seleccione un cliente",
        allowClear: true,
        width: '100%'
      });

      // Auto-submit cuando cambien algunos filtros
      $('#estado, #barrio, #tarifa_id, #orden').change(function() {
        $('#filterForm').submit();
      });

      // Auto-ocultar alertas después de 5 segundos
      setTimeout(() => {
        $('.alert').alert('close');
      }, 5000);
    });

    // Mapa Modal
    $('#mapModal').on('shown.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      const lat = parseFloat(button.data('lat'));
      const lng = parseFloat(button.data('lng'));
      const ref = button.data('ref') || 'Propiedad';
      const id = button.data('id');
    

      // Actualizar información
      $('#mapCoordinates').text(`Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
      
      // Link a Google Maps
      const gmaps = `https://www.google.com/maps?q=${lat},${lng}`;
      $('#gmapsLink').attr('href', gmaps);

      // Si no hay coordenadas válidas
      if (isNaN(lat) || isNaN(lng)) {
        if (leafletMap) { leafletMap.remove(); leafletMap = null; }
        document.getElementById('leafletMap').innerHTML = `
          <div class="d-flex justify-content-center align-items-center h-100 bg-light">
            <div class="text-center text-muted">
              <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
              <p>Sin coordenadas registradas</p>
            </div>
          </div>`;
        return;
      }

      // Inicializar mapa
      if (leafletMap) { leafletMap.remove(); }
      
      leafletMap = L.map('leafletMap');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(leafletMap);

      // Centrar y agregar marcador
      leafletMap.setView([lat, lng], 16);
      leafletMarker = L.marker([lat, lng]).addTo(leafletMap)
        .bindPopup(`<strong>${ref}</strong><br>ID: ${id}`)
        .openPopup();

      // Ajustar tamaño del mapa
      setTimeout(() => leafletMap.invalidateSize(), 100);
    });

    $('#mapModal').on('hidden.bs.modal', function () {
      if (leafletMap) { 
        leafletMap.remove(); 
        leafletMap = null; 
      }
    });

    // Funciones de confirmación
    function confirmDelete(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Está seguro?',
        html: `¿Eliminar la propiedad: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">Esta acción no se puede deshacer.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}`;
          form.innerHTML = `@csrf @method('DELETE')`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmCutService(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Solicitar Corte?',
        html: `¿Solicitar corte de servicio para: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">La propiedad quedará en estado "Corte Pendiente".</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, solicitar corte',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/cut`;
          form.innerHTML = `@csrf @method('PUT')`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmMarkAsCut(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Confirmar Corte Físico?',
        html: `¿Confirmar que se realizó el corte físico de: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-danger">Se aplicará multa automáticamente.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, corte realizado',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/cortes/marcar-cortado/${propertyId}`;
          form.innerHTML = `@csrf`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    // 🆕 NUEVA FUNCIÓN: Confirmación dinámica para cancelar acciones
    function confirmCancelAction(propertyId, propertyRef, tipoTrabajo) {
    let titulo, mensaje, textoBoton, icono = 'question';
    
    switch(tipoTrabajo) {
        case 'conexion_nueva':
            titulo = '¿Cancelar Instalación?';
            mensaje = `¿Cancelar la instalación pendiente de: <strong>"${propertyRef}"</strong>?<br>
                       <small class="text-info">La propiedad permanecerá en estado "Pendiente Conexión".</small>`;
            textoBoton = 'Sí, cancelar instalación';
            break;
        case 'corte_mora':
            titulo = '¿Cancelar Corte?';
            mensaje = `¿Cancelar la solicitud de corte para: <strong>"${propertyRef}"</strong>?<br>
                       <small class="text-info">La propiedad volverá a estado "Activo".</small>`;
            textoBoton = 'Sí, cancelar corte';
            break;
        case 'reconexion':
            titulo = '¿Cancelar Reconexión?';
            mensaje = `🚨 <strong>ADVERTENCIA:</strong> ¿Cancelar la reconexión de: <strong>"${propertyRef}"</strong>?<br>
                       <small class="text-danger">⚠️ Si el cliente ya pagó, NO cancele. El operador debe ejecutar la reconexión física.</small><br>
                       <small class="text-warning">Solo cancele si es un error y el cliente NO ha pagado.</small>`;
            textoBoton = 'Sí, cancelar (solo si no pagó)';
            icono = 'warning';
            break;
        default:
            titulo = '¿Cancelar Acción?';
            mensaje = `¿Cancelar la acción pendiente para: <strong>"${propertyRef}"</strong>?`;
            textoBoton = 'Sí, cancelar';
    }

    Swal.fire({
        title: titulo,
        html: mensaje,
        icon: icono,
        showCancelButton: true,
        confirmButtonColor: tipoTrabajo === 'reconexion' ? '#dc3545' : '#6c757d',
        cancelButtonColor: '#28a745',
        confirmButtonText: textoBoton,
        cancelButtonText: tipoTrabajo === 'reconexion' ? 'Mantener (Recomendado)' : 'Mantener',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/properties/${propertyId}/cancel-cut`;
            form.innerHTML = `@csrf @method('PUT')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
    function confirmRestoreService(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Reconectar Servicio?',
        html: `¿Reconectar el servicio de agua de: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-success">El cliente podrá recibir agua nuevamente.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reconectar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/restore`;
          form.innerHTML = `@csrf @method('PUT')`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmRequestReconnection(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Solicitar Reconexión?',
        html: `¿Solicitar reconexión de servicio para: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">La propiedad quedará en estado "Corte Pendiente" para que el operador realice la reconexión física.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, solicitar reconexión',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/request-reconnection`;
          form.innerHTML = `@csrf @method('PUT')`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }
    // ✅ NUEVA FUNCIÓN: Forzar reconexión cuando el cliente ya pagó
    function confirmForceReconnection(propertyId, propertyRef) {
        Swal.fire({
            title: '¿Forzar Reconexión?',
            html: `¿Marcar como reconectada la propiedad: <strong>"${propertyRef}"</strong>?<br>
                  <small class="text-success">✅ Use esta opción cuando el cliente YA PAGÓ y el operador completó el trabajo físico.</small><br>
                  <small class="text-info">La propiedad volverá a estado "Activo".</small>`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, reconexión completada',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/properties/${propertyId}/restore`;
                form.innerHTML = `@csrf @method('PUT')`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
  </script>
@stop