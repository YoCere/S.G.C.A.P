<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recibo - {{ $pagoPrincipal->numero_recibo }}</title>

@php
    $logoPath = public_path('images/logo_ng.png');
    $logoBase64 = null;
    $useCssFallback = false;

    if (file_exists($logoPath)) {
        // Imagick preferido
        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick($logoPath);
                $im->gaussianBlurImage(0, 4); // leve desenfoque opcional
                $im->setImageFormat('png');
                $logoBase64 = base64_encode($im->getImageBlob());
                $im->clear();
                $im->destroy();
            } catch (\Exception $e) {
                $logoBase64 = null;
            }
        }

        // Fallback GD
        if (!$logoBase64 && function_exists('imagecreatefrompng')) {
            $src = @imagecreatefrompng($logoPath);
            if ($src !== false) {
                for ($i = 0; $i < 4; $i++) {
                    @imagefilter($src, IMG_FILTER_GAUSSIAN_BLUR);
                }
                ob_start();
                imagepng($src);
                $imgData = ob_get_clean();
                imagedestroy($src);
                $logoBase64 = base64_encode($imgData);
            }
        }

        if (!$logoBase64) {
            $useCssFallback = true;
        }
    } else {
        $useCssFallback = true;
    }

    // URL fallback si no hay base64 (para <img>)
    $logoUrl = $logoBase64 ? "data:image/png;base64,{$logoBase64}" : asset('images/logo_ng.png');
@endphp

