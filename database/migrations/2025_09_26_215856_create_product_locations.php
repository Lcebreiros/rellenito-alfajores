<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id'); // Sin ->index() porque la foreign key ya lo crea
            $table->decimal('stock', 14, 3)->default(0);
            $table->decimal('min_stock', 14, 3)->default(0);
            $table->decimal('reserved_stock', 14, 3)->default(0); // Para stock reservado en órdenes
            $table->timestamps();

            // Índices
            $table->unique(['product_id', 'branch_id']);
            $table->index(['product_id', 'branch_id']); // Índice compuesto para queries
            
            // Foreign key constraint para branch_id (usuarios tipo ADMIN)
            $table->foreign('branch_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Migración de datos existentes
        $this->migrateExistingStock();
    }

    /**
     * Migrar stock existente desde products a product_locations
     */
    private function migrateExistingStock(): void
    {
        try {
            // Obtener productos con stock
            $products = DB::table('products')
                ->select('id', 'user_id', 'stock', 'min_stock', 'branch_id')
                ->whereNotNull('stock')
                ->orWhereNotNull('min_stock')
                ->get();

            foreach ($products as $product) {
                $branchId = $this->resolveBranchId($product);
                
                // Solo crear registro si hay stock o min_stock > 0
                if (($product->stock ?? 0) > 0 || ($product->min_stock ?? 0) > 0) {
                    DB::table('product_locations')->insertOrIgnore([
                        'product_id' => $product->id,
                        'branch_id' => $branchId,
                        'stock' => max(0, $product->stock ?? 0),
                        'min_stock' => max(0, $product->min_stock ?? 0),
                        'reserved_stock' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            \Log::info('product_locations: Migración de stock completada para ' . $products->count() . ' productos');
            
        } catch (\Throwable $e) {
            \Log::error('product_locations: Error en migración de stock: ' . $e->getMessage());
            // No lanzar excepción para no interrumpir la migración
        }
    }

    /**
     * Resolver el branch_id correcto para un producto
     */
    private function resolveBranchId($product): int
    {
        // 1. Si el producto tiene branch_id explícito, usarlo
        if (!empty($product->branch_id)) {
            return $product->branch_id;
        }

        // 2. Determinar branch_id basado en la jerarquía del usuario dueño
        $user = DB::table('users')->where('id', $product->user_id)->first();
        
        if (!$user) {
            throw new \Exception("Usuario no encontrado para producto ID: {$product->id}");
        }

        // Si el usuario es ADMIN (sucursal), usar su ID
        if ($user->hierarchy_level == 1) { // User::HIERARCHY_ADMIN
            return $user->id;
        }

        // Si es usuario regular, buscar su sucursal (parent con hierarchy_level = 1)
        if ($user->hierarchy_level == 2 && $user->parent_id) { // User::HIERARCHY_USER
            $parent = DB::table('users')->where('id', $user->parent_id)->first();
            if ($parent && $parent->hierarchy_level == 1) {
                return $parent->id;
            }
        }

        // Si es company, buscar la primera sucursal o crear una por defecto
        if ($user->hierarchy_level == 0) { // User::HIERARCHY_COMPANY
            $firstBranch = DB::table('users')
                ->where('parent_id', $user->id)
                ->where('hierarchy_level', 1)
                ->first();
                
            if ($firstBranch) {
                return $firstBranch->id;
            }
        }

        // Fallback: usar el user_id del producto (aunque no sea ideal)
        \Log::warning("product_locations: No se pudo resolver branch_id para producto {$product->id}, usando user_id como fallback");
        return $product->user_id;
    }

    public function down(): void
    {
        Schema::dropIfExists('product_locations');
    }
};