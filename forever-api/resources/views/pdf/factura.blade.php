<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura_{{ $venta->nro_factura }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; color: #2d3748; line-height: 1.4; margin: 0; }
        
        /* Cabecera */
        .header-table { width: 100%; border-bottom: 4px solid #065f46; padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { color: #064e3b; font-size: 28px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-label { font-size: 32px; color: #e2e8f0; font-weight: 900; text-align: right; text-transform: uppercase; }
        
        /* Bloques de Información */
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-box { vertical-align: top; width: 50%; }
        .info-title { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .info-content { font-size: 13px; font-weight: bold; color: #1e293b; }

        /* Tabla de Productos */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f1f5f9; color: #475569; font-size: 11px; font-weight: bold; text-align: left; padding: 12px; border-bottom: 2px solid #cbd5e1; text-transform: uppercase; }
        .items-table td { padding: 12px; font-size: 12px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .items-table tr:nth-child(even) { background-color: #f8fafc; }

        /* Totales */
        .totals-wrapper { width: 100%; }
        .totals-table { width: 250px; margin-left: auto; border-top: 2px solid #065f46; }
        .total-row td { padding: 8px 12px; font-size: 12px; }
        .total-amount { background-color: #065f46; color: white; font-weight: bold; font-size: 16px !important; border-radius: 8px; }
        .cc-badge { color: #2563eb; font-weight: bold; font-size: 11px; text-align: right; padding-top: 10px; }

        /* Pie de página */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">Forever Living</div>
                <div style="font-size: 12px; color: #059669;">Gerencia Regional La Paz, Bolivia</div>
            </td>
            <td class="invoice-label">Factura</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-box">
                <div class="info-title">Datos del Cliente</div>
                <div class="info-content">{{ $venta->razon_social ?? 'CONSUMIDOR FINAL' }}</div>
                <div class="info-content">NIT/CI: {{ $venta->nit_ci ?? 'S/N' }}</div>
            </td>
            <td class="info-box" style="text-align: right;">
                <div class="info-title">Detalles de Facturación</div>
                <div class="info-content">Nro: {{ $venta->nro_factura }}</div>
                <div class="info-content">Fecha: {{ $venta->created_at->format('d/m/Y') }}</div>
                <div class="info-content">Hora: {{ $venta->created_at->format('H:i') }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="50%">Descripción del Producto</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: right;">Precio Unit.</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->items as $item)
            <tr>
                <td style="font-weight: bold;">{{ $item->producto->name }}</td>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td style="text-align: right;">Bs. {{ number_format($item->precio_unitario, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">Bs. {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrapper">
        <table class="totals-table">
            <tr class="total-row">
                <td style="color: #64748b;">Subtotal:</td>
                <td style="text-align: right; font-weight: bold;">Bs. {{ number_format($venta->monto_total - $venta->monto_iva, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td style="color: #ef4444;">IVA (13%):</td>
                <td style="text-align: right; font-weight: bold; color: #ef4444;">Bs. {{ number_format($venta->monto_iva, 2) }}</td>
            </tr>
            <tr class="total-row total-amount">
                <td>TOTAL A PAGAR:</td>
                <td style="text-align: right;">Bs. {{ number_format($venta->monto_total, 2) }}</td>
            </tr>
        </table>
        <div class="cc-badge">
            PUNTOS CC TOTALES DE LA COMPRA: {{ number_format($venta->total_cc, 3) }}
        </div>
    </div>

    <div class="footer">
        <p>Documento emitido por el Sistema de Gestión Forever Living Bolivia - Sede La Paz</p>
        <p>Gracias por ser parte de nuestra familia Forever. ¡Éxitos en tu negocio!</p>
    </div>

</body>
</html>