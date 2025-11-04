<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las empresas (hierarchy_level = 0)
        $companies = User::where('hierarchy_level', User::HIERARCHY_COMPANY)->get();

        // Métodos de pago populares en Argentina
        $paymentMethods = [
            [
                'name' => 'Efectivo',
                'slug' => 'cash',
                'icon' => 'banknotes',
                'description' => 'Pago en efectivo',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Transferencia',
                'slug' => 'transferencia',
                'icon' => 'arrows-right-left',
                'description' => 'Transferencia bancaria o CBU/CVU',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'MercadoPago',
                'slug' => 'mercadopago',
                'icon' => 'device-phone-mobile',
                'description' => 'Pago con MercadoPago (QR, link, etc.)',
                'is_active' => true,
                'requires_gateway' => true,
                'gateway_provider' => 'mercadopago',
                'sort_order' => 3,
            ],
            [
                'name' => 'Visa',
                'slug' => 'visa',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Visa crédito',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Visa Débito',
                'slug' => 'visa-debito',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Visa débito',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Mastercard',
                'slug' => 'mastercard',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Mastercard',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Cuenta DNI',
                'slug' => 'cuenta-dni',
                'icon' => 'identification',
                'description' => 'Cuenta DNI del Banco Provincia',
                'is_active' => true,
                'requires_gateway' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'icon' => 'currency-dollar',
                'description' => 'Pago con PayPal',
                'is_active' => true,
                'requires_gateway' => true,
                'gateway_provider' => 'paypal',
                'sort_order' => 8,
            ],
        ];

        // Si no hay empresas, usar el primer usuario que encontremos o crear métodos sin user_id
        if ($companies->isEmpty()) {
            $firstUser = User::first();
            if ($firstUser) {
                foreach ($paymentMethods as $method) {
                    PaymentMethod::create(array_merge($method, ['user_id' => $firstUser->id]));
                }
            }
        } else {
            // Crear métodos de pago para cada empresa
            foreach ($companies as $company) {
                foreach ($paymentMethods as $method) {
                    PaymentMethod::firstOrCreate(
                        [
                            'user_id' => $company->id,
                            'slug' => $method['slug'],
                        ],
                        $method
                    );
                }
            }
        }
    }
}
