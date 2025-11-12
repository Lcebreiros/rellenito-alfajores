<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Supplier;
use App\Models\SupplierExpense;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna supplier_id (nullable por ahora)
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
        });

        // 2. Migrar datos: crear Suppliers a partir de supplier_names únicos
        $expenses = SupplierExpense::whereNull('supplier_id')->get();
        $supplierCache = [];

        foreach ($expenses as $expense) {
            $supplierName = trim($expense->supplier_name);
            $userId = $expense->user_id;
            $cacheKey = $userId . '_' . strtolower($supplierName);

            // Si ya creamos este proveedor para este usuario, reutilizarlo
            if (!isset($supplierCache[$cacheKey])) {
                // Buscar si ya existe un proveedor con ese nombre para este usuario
                $supplier = Supplier::where('user_id', $userId)
                    ->where('name', $supplierName)
                    ->first();

                if (!$supplier) {
                    // Crear nuevo proveedor
                    $supplier = Supplier::create([
                        'user_id' => $userId,
                        'name' => $supplierName,
                        'is_active' => true,
                    ]);
                }

                $supplierCache[$cacheKey] = $supplier->id;
            }

            // Actualizar el expense con el supplier_id
            $expense->supplier_id = $supplierCache[$cacheKey];
            $expense->save();
        }

        // 3. Hacer supplier_id required y eliminar supplier_name
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->dropColumn('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar supplier_name
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('supplier_id');
        });

        // Copiar nombres de suppliers de vuelta
        $expenses = SupplierExpense::with('supplier')->get();
        foreach ($expenses as $expense) {
            if ($expense->supplier) {
                $expense->supplier_name = $expense->supplier->name;
                $expense->save();
            }
        }

        // Eliminar supplier_id
        Schema::table('supplier_expenses', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
