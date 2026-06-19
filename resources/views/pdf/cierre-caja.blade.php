<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre de Caja</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #111; margin: 0; padding: 10px; }
        h1 { text-align: center; font-size: 16px; margin-bottom: 2px; }
        .sub { text-align: center; font-size: 10px; color: #555; margin-bottom: 16px; }
        hr { border: none; border-top: 1px dashed #333; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 4px; }
        .lbl { text-align: left; font-weight: bold; width: 50%; }
        .val { text-align: right; width: 50%; }
        .sec-title { font-size: 12px; font-weight: bold; text-align: center; padding: 6px 0; }
        .total-row { font-weight: bold; border-top: 1px solid #333; }
        .faltante { color: #c00; }
        .sobrante { color: #080; }
        .footer { text-align: center; font-size: 9px; color: #888; margin-top: 16px; }
    </style>
</head>
<body>

<h1>Monasterios Market</h1>
<p class="sub">Reporte de Cierre de Caja</p>

<hr>

<table>
    <tr><td class="lbl">Cajero:</td><td class="val">{{ $cajero }}</td></tr>
    <tr><td class="lbl">Apertura:</td><td class="val">{{ $apertura }}</td></tr>
    <tr><td class="lbl">Cierre:</td><td class="val">{{ $cierre }}</td></tr>
    <tr><td class="lbl">Sesión #:</td><td class="val">{{ $sesion_id }}</td></tr>
</table>

<hr>

<p class="sec-title">— Resumen Financiero —</p>

<table>
    <tr><td class="lbl">Monto de Inicio</td><td class="val">${{ number_format($opening, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">+ Ventas Efectivo</td><td class="val">${{ number_format($cash_sales, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">+ Ingresos</td><td class="val">${{ number_format($ingresos, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">- Retiros</td><td class="val">${{ number_format($retiros, 0, ',', '.') }}</td></tr>
    <tr class="total-row"><td class="lbl">= Esperado en Caja</td><td class="val">${{ number_format($esperado, 0, ',', '.') }}</td></tr>
    <tr><td colspan="2" style="padding:2px;"></td></tr>
    <tr><td class="lbl">Efectivo Declarado</td><td class="val">${{ number_format($efectivo_cierre, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">Redcompra Declarado</td><td class="val">${{ number_format($red_compra, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">Transferencia Declarada</td><td class="val">${{ number_format($transferencia, 0, ',', '.') }}</td></tr>
</table>

<hr>

<p class="sec-title">— Auditoría —</p>

<table>
    @php $diferencia = $efectivo_cierre - $esperado; @endphp
    <tr>
        <td class="lbl">Diferencia Total</td>
        <td class="val {{ $diferencia < 0 ? 'faltante' : ($diferencia > 0 ? 'sobrante' : '') }}">
            @if ($diferencia < 0)
                Faltante: -${{ number_format(abs($diferencia), 0, ',', '.') }}
            @elseif ($diferencia > 0)
                Sobrante: +${{ number_format($diferencia, 0, ',', '.') }}
            @else
                Caja Cuadrada
            @endif
        </td>
    </tr>
</table>

<p class="footer">
    Generado el {{ now('America/Santiago')->format('d/m/Y H:i:s') }} • Monasterios Market
</p>

</body>
</html>
