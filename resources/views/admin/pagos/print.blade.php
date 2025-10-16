<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - {{ $pagoPrincipal->numero_recibo }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
            background: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #333;
            padding: 20px;
            position: relative;
        }
        
        /* ENCABEZADO SIMPLIFICADO */
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        /* RESUMEN COMPACTO */
        .resumen {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .resumen-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            text-align: center;
        }
        
        /* INFORMACIÓN CLIENTE COMPACTA */
        .cliente-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        /* TABLA PRINCIPAL OPTIMIZADA */
        .pagos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .pagos-table th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .pagos-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .pagos-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        /* TOTALES */
        .totales {
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #333;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
        
        /* FIRMAS COMPACTAS */
        .firmas {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .firma-line {
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
        }
        
        .recibo-number {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #2c3e50;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .container {
                border: none;
                padding: 15px;
                box-shadow: none;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- ENCABEZADO SIMPLIFICADO --}}
        <div class="header">
            <h1>SISTEMA DE AGUA POTABLE</h1>
            <div style="color: #7f8c8d;">Recibo Oficial de Pago</div>
        </div>

        {{-- NÚMERO DE RECIBO --}}
        <div class="recibo-number">RECIBO: {{ $pagoPrincipal->numero_recibo }}</div>

        {{-- ✅ RESUMEN CONSOLIDADO --}}
        <div class="resumen">
            <div class="resumen-grid">
                <div>
                    <strong>Total Meses:</strong><br>
                    <span style="font-size: 16px;">{{ $pagosDelRecibo->count() }}</span>
                </div>
                <div>
                    <strong>Período:</strong><br>
                    <span style="font-size: 14px;">
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $pagosDelRecibo->first()->mes_pagado)->locale('es')->translatedFormat('M Y') }}
                        - 
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $pagosDelRecibo->last()->mes_pagado)->locale('es')->translatedFormat('M Y') }}
                    </span>
                </div>
                <div>
                    <strong>Monto Total:</strong><br>
                    <span style="font-size: 16px; color: #27ae60;">
                        Bs {{ number_format($pagosDelRecibo->sum('monto'), 2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ✅ INFORMACIÓN DEL CLIENTE COMPACTA --}}
        <div class="cliente-info">
            <div>
                <strong>Cliente:</strong> {{ $pagoPrincipal->propiedad->client->nombre ?? 'N/A' }}<br>
                <strong>CI/NIT:</strong> {{ $pagoPrincipal->propiedad->client->ci ?? 'N/A' }}<br>
                <strong>Código:</strong> {{ $pagoPrincipal->propiedad->client->codigo_cliente ?? 'N/A' }}
            </div>
            <div>
                <strong>Propiedad:</strong> {{ $pagoPrincipal->propiedad->referencia }}<br>
                <strong>Barrio:</strong> {{ $pagoPrincipal->propiedad->barrio ?? 'N/A' }}<br>
                <strong>Tarifa:</strong> {{ $pagoPrincipal->propiedad->tariff->nombre ?? 'N/A' }}
            </div>
        </div>

        {{-- ✅ TABLA ÚNICA CON TODOS LOS MESES --}}
        <table class="pagos-table">
            <thead>
                <tr>
                    <th width="50%">Mes de Servicio</th>
                    <th width="25%">Fecha de Pago</th>
                    <th width="25%">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagosDelRecibo as $pago)
                <tr>
                    <td>
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado)->locale('es')->translatedFormat('F Y') }}</strong>
                    </td>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td><strong>Bs {{ number_format($pago->monto, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ✅ INFORMACIÓN DEL PAGO CENTRALIZADA --}}
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 15px 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px;">
                <div>
                    <strong>Método de Pago:</strong> {{ ucfirst($pagoPrincipal->metodo) }}<br>
                    <strong>Comprobante:</strong> {{ $pagoPrincipal->comprobante ?? 'N/A' }}
                </div>
                <div>
                    <strong>Registrado por:</strong> {{ $pagoPrincipal->registradoPor->name ?? 'Sistema' }}<br>
                    <strong>Observaciones:</strong> {{ $pagoPrincipal->observaciones ?? 'Ninguna' }}
                </div>
            </div>
        </div>

        {{-- ✅ TOTAL --}}
        <div class="totales">
            <div>
                <span style="font-size: 16px;">
                    @if($pagosDelRecibo->count() > 1)
                    TOTAL ({{ $pagosDelRecibo->count() }} MESES):
                    @else
                    TOTAL:
                    @endif
                </span>
                <span class="total-amount">Bs {{ number_format($pagosDelRecibo->sum('monto'), 2) }}</span>
            </div>
        </div>

        {{-- ✅ FIRMAS --}}
        <div class="firmas">
            <div class="firma-line">
                Firma del Cliente
            </div>
            <div class="firma-line">
                Firma del Responsable
            </div>
        </div>

        {{-- ✅ PIE DE PÁGINA --}}
        <div style="text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #7f8c8d;">
            <p><strong>Este recibo es un comprobante oficial del pago realizado.</strong></p>
            <p>Conserve este documento para cualquier consulta o reclamo.</p>
            <p>Impreso el: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    {{-- BOTONES --}}
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px;">
            🖨️ Imprimir Recibo
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px;">
            ❌ Cerrar
        </button>
    </div>

    <script>
        window.onload = function() {
            // Auto-imprimir al cargar (opcional)
            // window.print();
        };
    </script>
</body>
</html>