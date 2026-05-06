<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->string('document_number')->nullable();
                $table->string('company')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('province')->nullable();
                $table->string('country')->nullable();
                $table->json('tags')->nullable();
                $table->text('notes')->nullable();
                $table->decimal('balance', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('created_by_type', 10)->default('company');
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('category', 100)->nullable();
                $table->string('unit', 20)->default('u');
                $table->string('sku');
                $table->string('barcode', 64)->nullable();
                $table->string('image')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('cost_price', 10, 2)->default(0);
                $table->unsignedInteger('yield_units')->default(1);
                $table->unsignedInteger('stock')->default(0);
                $table->decimal('min_stock', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_shared')->default(false);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['user_id', 'sku']);
            });
        }

        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->foreignId('product_id')->constrained()->onDelete('cascade');
                $table->integer('quantity_change');
                $table->integer('new_stock')->default(0);
                $table->string('reason')->nullable();
                $table->string('reference_type')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('status', 20)->default('draft');
                $table->boolean('is_scheduled')->default(false);
                $table->timestamp('cancelled_at')->nullable();
                $table->text('cancel_reason')->nullable();
                $table->string('order_number', 50)->nullable();
                $table->decimal('total', 12, 2)->default(0);
                $table->string('payment_method', 20)->default('cash');
                $table->string('payment_status', 20)->default('pending');
                $table->text('notes')->nullable();
                $table->timestamp('sold_at')->nullable();
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('tax_amount', 10, 2)->default(0);
                $table->timestamp('scheduled_for')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->unsignedBigInteger('service_id')->nullable();
                $table->decimal('quantity', 10, 3)->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->timestamps();
                $table->unique(['order_id', 'product_id', 'service_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('products');
        Schema::dropIfExists('services');
        Schema::dropIfExists('clients');
    }
};
