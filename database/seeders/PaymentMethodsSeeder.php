<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Métodos de pago predeterminados
        $defaultPaymentMethods = [
            [
                'name' => 'Efectivo',
                'slug' => 'cash',
                'icon' => 'banknotes',
                'description' => 'Pago en efectivo',
                'is_active' => true,
                'requires_gateway' => false,
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 1,
            ],
            [
                'name' => 'Transferencia',
                'slug' => 'transferencia',
                'icon' => 'arrows-right-left',
                'description' => 'Transferencia bancaria o CBU/CVU',
                'is_active' => true,
                'requires_gateway' => false,
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 2,
            ],
            [
                'name' => 'MercadoPago',
                'slug' => 'mercadopago',
                'icon' => 'device-phone-mobile',
                'description' => 'Pago con MercadoPago (QR, link, etc.)',
                'is_active' => true,
                'requires_gateway' => true,
                'gateway_config' => null,
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
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 4,
            ],
            [
                'name' => 'Visa Débito',
                'slug' => 'visa-debito',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Visa débito',
                'is_active' => true,
                'requires_gateway' => false,
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 5,
            ],
            [
                'name' => 'Mastercard',
                'slug' => 'mastercard',
                'icon' => 'credit-card',
                'description' => 'Tarjeta Mastercard',
                'is_active' => true,
                'requires_gateway' => false,
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 6,
            ],
            [
                'name' => 'PayPal',
                'slug' => 'paypal',
                'icon' => 'currency-dollar',
                'description' => 'Pago con PayPal',
                'is_active' => true,
                'requires_gateway' => true,
                'gateway_config' => null,
                'gateway_provider' => 'paypal',
                'sort_order' => 7,
            ],
            [
                'name' => 'Cuenta DNI',
                'slug' => 'cuenta-dni',
                'icon' => 'identification',
                'description' => 'Pago con Cuenta DNI del Banco Provincia',
                'is_active' => true,
                'requires_gateway' => false,
                'gateway_config' => null,
                'gateway_provider' => null,
                'sort_order' => 9,
            ],
        ];

        // Obtener todos los usuarios que tienen rol de vendedor o admin
        $users = User::whereIn('role', ['seller', 'admin'])->get();

        foreach ($users as $user) {
            foreach ($defaultPaymentMethods as $method) {
                // Verificar si ya existe este método para este usuario
                $exists = PaymentMethod::where('user_id', $user->id)
                    ->where('slug', $method['slug'])
                    ->exists();

                if (!$exists) {
                    PaymentMethod::create([
                        'user_id' => $user->id,
                        ...$method
                    ]);

                    $this->command->info("✓ Creado método '{$method['name']}' para usuario {$user->name} (ID: {$user->id})");
                } else {
                    $this->command->warn("⊗ Método '{$method['name']}' ya existe para usuario {$user->name} (ID: {$user->id})");
                }
            }
        }

        $this->command->info('');
        $this->command->info('✓ Seeder de métodos de pago completado!');
    }
}
