<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante #{{ $order->id }}</title>
    <style>
        /* Reset y base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            margin: 0;
            padding: 6mm;
            background: #fff;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        /* Contenedor */
        .wrap { max-width: 360px; margin: 16px auto; padding: 0 12px; }

        /* Tarjeta principal */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        /* Encabezado */
        .header { text-align: center; padding: 16px 14px 10px; }
        .logo {
            max-height: 56px;
            max-width: 260px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto 6px;
        }
        .brand { font-weight: 700; font-size: 15px; letter-spacing: 0.3px; }
        .subtitle { color: #475569; font-size: 11px; margin-top: 2px; }
        .badge {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 10px;
            border: 1px dashed #e2e8f0;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        /* Separadores */
        .rule {
            height: 1px;
            background: #e2e8f0;
            margin: 10px 0;
        }
        .rule--dotted {
            height: 1px;
            border-top: 2px dotted #e2e8f0;
            margin: 12px 0;
        }

        /* Meta información */
        .meta { padding: 8px 14px; font-size: 12px; }
        .row {
            width: 100%;
            margin: 6px 0;
        }
        .row::after {
            content: "";
            display: table;
            clear: both;
        }
        .label {
            color: #475569;
            float: left;
        }
        .value {
            font-weight: 600;
            float: right;
        }

        /* Items */
        .items { padding: 6px 14px 10px; }
        .item {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
            min-height: 36px;
        }
        .item:last-child { border-bottom: none; }
        .item > div:first-child {
            width: 70%;
            float: left;
        }
        .name {
            font-size: 13px;
            font-weight: 400;
            line-height: 1.3;
            margin-bottom: 4px;
            word-wrap: break-word;
        }
        .muted {
            color: #475569;
            font-size: 11px;
            font-weight: 400;
        }
        .item-details { margin-top: 2px; }
        .unit { font-weight: 400; }
        .amt {
            position: absolute;
            right: 0;
            top: 8px;
            text-align: right;
            font-size: 13px;
            font-weight: 700;
        }

        /* Totales */
        .totals { padding: 8px 14px 12px; font-size: 12px; }
        .trow {
            margin: 4px 0;
            clear: both;
        }
        .trow::after {
            content: "";
            display: table;
            clear: both;
        }
        .trow .label { float: left; }
        .trow .tval { float: right; }
        .trow.total {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid #e2e8f0;
            font-weight: 800;
        }

        /* Notas */
        .notes { padding: 8px 14px 12px; }
        .notes h4 { font-size: 12px; margin: 0 0 4px; }
        .notes p {
            font-size: 11px;
            color: #475569;
            white-space: pre-wrap;
            margin: 0;
        }

        /* QR */
        .qr {
            text-align: center;
            padding: 8px 0 14px;
        }
        .qr img {
            width: 96px;
            height: 96px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Footer */
        .footer { text-align: center; padding: 10px 14px 14px; }
        .small { font-size: 11px; color: #475569; }
    </style>
</head>
<body>
    @include('orders.partials.ticket', [
        'order' => $order,
        'logoUrl' => $logoUrl ?? null,
        'appName' => $appName ?? config('app.name', 'Rellenito'),
        'subtotal' => $totals['subtotal'] ?? null,
        'discount' => $totals['discount'] ?? null,
        'tax' => $totals['tax_amount'] ?? null,
        'total' => $totals['total'] ?? null,
        'withControls' => false,
        'isPdf' => true,
        'qr' => $qr ?? null,
        'totals' => $totals ?? [],
        'paymentMethod' => $paymentMethod ?? null,
    ])
</body>
</html>
