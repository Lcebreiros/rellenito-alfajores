<?php

namespace App\Services;

use App\Models\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class OrderTicketPdfService
{
    /**
     * Genera el PDF inline reutilizando la vista del comprobante.
     */
    public function render(Order $order, array $viewData = []): string
    {
        // Generar QR code localmente si está presente
        $qrData = $viewData['qr'] ?? null;
        if ($qrData) {
            $viewData['qr_base64'] = $this->generateQrBase64($qrData);
        }

        // Convertir logo a base64 si es una URL local
        if (!empty($viewData['logoUrl'])) {
            $viewData['logoUrl'] = $this->convertLogoToBase64($viewData['logoUrl']);
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false); // Deshabilitado para evitar timeouts
        $options->set('defaultFont', 'Arial');
        $options->set('chroot', public_path()); // Permitir archivos locales
        $options->set('debugPng', false);
        $options->set('debugKeepTemp', false);
        $options->set('debugCss', false);

        $html = view('pdf.order-ticket', $viewData + ['order' => $order])->render();

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Genera un QR code en formato base64 data URI
     */
    private function generateQrBase64(string $data): string
    {
        try {
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 6,
                'imageBase64' => true,
            ]);

            return (new QRCode($options))->render($data);
        } catch (\Throwable $e) {
            \Log::warning('QR generation failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Convierte un logo local a base64 para evitar problemas con rutas
     */
    private function convertLogoToBase64(string $logoUrl): string
    {
        try {
            // Si ya es base64, devolverlo tal cual
            if (str_starts_with($logoUrl, 'data:')) {
                return $logoUrl;
            }

            // Si ya es una URL remota completa (http/https), dejarla así
            if (str_starts_with($logoUrl, 'http://') || str_starts_with($logoUrl, 'https://')) {
                // Intentar convertir URLs locales (mismo dominio) a rutas de archivo
                $host = parse_url(config('app.url'), PHP_URL_HOST);
                if ($host && str_contains($logoUrl, $host)) {
                    $path = parse_url($logoUrl, PHP_URL_PATH);
                    $localPath = public_path($path);

                    if (file_exists($localPath)) {
                        $imageData = file_get_contents($localPath);
                        $mimeType = mime_content_type($localPath) ?: 'image/png';
                        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                    }
                }

                // Si es una URL externa, intentar descargarla con timeout corto
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'ignore_errors' => true,
                    ]
                ]);
                $imageData = @file_get_contents($logoUrl, false, $context);
                if ($imageData) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($imageData) ?: 'image/png';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }

            // Si es una ruta de storage, obtener el contenido
            if (str_contains($logoUrl, '/storage/')) {
                $path = str_replace('/storage/', '', parse_url($logoUrl, PHP_URL_PATH));
                if (Storage::disk('public')->exists($path)) {
                    $imageData = Storage::disk('public')->get($path);
                    $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/png';
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
            }

            return $logoUrl;
        } catch (\Throwable $e) {
            \Log::warning('Logo conversion failed', ['url' => $logoUrl, 'error' => $e->getMessage()]);
            return $logoUrl;
        }
    }
}
