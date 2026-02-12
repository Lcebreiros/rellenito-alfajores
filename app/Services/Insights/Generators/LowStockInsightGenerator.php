<?php

namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;
use Carbon\Carbon;

/**
 * Generador de insights de stock bajo
 *
 * Analiza productos con stock crítico y genera alertas
 * basadas en el historial de ventas
 */
class LowStockInsightGenerator extends BaseInsightGenerator
{
    protected function getType(): string
    {
        return BusinessInsight::TYPE_STOCK_ALERT;
    }

    protected function shouldRun(): bool
    {
        // Solo ejecutar si el usuario tiene productos
        return $this->getUserProducts()->count() > 0;
    }

    protected function fetchData(): mixed
    {
        // Obtener productos con stock
        $products = $this->getUserProducts()
            ->filter(fn($p) => isset($p->current_stock));

        if ($products->isEmpty()) {
            return [];
        }

        // Obtener ventas de los últimos 30 días para calcular promedios
        $startDate = now()->subDays(30);
        $orders = $this->getUserOrders($startDate)
            ->where('status', '!=', 'cancelled')
            ->load('items');

        return [
            'products' => $products,
            'orders' => $orders,
        ];
    }

    protected function analyze(mixed $data): array
    {
        $insights = [];
        $products = $data['products'];
        $orders = $data['orders'];

        // Calcular ventas por producto
        $salesByProduct = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (!isset($salesByProduct[$item->product_id])) {
                    $salesByProduct[$item->product_id] = 0;
                }
                $salesByProduct[$item->product_id] += $item->quantity;
            }
        }

        foreach ($products as $product) {
            $currentStock = $product->current_stock ?? 0;
            $minStock = $product->min_stock ?? 5;

            // Calcular tasa de venta diaria
            $productId = $product->id;
            $totalSold = $salesByProduct[$productId] ?? 0;
            $dailySalesRate = $totalSold / 30; // promedio de últimos 30 días

            // Calcular días hasta quedarse sin stock
            $daysUntilEmpty = $dailySalesRate > 0
                ? $currentStock / $dailySalesRate
                : 999;

            // Generar insights según criticidad
            if ($currentStock <= 0) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_CRITICAL,
                    "⚠️ Sin stock: {$product->name}",
                    "El producto {$product->name} no tiene stock disponible. Es necesario realizar un pedido urgente.",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $currentStock,
                        'min_stock' => $minStock,
                        'daily_sales' => round($dailySalesRate, 2),
                    ],
                    'Hacer pedido',
                    "/products/{$product->id}",
                    48
                );
            } elseif ($currentStock < $minStock) {
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_HIGH,
                    "📦 Stock bajo: {$product->name}",
                    "El producto {$product->name} tiene solo {$currentStock} unidades. Mínimo recomendado: {$minStock}.",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $currentStock,
                        'min_stock' => $minStock,
                        'daily_sales' => round($dailySalesRate, 2),
                    ],
                    'Revisar stock',
                    "/products/{$product->id}",
                    24
                );
            } elseif ($daysUntilEmpty <= 7 && $daysUntilEmpty > 0) {
                $daysRounded = ceil($daysUntilEmpty);
                $insights[] = $this->makeInsight(
                    BusinessInsight::PRIORITY_MEDIUM,
                    "⏱️ Stock limitado: {$product->name}",
                    "Al ritmo actual de ventas, {$product->name} se agotará en aproximadamente {$daysRounded} días.",
                    [
                        'product_id' => $product->id,
                        'current_stock' => $currentStock,
                        'days_until_empty' => $daysRounded,
                        'daily_sales' => round($dailySalesRate, 2),
                    ],
                    'Ver detalles',
                    "/products/{$product->id}",
                    48
                );
            }
        }

        return $insights;
    }
}
