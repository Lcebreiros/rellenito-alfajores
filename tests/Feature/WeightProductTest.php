<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeightProductTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function makeProduct(User $user, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'user_id'          => $user->id,
            'created_by_type'  => 'App\\Models\\User',
            'name'             => 'Test Product',
            'sku'              => 'TEST-' . uniqid(),
            'unit'             => 'kg',
            'price'            => 3000,
            'cost_price'       => 0,
            'yield_units'      => 1,
            'stock'            => 10,
            'uses_stock'       => true,
            'min_stock'        => 0,
            'is_active'        => true,
            'is_shared'        => false,
        ], $attrs));
    }

    private function makeDraft(User $user): Order
    {
        return Order::create([
            'user_id'        => $user->id,
            'branch_id'      => $user->id,
            'company_id'     => $user->id,
            'status'         => 'draft',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'discount'       => 0,
            'tax_amount'     => 0,
        ]);
    }

    public function test_add_half_kg_to_order(): void
    {
        $user    = $this->makeUser();
        $this->actingAs($user);
        $product = $this->makeProduct($user, ['unit' => 'kg', 'price' => 3000]);
        $order   = $this->makeDraft($user);

        app(OrderService::class)->addItem($order->id, $product->id, 0.5);

        $snap = app(OrderService::class)->snapshot($order->id);
        $this->assertCount(1, $snap['items']);
        $this->assertEqualsWithDelta(0.5,    $snap['items'][0]['qty'],     0.001);
        $this->assertEqualsWithDelta(1500.0, $snap['items'][0]['subtotal'], 0.01);
        $this->assertEquals('kg', $snap['items'][0]['unit']);
    }

    public function test_adding_same_weight_product_accumulates(): void
    {
        $user    = $this->makeUser();
        $this->actingAs($user);
        $product = $this->makeProduct($user, ['unit' => 'kg', 'price' => 3000]);
        $order   = $this->makeDraft($user);

        $svc = app(OrderService::class);
        $svc->addItem($order->id, $product->id, 0.5); // 500 g
        $svc->addItem($order->id, $product->id, 0.3); // 300 g

        $snap = $svc->snapshot($order->id);
        $this->assertCount(1, $snap['items']);
        $this->assertEqualsWithDelta(0.8,    $snap['items'][0]['qty'],     0.001);
        $this->assertEqualsWithDelta(2400.0, $snap['items'][0]['subtotal'], 0.01);
    }

    public function test_set_item_quantity_float(): void
    {
        $user    = $this->makeUser();
        $this->actingAs($user);
        $product = $this->makeProduct($user, ['unit' => 'g', 'price' => 3]);
        $order   = $this->makeDraft($user);

        $svc = app(OrderService::class);
        $svc->addItem($order->id, $product->id, 500.0);

        $snap = $svc->snapshot($order->id);
        $itemId = $snap['items'][0]['id'];

        $svc->setItemQuantity($order->id, $itemId, 750.0);

        $snap2 = $svc->snapshot($order->id);
        $this->assertEqualsWithDelta(750.0,   $snap2['items'][0]['qty'],     0.001);
        $this->assertEqualsWithDelta(2250.0,  $snap2['items'][0]['subtotal'], 0.01);
    }

    public function test_unit_products_still_work_as_integers(): void
    {
        $user    = $this->makeUser();
        $this->actingAs($user);
        $product = $this->makeProduct($user, ['unit' => 'u', 'price' => 100, 'stock' => 5]);
        $order   = $this->makeDraft($user);

        app(OrderService::class)->addItem($order->id, $product->id, 3);

        $snap = app(OrderService::class)->snapshot($order->id);
        $this->assertEqualsWithDelta(3.0,   $snap['items'][0]['qty'],     0.001);
        $this->assertEqualsWithDelta(300.0, $snap['items'][0]['subtotal'], 0.01);
        $this->assertEquals('u', $snap['items'][0]['unit']);
    }
}
