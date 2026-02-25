<?php

namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;
use Carbon\Carbon;

/**
 * Generador de insights de retención de clientes
 *
 * Identifica clientes inactivos y patrones de compra
 */
class ClientRetentionGenerator extends BaseInsightGenerator
{
    protected function getType(): string
    {
        return BusinessInsight::TYPE_CLIENT_RETENTION;
    }

    protected function shouldRun(): bool
    {
        return $this->getUserClients()->count() > 0;
    }

    protected function fetchData(): mixed
    {
        $clients = $this->getUserClients();
        $last90DaysOrders = $this->getUserOrders(now()->subDays(90))
            ->where('status', '!=', 'cancelled')
            ->load('client');

        return [
            'clients' => $clients,
            'orders' => $last90DaysOrders,
        ];
    }

    protected function analyze(mixed $data): array
    {
        $insights = [];
        $clients = $data['clients'];
        $orders = $data['orders'];

        // Agrupar pedidos por cliente
        $ordersByClient = [];
        foreach ($orders as $order) {
            if (!$order->client_id) continue;

            $clientId = $order->client_id;
            if (!isset($ordersByClient[$clientId])) {
                $ordersByClient[$clientId] = [];
            }
            $ordersByClient[$clientId][] = $order;
        }

        // Analizar clientes inactivos
        $inactiveClients = [];
        foreach ($clients as $client) {
            $clientOrders = $ordersByClient[$client->id] ?? [];

            if (empty($clientOrders)) {
                // Cliente sin pedidos en 90 días
                $inactiveClients[] = $client;
            } else {
                // Verificar última compra
                $lastOrder = collect($clientOrders)->sortByDesc('created_at')->first();
                $daysSinceLastOrder = now()->diffInDays($lastOrder->created_at);

                if ($daysSinceLastOrder > 60) {
                    $inactiveClients[] = [
                        'client' => $client,
                        'days_since_last_order' => $daysSinceLastOrder,
                    ];
                }
            }
        }

        // Generar insight de clientes inactivos
        if (count($inactiveClients) > 0) {
            $count = count($inactiveClients);
            $priority = $count > 10 ? BusinessInsight::PRIORITY_MEDIUM : BusinessInsight::PRIORITY_LOW;

            $insights[] = $this->makeInsight(
                $priority,
                "Clientes inactivos",
                "Tienes {$count} cliente(s) que no han comprado en más de 60 días. Considera contactarlos con una promoción.",
                [
                    'inactive_count' => $count,
                    'inactive_clients' => array_slice($inactiveClients, 0, 10), // Limitar a 10
                ],
                'Ver clientes',
                '/clients',
                72
            );
        }

        // Analizar clientes frecuentes
        $frequentClients = [];
        foreach ($ordersByClient as $clientId => $clientOrders) {
            if (count($clientOrders) >= 5) {
                $client = $clients->firstWhere('id', $clientId);
                $totalSpent = collect($clientOrders)->sum('total_price');

                $frequentClients[] = [
                    'client' => $client,
                    'order_count' => count($clientOrders),
                    'total_spent' => $totalSpent,
                ];
            }
        }

        if (count($frequentClients) > 0) {
            // Ordenar por gasto total
            usort($frequentClients, fn($a, $b) => $b['total_spent'] <=> $a['total_spent']);
            $topClient = $frequentClients[0];

            $insights[] = $this->makeInsight(
                BusinessInsight::PRIORITY_LOW,
                "Clientes frecuentes",
                "Tienes " . count($frequentClients) . " cliente(s) frecuente(s). " .
                $topClient['client']->name . " es tu mejor cliente con " .
                $topClient['order_count'] . " pedido(s).",
                [
                    'vip_count' => count($frequentClients),
                    'top_client' => $topClient,
                ],
                'Ver clientes',
                '/clients',
                168 // 7 días
            );
        }

        // Analizar tasa de retención
        $totalClients = $clients->count();
        $activeClients = count($ordersByClient);
        $retentionRate = $totalClients > 0 ? ($activeClients / $totalClients) * 100 : 0;

        if ($retentionRate < 30 && $totalClients >= 10) {
            $insights[] = $this->makeInsight(
                BusinessInsight::PRIORITY_MEDIUM,
                "Baja retención de clientes",
                "Solo el " . round($retentionRate, 1) . "% de tus clientes han comprado en los últimos 90 días. " .
                "Considera implementar un programa de fidelización.",
                [
                    'total_clients' => $totalClients,
                    'active_clients' => $activeClients,
                    'retention_rate' => round($retentionRate, 2),
                ],
                'Ver estadísticas',
                '/clients',
                96
            );
        }

        return $insights;
    }
}
