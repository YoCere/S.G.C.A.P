<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pago - {{ $pago->numero_recibo }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            padding: 25px;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #2c3e50;
            font-weight: bold;
        }
        .header .subtitle {
            margin: 8px 0;
            font-size: 16px;
            color: #7f8c8d;
        }
        .recibo-number {
            position: absolute;
            top: 25px;
            right: 25px;
            background: #2c3e50;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 140px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            border: 1px solid #ddd;
        }
        .details-table th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .details-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .total-section {
            text-align: right;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 3px double #333;
        }
        .total-amount {
            font-size: 22px;
            font-weight: bold;
            color: #27ae60;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
        }
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .signature-line {
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 8px;
            font-size: 11px;
        }
        .watermark {
            position: absolute;
            opacity: 0.03;
            font-size: 120px;
            transform: rotate(-45deg);
            top: 35%;
            left: 10%;
            color: #333;
            font-weight: bold;
            z-index: -1;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
        }
        .status-current {
            background: #28a745;
            color: white;
        }
        .status-future {
            background: #ffc107;
            color: #212529;
        }
        .status-past {
            background: #6c757d;
            color: white;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .container {
                border: none;
                padding: 0;
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- MARCA DE AGUA --}}
        <div class="watermark">PAGADO</div>
        
        {{-- ENCABEZADO --}}
        <div class="header">
            <h1>SISTEMA DE AGUA POTABLE</h1>
            <div class="subtitle">Recibo Oficial de Pago</div>
            <div class="subtitle">Servicio de Distribución de Agua</div>
        </div>

        {{-- NÚMERO DE RECIBO --}}
        <div class="recibo-number">RECIBO: {{ $pago->numero_recibo }}</div>

        {{-- INFORMACIÓN DEL CLIENTE --}}
        <div class="info-section">
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Cliente:</span>
                        {{ $pago->cliente->nombre }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">C.I./NIT:</span>
                        {{ $pago->cliente->ci ?? 'No especificado' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Teléfono:</span>
                        {{ $pago->cliente->telefono ?? 'No especificado' }}
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Propiedad:</span>
                        {{ $pago->propiedad->referencia }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Barrio/Zona:</span>
                        {{ $pago->propiedad->barrio ?? 'No especificado' }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tarifa:</span>
                        {{ $pago->propiedad->tariff->nombre ?? 'No especificada' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLES DEL PAGO --}}
        <table class="details-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Período</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pago de Servicio de Agua</td>
                    <td>
                        <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado)->format('F Y') }}</strong>
                        @php
                            $mesPago = \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado);
                            $now = \Carbon\Carbon::now();
                        @endphp
                        @if($mesPago->format('Y-m') == $now->format('Y-m'))
                            <span class="status-badge status-current">MES ACTUAL</span>
                        @elseif($mesPago > $now)
                            <span class="status-badge status-future">PAGO ADELANTADO</span>
                        @else
                            <span class="status-badge status-past">MES ANTERIOR</span>
                        @endif
                    </td>
                    <td><strong>Bs {{ number_format($pago->monto, 2) }}</strong></td>
                </tr>
                @if($pago->observaciones)
                <tr>
                    <td colspan="3">
                        <strong>Observaciones:</strong> {{ $pago->observaciones }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        {{-- INFORMACIÓN DEL PAGO --}}
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Fecha de Pago:</span>
                    <strong>{{ $pago->fecha_pago->format('d/m/Y') }}</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Método de Pago:</span>
                    <strong>{{ ucfirst($pago->metodo) }}</strong>
                </div>
            </div>
            <div>
                @if($pago->comprobante)
                <div class="info-item">
                    <span class="info-label">Comprobante:</span>
                    {{ $pago->comprobante }}
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Registrado por:</span>
                    {{ $pago->registradoPor->name ?? 'Sistema' }}
                </div>
            </div>
        </div>

        {{-- TOTAL --}}
        <div class="total-section">
            <div class="info-item">
                <span class="info-label" style="font-size: 16px;">TOTAL PAGADO:</span>
                <span class="total-amount">Bs {{ number_format($pago->monto, 2) }}</span>
            </div>
        </div>

        {{-- FIRMAS --}}
        <div class="signature-section">
            <div class="signature-line">
                ______________________________<br>
                Firma del Cliente
            </div>
            <div class="signature-line">
                ______________________________<br>
                Firma del Responsable
            </div>
        </div>

        {{-- PIE DE PÁGINA --}}
        <div class="footer">
            <p><strong>Este recibo es un comprobante oficial del pago realizado.</strong></p>
            <p>Conserve este documento para cualquier consulta o reclamo.</p>
            <p>Impreso el: {{ now()->format('d/m/Y H:i:s') }} | Sistema de Gestión de Agua</p>
            <p class="no-print">
                <button onclick="window.print()" class="no-print" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">🖨️ Imprimir Recibo</button>
                <button onclick="window.close()" class="no-print" style="padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">❌ Cerrar</button>
            </p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar
        window.onload = function() {
            // Descomenta la siguiente línea para auto-imprimir
            // window.print();
        };
    </script>
</body>
</html>