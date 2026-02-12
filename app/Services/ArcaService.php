<?php

namespace App\Services;

use App\Models\ArcaConfiguration;
use App\Models\Invoice;
use Carbon\Carbon;
use SoapClient;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArcaService
{
    protected $config;
    protected $token;
    protected $sign;

    public function __construct(ArcaConfiguration $config)
    {
        $this->config = $config;
    }

    /**
     * Obtener token y sign de autorización
     */
    protected function getAuth()
    {
        if ($this->token && $this->sign) {
            return true;
        }

        // Intentar leer de cache (token/sign + expiración)
        $cacheKey = 'arca_auth_' . $this->config->company_id . '_' . $this->config->environment;
        $cached = Cache::get($cacheKey);
        if ($cached && !empty($cached['token']) && !empty($cached['sign']) && !empty($cached['expires_at'])) {
            $expiresAt = Carbon::parse($cached['expires_at']);
            if ($expiresAt->isFuture()) {
                $this->token = $cached['token'];
                $this->sign  = $cached['sign'];
                return true;
            }
        }

        try {
            $wsaaUrl = $this->config->environment === 'production'
                ? 'https://wsaa.afip.gov.ar/ws/services/LoginCms?wsdl'
                : 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl';

            // Crear TRA (Ticket de Requerimiento de Acceso)
            $tra = $this->createTRA();

            // Firmar TRA con certificado y clave privada
            $cms = $this->signTRA($tra);

            // Llamar a WSAA para obtener token y sign
            $client = new SoapClient($wsaaUrl, [
                'soap_version' => SOAP_1_2,
                'location' => $wsaaUrl,
                'trace' => 1,
                'exceptions' => 0
            ]);

            $results = $client->loginCms(['in0' => $cms]);

            if (is_soap_fault($results)) {
                throw new Exception("Error WSAA: " . $results->faultstring);
            }

            // Parsear respuesta XML
            $xml = simplexml_load_string($results->loginCmsReturn);

            $this->token = (string) $xml->credentials->token;
            $this->sign = (string) $xml->credentials->sign;

            // Guardar en cache con expiración (10 min antes de expirar)
            $exp = Carbon::parse((string) $xml->header->expirationTime)->subMinutes(10);
            Cache::put($cacheKey, [
                'token' => $this->token,
                'sign' => $this->sign,
                'expires_at' => $exp->toIso8601String(),
            ], $exp);

            return true;
        } catch (Exception $e) {
            throw new Exception("Error obteniendo autorización ARCA: " . $e->getMessage());
        }
    }

    /**
     * Crear TRA (Ticket de Requerimiento de Acceso)
     */
    protected function createTRA()
    {
        $now = Carbon::now();
        $tra = '<?xml version="1.0" encoding="UTF-8"?>
<loginTicketRequest version="1.0">
<header>
    <uniqueId>' . time() . '</uniqueId>
    <generationTime>' . $now->format('c') . '</generationTime>
    <expirationTime>' . $now->addHours(12)->format('c') . '</expirationTime>
</header>
<service>wsfe</service>
</loginTicketRequest>';

        return $tra;
    }

    /**
     * Firmar TRA con certificado y clave privada
     */
    protected function signTRA($tra)
    {
        // Guardar TRA temporalmente
        $traFile = storage_path('app/temp/tra.xml');
        $cmsFile = storage_path('app/temp/tra.cms');
        $certFile = storage_path('app/temp/cert.pem');
        $keyFile  = storage_path('app/temp/key.pem');

        if (!is_dir(dirname($traFile))) {
            mkdir(dirname($traFile), 0755, true);
        }

        try {
            file_put_contents($traFile, $tra);
            file_put_contents($certFile, $this->config->certificate);
            file_put_contents($keyFile, $this->config->private_key);

            // Firmar con OpenSSL
            $password = $this->config->certificate_password;
            $key = $password
                ? openssl_pkey_get_private(file_get_contents($keyFile), $password)
                : file_get_contents($keyFile);

            $cert = file_get_contents($certFile);

            openssl_pkcs7_sign(
                $traFile,
                $cmsFile,
                $cert,
                $key,
                [],
                !PKCS7_DETACHED
            );

            // Leer CMS generado
            $cms = file_get_contents($cmsFile);

            // Extraer solo la parte firmada (sin headers)
            $cms = preg_replace('/^.+\n\n/', '', $cms);
            $cms = preg_replace('/\n.+$/', '', $cms);

            return $cms;
        } finally {
            @unlink($traFile);
            @unlink($cmsFile);
            @unlink($certFile);
            @unlink($keyFile);
        }
    }

    /**
     * Obtener próximo número de comprobante
     */
    public function getNextVoucherNumber($voucherType, $salePoint)
    {
        try {
            $this->getAuth();

            $client = new SoapClient($this->config->getEnvironmentUrl(), [
                'soap_version' => SOAP_1_2,
                'location' => $this->config->getEnvironmentUrl(),
                'trace' => 1,
                'exceptions' => 0
            ]);

        $params = [
            'Auth' => [
                'Token' => $this->token,
                'Sign' => $this->sign,
                'Cuit' => $this->config->cuit
                ],
                'PtoVta' => $salePoint,
                'CbteTipo' => $this->getVoucherTypeCode($voucherType)
            ];

            $results = $client->FECompUltimoAutorizado($params);

            if (is_soap_fault($results)) {
                throw new Exception("Error consultando último comprobante: " . $results->faultstring);
            }

            return (int) $results->FECompUltimoAutorizadoResult->CbteNro + 1;
        } catch (Exception $e) {
            throw new Exception("Error obteniendo próximo número: " . $e->getMessage());
        }
    }

    /**
     * Enviar factura a ARCA y obtener CAE
     */
    public function sendInvoice(Invoice $invoice)
    {
        try {
            $this->getAuth();

            // Obtener próximo número si no tiene
            if (!$invoice->voucher_number || $invoice->voucher_number == 0) {
                $invoice->voucher_number = $this->getNextVoucherNumber(
                    $invoice->voucher_type,
                    $invoice->sale_point
                );
                $invoice->save();
            }

            $client = new SoapClient($this->config->getEnvironmentUrl(), [
                'soap_version' => SOAP_1_2,
                'location' => $this->config->getEnvironmentUrl(),
                'trace' => 1,
                'exceptions' => 0
            ]);

            // Preparar datos del comprobante
            $invoiceData = $this->prepareInvoiceData($invoice);

            $params = [
                'Auth' => [
                    'Token' => $this->token,
                    'Sign' => $this->sign,
                    'Cuit' => $this->config->cuit
                ],
                'FeCAEReq' => [
                    'FeCabReq' => [
                        'CantReg' => 1,
                        'PtoVta' => $invoice->sale_point,
                        'CbteTipo' => $this->getVoucherTypeCode($invoice->voucher_type)
                    ],
                    'FeDetReq' => [
                        'FECAEDetRequest' => $invoiceData
                    ]
                ]
            ];

            // Validar totales antes de enviar
            $this->validateTotals($invoiceData);

            // Enviar a ARCA
            $results = $client->FECAESolicitar($params);

            if (is_soap_fault($results)) {
                throw new Exception("Error en solicitud CAE: " . $results->faultstring);
            }

            // Procesar respuesta
            return $this->processResponse($invoice, $results);
        } catch (Exception $e) {
            throw new Exception("Error enviando factura a ARCA: " . $e->getMessage());
        }
    }

    /**
     * Validar coherencia de totales antes de enviar
     */
    protected function validateTotals(array $invoiceData): void
    {
        $impTotal   = (float) $invoiceData['ImpTotal'];
        $impNeto    = (float) $invoiceData['ImpNeto'];
        $impOpEx    = (float) $invoiceData['ImpOpEx'];
        $impIVA     = (float) $invoiceData['ImpIVA'];
        $impTrib    = (float) $invoiceData['ImpTrib'];
        $impTotConc = (float) $invoiceData['ImpTotConc'];

        $calc = $impTotConc + $impOpEx + $impNeto + $impIVA + $impTrib;
        $delta = round($calc - $impTotal, 2);
        if (abs($delta) > 0.05) {
            Log::warning('Inconsistencia de totales ARCA', [
                'calc' => $calc,
                'impTotal' => $impTotal,
                'data' => $invoiceData,
            ]);
            throw new Exception("Los totales de la factura no cierran (delta {$delta}). Revisá importes e IVA.");
        }
    }

    /**
     * Preparar datos de la factura para ARCA
     */
    protected function prepareInvoiceData(Invoice $invoice)
    {
        // Determinar concepto según items: si tienen product_id => productos, si no => servicios
        $hasProducts = $invoice->items()->whereNotNull('product_id')->exists();
        $hasServices = $invoice->items()->whereNull('product_id')->exists();
        $conceptCode = ($hasProducts && $hasServices) ? 3 : ($hasServices ? 2 : 1);

        // Documento
        $docNumber = 0;
        $docType = 99; // sin identificar
        if (!empty($invoice->client_cuit)) {
            $docType = 80;
            $docNumber = preg_replace('/[^0-9]/', '', $invoice->client_cuit);
        } elseif (!empty($invoice->client_tax_id)) { // si guardas DNI en otro campo, ajusta
            $docType = 96;
            $docNumber = preg_replace('/[^0-9]/', '', $invoice->client_tax_id);
        }

        return [
            'Concepto' => $conceptCode,
            'DocTipo' => $docType,
            'DocNro' => $docNumber,
            'CbteDesde' => $invoice->voucher_number,
            'CbteHasta' => $invoice->voucher_number,
            'CbteFch' => Carbon::parse($invoice->invoice_date)->format('Ymd'),
            'ImpTotal' => number_format($invoice->total, 2, '.', ''),
            'ImpTotConc' => number_format($invoice->untaxed_amount, 2, '.', ''),
            'ImpNeto' => number_format($invoice->taxed_amount, 2, '.', ''),
            'ImpOpEx' => number_format($invoice->exempt_amount, 2, '.', ''),
            'ImpIVA' => number_format($invoice->tax_amount, 2, '.', ''),
            'ImpTrib' => '0.00',
            'MonId' => 'PES', // Pesos argentinos
            'MonCotiz' => 1,
            'Iva' => $this->prepareIvaData($invoice)
        ];
    }

    /**
     * Preparar datos de IVA
     */
    protected function prepareIvaData(Invoice $invoice)
    {
        $ivaGroups = [];

        foreach ($invoice->items as $item) {
            $rate = (float) $item->tax_rate;

            if ($rate == 0) continue;

            $key = (int) $rate;

            if (!isset($ivaGroups[$key])) {
                $ivaGroups[$key] = [
                    'baseImponible' => 0,
                    'importe' => 0
                ];
            }

            $subtotal = $item->quantity * $item->unit_price - $item->discount_amount;
            $ivaGroups[$key]['baseImponible'] += $subtotal;
            $ivaGroups[$key]['importe'] += $subtotal * ($rate / 100);
        }

        $ivaArray = [];
        foreach ($ivaGroups as $rate => $data) {
            $ivaArray[] = [
                'AlicIva' => [
                    'Id' => $this->getIvaCode($rate),
                    'BaseImp' => number_format($data['baseImponible'], 2, '.', ''),
                    'Importe' => number_format($data['importe'], 2, '.', '')
                ]
            ];
        }

        return $ivaArray ?: null;
    }

    /**
     * Procesar respuesta de ARCA
     */
    protected function processResponse(Invoice $invoice, $results)
    {
        $response = $results->FECAESolicitarResult;

        // Guardar respuesta completa
        $invoice->arca_response = json_encode($response);
        $invoice->arca_observations = null;
        $invoice->arca_errors = null;

        // Verificar errores
        if (isset($response->Errors)) {
            $errors = is_array($response->Errors->Err) ? $response->Errors->Err : [$response->Errors->Err];
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[] = $error->Code . ': ' . $error->Msg;
            }

            $invoice->status = 'rejected';
            $invoice->arca_errors = implode(', ', $errorMessages);
            $invoice->save();

            throw new Exception("ARCA rechazó la factura: " . implode(', ', $errorMessages));
        }

        // Obtener resultado
        $result = $response->FeDetResp->FECAEDetResponse;

        if ($result->Resultado == 'A') {
            // Aprobado
            $invoice->cae = $result->CAE;
            $invoice->cae_expiration = Carbon::createFromFormat('Ymd', $result->CAEFchVto);
            $invoice->status = 'approved';
            $invoice->save();

            return [
                'success' => true,
                'cae' => $result->CAE,
                'cae_expiration' => $result->CAEFchVto,
                'message' => 'Factura aprobada por ARCA'
            ];
        } elseif ($result->Resultado == 'P') {
            // Pendiente
            $invoice->status = 'pending';
            $invoice->arca_observations = 'Pendiente de autorización';
            $invoice->save();
            return [
                'success' => false,
                'pending' => true,
                'message' => 'Factura pendiente de autorización en ARCA'
            ];
        } else {
            // Rechazado
            $invoice->status = 'rejected';
            $invoice->save();

            $observations = '';
            if (isset($result->Observaciones)) {
                $obs = is_array($result->Observaciones->Obs) ? $result->Observaciones->Obs : [$result->Observaciones->Obs];
                foreach ($obs as $o) {
                    $observations .= $o->Code . ': ' . $o->Msg . ' ';
                }
            }
            $invoice->arca_observations = trim($observations);

            throw new Exception("Factura rechazada: " . $observations);
        }
    }

    /**
     * Obtener código de tipo de comprobante para ARCA
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

        return $codes[$voucherType] ?? 6; // Default: Factura B
    }

    /**
     * Obtener código de alícuota IVA para ARCA
     */
    protected function getIvaCode($rate)
    {
        $codes = [
            0 => 3,      // 0%
            10.5 => 4,   // 10.5%
            21 => 5,     // 21%
            27 => 6      // 27%
        ];

        return $codes[(float)$rate] ?? 5; // Default: 21%
    }

    /**
     * Consultar comprobante en ARCA
     */
    public function queryInvoice(Invoice $invoice)
    {
        try {
            $this->getAuth();

            $client = new SoapClient($this->config->getEnvironmentUrl(), [
                'soap_version' => SOAP_1_2,
                'location' => $this->config->getEnvironmentUrl(),
                'trace' => 1,
                'exceptions' => 0
            ]);

            $params = [
                'Auth' => [
                    'Token' => $this->token,
                    'Sign' => $this->sign,
                    'Cuit' => $this->config->cuit
                ],
                'FeCompConsReq' => [
                    'CbteTipo' => $this->getVoucherTypeCode($invoice->voucher_type),
                    'PtoVta' => $invoice->sale_point,
                    'CbteNro' => $invoice->voucher_number
                ]
            ];

            $results = $client->FECompConsultar($params);

            if (is_soap_fault($results)) {
                throw new Exception("Error consultando comprobante: " . $results->faultstring);
            }

            return $results->FECompConsultarResult;
        } catch (Exception $e) {
            throw new Exception("Error consultando factura: " . $e->getMessage());
        }
    }
}
