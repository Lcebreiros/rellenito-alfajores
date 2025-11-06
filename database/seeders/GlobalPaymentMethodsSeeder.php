<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class GlobalPaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Efectivo',
                'slug' => 'efectivo',
                'icon' => 'banknotes',
                'description' => 'Pago en efectivo al momento de la entrega o retiro',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Transferencia',
                'slug' => 'transferencia',
                'icon' => 'arrows-right-left',
                'description' => 'Transferencia bancaria (CBU/CVU/Alias)',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'Visa',
                'slug' => 'visa',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Visa (crédito)',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 3,
            ],
            [
                'name' => 'Visa Débito',
                'slug' => 'visa-debito',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Visa Débito',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 4,
            ],
            [
                'name' => 'Mercado Pago',
                'slug' => 'mercadopago',
                'icon' => 'device-phone-mobile',
                'description' => 'Pago con Mercado Pago (QR, Link, Tarjetas)',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => true,
                'gateway_provider' => 'mercadopago',
                'gateway_config' => [
                    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', ''),
                    'public_key' => env('MERCADOPAGO_PUBLIC_KEY', ''),
                ],
                'sort_order' => 5,
            ],
            [
                'name' => 'Mastercard',
                'slug' => 'mastercard',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Mastercard',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 6,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'icon' => 'credit-card',
                'description' => 'Pago con PayPal',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => true,
                'gateway_provider' => 'paypal',
                'gateway_config' => [
                    'client_id' => env('PAYPAL_CLIENT_ID', ''),
                    'secret' => env('PAYPAL_SECRET', ''),
                    'mode' => env('PAYPAL_MODE', 'sandbox'),
                ],
                'sort_order' => 7,
            ],
            [
                'name' => 'Cuenta DNI',
                'slug' => 'cuenta-dni',
                'icon' => 'identification',
                'description' => 'Transferencia a Cuenta DNI del Banco Provincia',
                'is_active' => true,
                'is_global' => true,
                'requires_gateway' => false,
                'gateway_provider' => null,
                'gateway_config' => null,
                'sort_order' => 8,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug'], 'user_id' => null],
                $method
            );
        }
    }
}
