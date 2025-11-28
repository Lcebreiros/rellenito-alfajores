<?php

namespace App\Services;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    /**
     * Generar PDF de factura
     */
    public function generatePdf(Invoice $invoice)
    {
        // Generar código QR para AFIP
        $qrData = $this->generateQrData($invoice);
        $qrCodeImage = (new QRCode(new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 6,
        ])))->render($qrData);

        // Generar HTML
        $html = $this->generateHtml($invoice, $qrCodeImage);

        // Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfContent = $dompdf->output();
        $fileName = 'invoices/' . $invoice->company_id . '/' . $invoice->voucher_type . '-' . $invoice->full_number . '.pdf';

        Storage::disk('public')->put($fileName, $pdfContent);

        // Actualizar ruta en invoice
        $invoice->pdf_path = $fileName;
        $invoice->save();

        return $fileName;
    }

    /**
     * Generar datos para código QR de AFIP
     */
    protected function generateQrData(Invoice $invoice)
    {
        $config = $invoice->company->arcaConfiguration;

        $data = [
            'ver' => 1,
            'fecha' => $invoice->invoice_date,
            'cuit' => $config->cuit ?? '',
            'ptoVta' => $invoice->sale_point,
            'tipoCmp' => $this->getVoucherTypeCode($invoice->voucher_type),
            'nroCmp' => $invoice->voucher_number,
            'importe' => number_format($invoice->total, 2, '.', ''),
            'moneda' => 'PES',
            'ctz' => 1,
            'tipoDocRec' => $invoice->client_cuit ? 80 : 99,
            'nroDocRec' => $invoice->client_cuit ? preg_replace('/[^0-9]/', '', $invoice->client_cuit) : 0,
            'tipoCodAut' => 'E',
            'codAut' => $invoice->cae ?? 0
        ];

        return 'https://www.afip.gob.ar/fe/qr/?' . http_build_query($data);
    }

    /**
     * Generar HTML de la factura
     */
    protected function generateHtml(Invoice $invoice, $qrCodeImage)
    {
        $config = $invoice->company->arcaConfiguration ?? null;
        $voucherTypeName = $this->getVoucherTypeName($invoice->voucher_type);
        $voucherLetter = $this->getVoucherLetter($invoice->voucher_type);

        $html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura ' . $invoice->full_number . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            padding: 20px;
        }
        .header {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .company-info {
            width: 45%;
        }
        .voucher-type {
            width: 10%;
            text-align: center;
            border: 2px solid #000;
            padding: 10px;
        }
        .voucher-type h1 {
            font-size: 48pt;
            margin: 10px 0;
        }
        .invoice-info {
            width: 45%;
            text-align: right;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .client-section {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            width: 50%;
            margin-left: auto;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }
        .cae-section {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
        .qr-code img {
            width: 150px;
            height: 150px;
        }
        small {
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top" style="display: table; width: 100%;">
            <div class="company-info" style="display: table-cell; width: 40%; vertical-align: top;">
                <div class="company-name">' . ($config->business_name ?? 'Sin configurar') . '</div>
                <div>CUIT: ' . ($config->cuit ?? 'N/A') . '</div>
                <div>Condición IVA: ' . ($config->tax_condition ?? 'N/A') . '</div>
                <div><small>Punto de venta: ' . $invoice->sale_point . '</small></div>
            </div>

            <div class="voucher-type" style="display: table-cell; width: 20%; vertical-align: middle; text-align: center;">
                <h1>' . $voucherLetter . '</h1>
                <div style="font-size: 8pt;">COD. ' . $this->getVoucherTypeCode($invoice->voucher_type) . '</div>
            </div>

            <div class="invoice-info" style="display: table-cell; width: 40%; vertical-align: top; text-align: right;">
                <div style="font-size: 12pt; font-weight: bold;">' . $voucherTypeName . '</div>
                <div>Nro: ' . $invoice->full_number . '</div>
                <div>Fecha: ' . \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') . '</div>
            </div>
        </div>
    </div>

    <div class="client-section">
        <strong>Cliente:</strong><br>
        Nombre/Razón Social: ' . $invoice->client_name . '<br>
        ' . ($invoice->client_cuit ? 'CUIT: ' . $invoice->client_cuit . '<br>' : '') . '
        Condición IVA: ' . $invoice->client_tax_condition . '<br>
        ' . ($invoice->client_address ? 'Dirección: ' . $invoice->client_address : '') . '
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">Cant.</th>
                <th style="width: 50%;">Descripción</th>
                <th style="width: 15%;" class="text-right">P. Unit.</th>
                <th style="width: 10%;" class="text-center">IVA %</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($invoice->items as $item) {
            $html .= '
            <tr>
                <td class="text-center">' . number_format($item->quantity, 2, ',', '.') . '</td>
                <td>' . $item->description . '</td>
                <td class="text-right">$ ' . number_format($item->unit_price, 2, ',', '.') . '</td>
                <td class="text-center">' . number_format($item->tax_rate, 1) . '%</td>
                <td class="text-right">$ ' . number_format($item->total, 2, ',', '.') . '</td>
            </tr>';
        }

        $html .= '
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="text-right">$ ' . number_format($invoice->subtotal, 2, ',', '.') . '</td>
        </tr>
        <tr>
            <td><strong>IVA:</strong></td>
            <td class="text-right">$ ' . number_format($invoice->tax_amount, 2, ',', '.') . '</td>
        </tr>
        <tr style="background-color: #f0f0f0;">
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>$ ' . number_format($invoice->total, 2, ',', '.') . '</strong></td>
        </tr>
    </table>';

        if ($invoice->cae) {
            $html .= '
    <div class="cae-section">
        <strong>Comprobante Autorizado</strong><br>
        <strong>CAE:</strong> ' . $invoice->cae . '<br>
        <strong>Fecha Vto. CAE:</strong> ' . \Carbon\Carbon::parse($invoice->cae_expiration)->format('d/m/Y') . '
    </div>

    <div class="qr-code">
        <img src="' . $qrCodeImage . '" alt="QR AFIP">
        <br>
        <small>Escaneá este código para verificar el comprobante</small>
    </div>';
        }

        $html .= '
    <div class="footer">
        <small>
            Documento electrónico generado por Gestior - Sistema de Gestión Comercial<br>
            Fecha de generación: ' . now()->format('d/m/Y H:i') . '
        </small>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Obtener nombre del tipo de comprobante
     */
    protected function getVoucherTypeName($voucherType)
    {
        $names = [
            'FC-A' => 'FACTURA A',
            'FC-B' => 'FACTURA B',
            'FC-C' => 'FACTURA C',
            'NC-A' => 'NOTA DE CRÉDITO A',
            'NC-B' => 'NOTA DE CRÉDITO B',
            'NC-C' => 'NOTA DE CRÉDITO C',
            'ND-A' => 'NOTA DE DÉBITO A',
            'ND-B' => 'NOTA DE DÉBITO B',
            'ND-C' => 'NOTA DE DÉBITO C',
        ];

        return $names[$voucherType] ?? 'COMPROBANTE';
    }

    /**
     * Obtener letra del comprobante
     */
    protected function getVoucherLetter($voucherType)
    {
        return substr($voucherType, -1);
    }

    /**
     * Obtener código de tipo de comprobante
     */
    protected function getVoucherTypeCode($voucherType)
    {
        $codes = [
            'FC-A' => 1,
            'FC-B' => 6,
            'FC-C' => 11,
            'NC-A' => 3,
            'NC-B' => 8,
            'NC-C' => 13,
            'ND-A' => 2,
            'ND-B' => 7,
            'ND-C' => 12,
        ];

        return $codes[$voucherType] ?? 6;
    }
}
