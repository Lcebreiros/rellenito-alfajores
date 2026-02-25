<?php

namespace App\Services\Insights\Generators;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Generador base de insights usando Template Method Pattern
 *
 * Cada generador concreto debe implementar:
 * - getType(): tipo de insight que genera
 * - shouldRun(): validación de pre-requisitos
 * - fetchData(): obtención de datos necesarios
 * - analyze(): análisis y generación de insights
 */
abstract class BaseInsightGenerator
{
    protected User $user;
    protected ?string $organizationId;

    public function __construct(User $user, ?string $organizationId = null)
    {
        $this->user = $user;
        $this->organizationId = $organizationId;
    }

    /**
     * Template Method: define el flujo general de generación
     *
     * @return Collection<int, array> insights generados
     */
    public function generate(): Collection
    {
        // Validar si este generador debe ejecutarse
        if (!$this->shouldRun()) {
            return collect();
        }

        // Obtener datos necesarios
        $data = $this->fetchData();

        // Si no hay datos, no generar insights
        if (empty($data) || (is_countable($data) && count($data) === 0)) {
            return collect();
        }

        // Analizar y generar insights
        $insights = $this->analyze($data);

        // Filtrar insights vacíos o inválidos
        return collect($insights)->filter(function ($insight) {
            return !empty($insight) &&
                   isset($insight['title']) &&
                   isset($insight['description']) &&
                   isset($insight['priority']);
        });
    }

    /**
     * Retorna el tipo de insight que genera este generador
     */
    abstract protected function getType(): string;

    /**
     * Expone el tipo públicamente para que InsightService pueda limpiar
     * los insights de este tipo antes de regenerarlos.
     */
    public function getInsightType(): string
    {
        return $this->getType();
    }

    /**
     * Determina si este generador debe ejecutarse
     * Útil para validar pre-requisitos (ej: negocio con inventario habilitado)
     */
    abstract protected function shouldRun(): bool;

    /**
     * Obtiene los datos necesarios para el análisis
     */
    abstract protected function fetchData(): mixed;

    /**
     * Analiza los datos y genera insights
     *
     * @return array insights generados
     */
    abstract protected function analyze(mixed $data): array;

    /**
     * Helper para crear la estructura de un insight
     */
    protected function makeInsight(
        string $priority,
        string $title,
        string $description,
        ?array $metadata = null,
        ?string $actionLabel = null,
        ?string $actionRoute = null,
        ?int $expirationHours = 24
    ): array {
        return [
            'type' => $this->getType(),
            'priority' => $priority,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata ?? [],
            'action_label' => $actionLabel,
            'action_route' => $actionRoute,
            'expires_at' => $expirationHours ? now()->addHours($expirationHours) : null,
        ];
    }

    /**
     * Helper para obtener productos del usuario
     */
    protected function getUserProducts()
    {
        return \App\Models\Product::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            })
            ->get();
    }

    /**
     * Helper para obtener pedidos del usuario en un rango de fechas
     */
    protected function getUserOrders($startDate = null, $endDate = null)
    {
        $query = \App\Models\Order::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            });

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Helper para obtener gastos del usuario
     * Combina ProductionExpense, ServiceExpense y SupplierExpense
     */
    protected function getUserExpenses($startDate = null, $endDate = null)
    {
        $expenses = collect();

        // Obtener gastos de producción
        $productionQuery = \App\Models\ProductionExpense::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            });

        if ($startDate) $productionQuery->where('created_at', '>=', $startDate);
        if ($endDate) $productionQuery->where('created_at', '<=', $endDate);

        // Obtener gastos de servicios
        $serviceQuery = \App\Models\ServiceExpense::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            });

        if ($startDate) $serviceQuery->where('created_at', '>=', $startDate);
        if ($endDate) $serviceQuery->where('created_at', '<=', $endDate);

        // Obtener gastos de proveedores
        $supplierQuery = \App\Models\SupplierExpense::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            });

        if ($startDate) $supplierQuery->where('created_at', '>=', $startDate);
        if ($endDate) $supplierQuery->where('created_at', '<=', $endDate);

        // Combinar todos los gastos
        $expenses = $expenses->merge($productionQuery->get())
                             ->merge($serviceQuery->get())
                             ->merge($supplierQuery->get());

        return $expenses;
    }

    /**
     * Helper para calcular el monto total de un gasto
     * Compatible con ProductionExpense, ServiceExpense y SupplierExpense
     */
    protected function getExpenseAmount($expense): float
    {
        // ServiceExpense solo tiene 'cost'
        if ($expense instanceof \App\Models\ServiceExpense) {
            return (float) $expense->cost;
        }

        // ProductionExpense y SupplierExpense tienen accessor 'total_cost'
        if (isset($expense->total_cost)) {
            return (float) $expense->total_cost;
        }

        // Fallback: intentar calcular manualmente
        if (isset($expense->cost) && isset($expense->quantity)) {
            return (float) ($expense->cost * $expense->quantity);
        }

        if (isset($expense->cost_per_unit) && isset($expense->quantity)) {
            return (float) ($expense->cost_per_unit * $expense->quantity);
        }

        if (isset($expense->cost)) {
            return (float) $expense->cost;
        }

        return 0;
    }

    /**
     * Helper para obtener clientes del usuario
     */
    protected function getUserClients()
    {
        return \App\Models\Client::where('user_id', $this->user->id)
            ->when($this->organizationId, function ($query) {
                $query->where('organization_id', $this->organizationId);
            })
            ->get();
    }
}
