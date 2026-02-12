<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_product_list_hides_sensitive_fields(): void
    {
        if (!Schema::hasTable('products')) {
            $this->markTestSkipped('Tabla products no disponible en entorno de test.');
        }

        $user = User::factory()->create();
        Product::create([
            'user_id' => $user->id,
            'company_id' => $user->id,
            'name' => 'Prod 1',
            'sku' => 'SKU1',
            'price' => 100,
            'cost_price' => 50,
            'stock' => 10,
            'min_stock' => 1,
            'is_active' => true,
            'created_by_type' => 'company',
        ]);

        Sanctum::actingAs($user, abilities: ['storefront']);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $item = $response->json('data.0');

        $this->assertArrayHasKey('price', $item);
        $this->assertArrayNotHasKey('cost_price', $item);
        $this->assertArrayNotHasKey('company_id', $item);
        $this->assertArrayNotHasKey('user_id', $item);
    }

    public function test_internal_product_list_includes_sensitive_fields(): void
    {
        if (!Schema::hasTable('products')) {
            $this->markTestSkipped('Tabla products no disponible en entorno de test.');
        }

        $user = User::factory()->create();
        Product::create([
            'user_id' => $user->id,
            'company_id' => $user->id,
            'name' => 'Prod 1',
            'sku' => 'SKU1',
            'price' => 100,
            'cost_price' => 50,
            'stock' => 10,
            'min_stock' => 1,
            'is_active' => true,
            'created_by_type' => 'company',
        ]);

        Sanctum::actingAs($user, abilities: ['*']);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $item = $response->json('data.0');

        $this->assertArrayHasKey('cost_price', $item);
        $this->assertArrayHasKey('company_id', $item);
    }

    public function test_storefront_cannot_access_internal_resources(): void
    {
        if (!Schema::hasTable('supplies')) {
            $this->markTestSkipped('Tabla supplies no disponible en entorno de test.');
        }

        $user = User::factory()->create();
        Sanctum::actingAs($user, abilities: ['storefront']);

        $this->getJson('/api/v1/supplies')->assertStatus(403);
        $this->getJson('/api/v1/expenses/summary')->assertStatus(403);
    }
}