<style>
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#333; margin:0; padding:15px; background:#f8f9fa; }
    .container { max-width:800px; margin:0 auto; background:#fff; border:1px solid #333; padding:20px; padding-top: 40px; position:relative; overflow:visible; }

    /* ===== LOGO ABSOLUTO (siempre imprimible) ===== */
    .logo-print {
        position: absolute;
        top: 12px;        /* distancia desde borde superior */
        left: 12px;       /* distancia desde borde izquierdo */
        width: 120px;     /* ancho del logo (ajusta) */
        height: auto;
        opacity: 0.95;    /* casi opaco — si quieres más tenue baja a 0.6 o 0.4 */
        pointer-events: none;
        z-index: 0;       /* DETRÁS */
        filter: saturate(0.85) brightness(1.02); /* ligero ajuste visual */
    }

    /* Si quieres que el logo se vea más tenue en papel, se puede bajar aquí */
    @media print {
        .logo-print { opacity: 0.85; } /* ajustar si la impresora lo imprime muy fuerte */
    }

    /* ===== contenido encima del logo ===== */
    .container > * { position: relative; z-index: 2; }

    .recibo-number { position:absolute; top:18px; right:20px; background:#2c3e50; color:#fff; padding:5px 10px; border-radius:3px; font-weight:700; z-index:11; font-size:13px; }

    .header { text-align:center; border-bottom:3px double #333; padding-bottom:12px; margin-bottom:16px; }
    .header h1 { margin:0; font-size:20px; color:#2c3e50; }

    .resumen { background:#f8f9fa; border:1px solid #ddd; border-radius:5px; padding:12px; margin-bottom:14px; }
    .resumen-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; text-align:center; font-size:13px; }

    .cliente-info { display:grid; grid-template-columns:1fr 1fr; gap:12px; background:#f8f9fa; padding:10px; border-radius:5px; margin-bottom:14px; }

    .pagos-table { width:100%; border-collapse:collapse; margin:14px 0; }
    .pagos-table th { background:#2c3e50; color:#fff; padding:8px; border:1px solid #ddd; }
    .pagos-table td { padding:8px; border:1px solid #ddd; }

    .seccion-multas { background:#fef9e7; border:1px solid #f39c12; border-radius:5px; padding:12px; margin-top:12px; }
    .multas-table { width:100%; border-collapse:collapse; }
    .multas-table th { background:#e74c3c; color:#fff; padding:8px; border:1px solid #ddd; }
    .multas-table td { padding:8px; border:1px solid #ddd; }

    .totales { border-top:2px solid #333; padding-top:10px; margin-top:18px; text-align:right; }
    .total-amount { font-size:16px; color:#27ae60; font-weight:700; }

    .firmas { margin-top:30px; display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .firma-line { border-top:1px solid #333; padding-top:6px; text-align:center; font-size:11px; }

    @media print {
        body { margin:0; padding:0; background:white; }
        .container { border:none; padding:28px; padding-top:110px; }
        .no-print { display:none; }
    }
</style>
</head>
<body>

<div class="container">

    {{-- IMG absoluta: siempre impresible (usa base64 si fue creado) --}}
    <img src="{{ $logoUrl }}" alt="Logo" class="logo-print" />

    <div class="recibo-number">RECIBO: {{ $pagoPrincipal->numero_recibo }}</div>

    <div class="header">
        <h1>SISTEMA DE AGUA POTABLE</h1>
        <div style="color:#777; font-size:12px;">Recibo Oficial de Pago</div>
    </div>

    <div class="resumen">
        <div class="resumen-grid">
            <div><strong>Total Meses:</strong><br>{{ $pagosDelRecibo->count() }}</div>
            <div><strong>Período:</strong><br>
                {{ \Carbon\Carbon::parse($pagosDelRecibo->first()->mes_pagado)->translatedFormat('M Y') }}
                –
                {{ \Carbon\Carbon::parse($pagosDelRecibo->last()->mes_pagado)->translatedFormat('M Y') }}
            </div>
            <div><strong>Multas:</strong><br>{{ $multasPagadas->count() }}</div>
            @php
                $totalMeses = $pagosDelRecibo->sum('monto');
                $totalMultas = $multasPagadas->sum('monto');
                $totalGeneral = $totalMeses + $totalMultas;
            @endphp
            <div><strong>Total:</strong><br><span class="total-amount">Bs {{ number_format($totalGeneral,2) }}</span></div>
        </div>
    </div>

    <div class="cliente-info">
        <div>
            <strong>Cliente:</strong> {{ $pagoPrincipal->propiedad->client->nombre ?? 'N/A' }}<br>
            <strong>CI/NIT:</strong> {{ $pagoPrincipal->propiedad->client->ci ?? 'N/A' }}<br>
            <strong>Código:</strong> {{ $pagoPrincipal->propiedad->client->codigo_cliente ?? 'N/A' }}
        </div>
        <div>
            <strong>Propiedad:</strong> {{ $pagoPrincipal->propiedad->referencia ?? 'N/A' }}<br>
            <strong>Barrio:</strong> {{ $pagoPrincipal->propiedad->barrio ?? 'N/A' }}<br>
            <strong>Tarifa:</strong> {{ $pagoPrincipal->propiedad->tariff->nombre ?? 'N/A' }}
        </div>
    </div>

    <table class="pagos-table">
        <thead><tr><th>Mes de Servicio</th><th>Fecha de Pago</th><th>Monto</th></tr></thead>
        <tbody>
            @foreach($pagosDelRecibo as $pago)
            <tr>
                <td>{{ \Carbon\Carbon::parse($pago->mes_pagado)->translatedFormat('F Y') }}</td>
                <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                <td><strong>Bs {{ number_format($pago->monto,2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($multasPagadas->count() > 0)
    <div class="seccion-multas">
        <h4 style="text-align:center;margin:0 0 10px;color:#c0392b;">MULTAS PAGADAS</h4>
        <table class="multas-table">
            <thead><tr><th>Descripción</th><th>Fecha</th><th>Monto</th></tr></thead>
            <tbody>
                @foreach($multasPagadas as $multa)
                <tr>
                    <td>{{ $multa->nombre }}</td>
                    <td>{{ $multa->fecha_aplicacion->format('d/m/Y') }}</td>
                    <td><strong>Bs {{ number_format($multa->monto,2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="totales">
        <span style="font-size:14px;">TOTAL GENERAL:</span>
        <span class="total-amount">Bs {{ number_format($totalGeneral,2) }}</span>
    </div>

    <div class="firmas">
        <div class="firma-line">Firma del Cliente</div>
        <div class="firma-line">Firma del Responsable</div>
    </div>

    <div style="text-align:center;margin-top:12px;font-size:10px;color:#777;">Impreso el {{ now()->format('d/m/Y H:i:s') }}</div>

</div>

<div class="no-print" style="text-align:center;margin-top:16px;">
    <button onclick="window.print()">🖨️ Imprimir</button>
    <button onclick="window.close()">Cerrar</button>
</div>

</body>
</html>
