<?php

namespace App\Services;

use App\Models\ParkingStay;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio de impresión térmica DIRECTA (sin servidor Node.js)
 *
 * Usa la librería mike42/escpos-php para comunicarse directamente
 * con la impresora USB sin necesidad de servidor intermediario.
 *
 * MUCHO MÁS SIMPLE para configurar y usar.
 */
class DirectThermalPrinterService
{
    private $enabled;
    private $printerPath;
    private $isWindows;

    public function __construct()
    {
        $this->enabled = config('thermal_printer.direct.enabled', true);
        $this->printerPath = config('thermal_printer.direct.printer_path');
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Imprime un ticket de parking en la impresora térmica
     * Solo se imprime al INGRESO (al egreso se escanea el ticket)
     */
    public function printParkingTicket(ParkingStay $stay): bool
    {
        if (!$this->enabled) {
            Log::info('Impresión térmica directa deshabilitada en configuración');
            return false;
        }

        try {
            $printer = $this->connectToPrinter();

            if (!$printer) {
                Log::error('No se pudo conectar a la impresora');
                return false;
            }

            // Preparar datos del ticket
            $ticketService = new ParkingTicketService();
            $ticketData = $ticketService->generateTicketData($stay);
            $ticketData['business_name'] = config('parking.business_name', 'Estacionamiento Moreno S.R.L.');

            // Imprimir ticket
            $this->printTicket($printer, $ticketData);

            // Cerrar conexión
            $printer->close();

            Log::info('Ticket impreso correctamente (directo)', [
                'stay_id' => $stay->id,
                'license_plate' => $stay->license_plate,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Error al imprimir ticket directo', [
                'error' => $e->getMessage(),
                'stay_id' => $stay->id ?? null,
            ]);
            return false;
        }
    }

    /**
     * Conecta con la impresora térmica
     */
    private function connectToPrinter(): ?Printer
    {
        try {
            if ($this->isWindows) {
                // Windows: usar nombre de impresora compartida
                if (empty($this->printerPath)) {
                    // Intentar encontrar impresora por defecto
                    $this->printerPath = $this->findDefaultPrinter();
                }

                $connector = new WindowsPrintConnector($this->printerPath);
            } else {
                // Linux: priorizar CUPS si está disponible
                $cupsName = $this->findCupsPrinter();

                if ($cupsName) {
                    // Usar CUPS (más compatible y robusto)
                    Log::info('Conectando vía CUPS', ['printer' => $cupsName]);
                    $connector = new CupsPrintConnector($cupsName);
                } else {
                    // Fallback: usar archivo de dispositivo USB directo
                    if (empty($this->printerPath)) {
                        $this->printerPath = $this->findLinuxPrinterPath();
                    }

                    Log::info('Conectando vía dispositivo USB', ['path' => $this->printerPath]);
                    $connector = new FilePrintConnector($this->printerPath);
                }
            }

            return new Printer($connector);

        } catch (Exception $e) {
            Log::error('Error al conectar con impresora', [
                'error' => $e->getMessage(),
                'path' => $this->printerPath ?? 'N/A',
            ]);
            return null;
        }
    }

    /**
     * Imprime el contenido del ticket
     */
    private function printTicket(Printer $printer, array $ticketData): void
    {
        // Configurar impresora
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        // Encabezado
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text($ticketData['business_name'] . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        $printer->text(str_repeat("=", 42) . "\n\n");

        // Datos del vehículo
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text($this->formatLine('Patente:', $ticketData['license_plate']));
        $printer->text($this->formatLine('Vehiculo:', $ticketData['vehicle_type']));
        $printer->text($this->formatLine('Cochera:', $ticketData['space_name']));
        $printer->text("\n");
        $printer->text($this->formatLine('Fecha:', $ticketData['entry_at']));
        $printer->text("\n");

        // Código de barras
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        if (isset($ticketData['barcode'])) {
            try {
                $printer->setBarcodeHeight(50);
                $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
                $printer->barcode($ticketData['barcode'], Printer::BARCODE_CODE39);
            } catch (Exception $e) {
                // Si falla el código de barras, imprimir solo el número
                $printer->text("\n" . $ticketData['barcode'] . "\n");
            }
        }

        $printer->text("\n");
        $printer->text(str_repeat("=", 42) . "\n");

        // Pie de página
        $printer->text("Conserve este ticket\n");
        $printer->text("Gracias por su visita\n");

        // Cortar papel
        $printer->feed(3);
        $printer->cut();
    }

    /**
     * Formatea una línea con etiqueta y valor
     */
    private function formatLine(string $label, string $value): string
    {
        $width = 42;
        $totalLen = strlen($label) + strlen($value);

        if ($totalLen >= $width) {
            return $label . ' ' . $value . "\n";
        }

        $spaces = $width - $totalLen;
        return $label . str_repeat(' ', $spaces) . $value . "\n";
    }

    /**
     * Encuentra la impresora por defecto en Windows
     */
    private function findDefaultPrinter(): string
    {
        // Nombres comunes de impresoras térmicas
        $commonNames = [
            'POS-80',
            'POS-58',
            'Thermal Printer',
            'Receipt Printer',
            'TM-T20',
            'XP-80C',
        ];

        foreach ($commonNames as $name) {
            if (file_exists("\\\\localhost\\{$name}")) {
                return $name;
            }
        }

        // Por defecto, asumir que hay una compartida llamada "ThermalPrinter"
        return 'ThermalPrinter';
    }

    /**
     * Encuentra la impresora en CUPS (Linux)
     */
    private function findCupsPrinter(): ?string
    {
        // Si el usuario especificó un nombre, usarlo
        if (!empty($this->printerPath)) {
            return $this->printerPath;
        }

        try {
            // Intentar obtener la impresora predeterminada
            exec('lpstat -d 2>/dev/null', $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                foreach ($output as $line) {
                    if (preg_match('/destino predeterminado del sistema:\s*(.+)/', $line, $matches)) {
                        return trim($matches[1]);
                    }
                    if (preg_match('/system default destination:\s*(.+)/', $line, $matches)) {
                        return trim($matches[1]);
                    }
                }
            }

            // Si no hay predeterminada, buscar cualquier impresora térmica
            exec('lpstat -a 2>/dev/null', $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                // Buscar impresoras con nombres comunes de térmicas
                foreach ($output as $line) {
                    if (preg_match('/^(Thermal|ThermalPrinter|POS|EPSON|TM-T)/i', $line)) {
                        $parts = explode(' ', $line);
                        return $parts[0];
                    }
                }

                // Si no encuentra ninguna con nombre conocido, usar la primera disponible
                if (!empty($output)) {
                    $parts = explode(' ', $output[0]);
                    return $parts[0];
                }
            }
        } catch (Exception $e) {
            Log::warning('No se pudo detectar impresora CUPS', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Encuentra el path de la impresora en Linux
     */
    private function findLinuxPrinterPath(): string
    {
        // Rutas comunes de dispositivos USB en Linux
        $commonPaths = [
            '/dev/usb/lp0',
            '/dev/usb/lp1',
            '/dev/lp0',
            '/dev/lp1',
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_writable($path)) {
                return $path;
            }
        }

        // Por defecto usar /dev/usb/lp0
        return '/dev/usb/lp0';
    }

    /**
     * Verifica si la impresora está disponible
     */
    public function isAvailable(): bool
    {
        try {
            $printer = $this->connectToPrinter();
            if ($printer) {
                $printer->close();
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Imprime un ticket de prueba
     */
    public function printTest(): bool
    {
        try {
            $printer = $this->connectToPrinter();

            if (!$printer) {
                return false;
            }

            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("TICKET DE PRUEBA\n");
            $printer->setEmphasis(false);
            $printer->text("\n");
            $printer->text("Impresora funcionando correctamente\n");
            $printer->text("\n");
            $printer->text(date('d/m/Y H:i:s') . "\n");
            $printer->feed(3);
            $printer->cut();

            $printer->close();

            return true;

        } catch (Exception $e) {
            Log::error('Error al imprimir ticket de prueba', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
