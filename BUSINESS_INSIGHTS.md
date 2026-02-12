# Sistema de Business Insights

Sistema inteligente de análisis y sugerencias para negocios, que proporciona insights accionables basados en datos del negocio.

## 📋 Características

- **Alertas de Stock**: Notifica productos con stock bajo o agotado, con predicciones de días hasta agotamiento
- **Oportunidades de Ingreso**: Identifica tendencias de crecimiento, productos estrella y horarios pico
- **Advertencias de Costos**: Detecta aumentos anormales en gastos y problemas de margen de ganancia
- **Retención de Clientes**: Identifica clientes inactivos y clientes VIP frecuentes

## 🏗️ Arquitectura

### Estructura de Archivos

```
app/
├── Models/
│   └── BusinessInsight.php                  # Modelo de Eloquent
├── Services/
│   └── Insights/
│       ├── InsightService.php              # Servicio principal
│       └── Generators/
│           ├── BaseInsightGenerator.php    # Generador base (Template Method)
│           ├── LowStockInsightGenerator.php
│           ├── RevenueOpportunityGenerator.php
│           ├── CostWarningGenerator.php
│           └── ClientRetentionGenerator.php
├── Http/
│   └── Controllers/
│       └── Api/
│           └── InsightController.php       # Controller de API
└── Jobs/
    └── GenerateBusinessInsights.php        # Job asíncrono

database/
└── migrations/
    └── 2026_02_07_000000_create_business_insights_table.php

routes/
└── api.php                                 # Rutas de API
```

### Patrones de Diseño Utilizados

1. **Template Method Pattern**: `BaseInsightGenerator` define el flujo general, cada generador implementa su lógica específica
2. **Service Layer Pattern**: `InsightService` encapsula la lógica de negocio
3. **Repository Pattern**: Eloquent actúa como repository con scopes personalizados
4. **Dependency Injection**: Controllers reciben servicios via constructor

## 🔌 API Endpoints

### 1. Obtener Insights Activos

```http
GET /api/v1/insights
```

**Query Params:**
- `type` (opcional): Filtrar por tipo (`stock_alert`, `revenue_opportunity`, `cost_warning`, `client_retention`)
- `priority` (opcional): Filtrar por prioridad (`critical`, `high`, `medium`, `low`)
- `limit` (opcional): Limitar resultados (default: 10, max: 50)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "stock_alert",
      "priority": "critical",
      "title": "⚠️ Sin stock: Producto X",
      "description": "El producto X no tiene stock disponible...",
      "metadata": {
        "product_id": 42,
        "current_stock": 0,
        "min_stock": 10
      },
      "action_label": "Hacer pedido",
      "action_route": "/products/42",
      "is_dismissed": false,
      "created_at": "2026-02-07T10:00:00Z",
      "expires_at": "2026-02-09T10:00:00Z",
      "priority_color": "#EF4444",
      "type_icon": "inventory"
    }
  ]
}
```

### 2. Generar Nuevos Insights

```http
POST /api/v1/insights/generate
```

**Body:**
```json
{
  "clear_existing": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Insights generados exitosamente",
  "data": {
    "count": 5,
    "insights": [...]
  }
}
```

### 3. Descartar un Insight

```http
PATCH /api/v1/insights/{id}/dismiss
```

**Response:**
```json
{
  "success": true,
  "message": "Insight descartado exitosamente",
  "data": {
    "id": 1,
    "is_dismissed": true
  }
}
```

### 4. Obtener Estadísticas

```http
GET /api/v1/insights/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 12,
    "by_type": {
      "stock_alert": 5,
      "revenue_opportunity": 3,
      "cost_warning": 2,
      "client_retention": 2
    },
    "by_priority": {
      "critical": 2,
      "high": 4,
      "medium": 3,
      "low": 3
    },
    "by_type_and_priority": [...]
  }
}
```

## 📊 Tipos de Insights

### Stock Alert (stock_alert)

Analiza el inventario y genera alertas basadas en:
- Stock actual vs stock mínimo
- Historial de ventas (últimos 30 días)
- Predicción de días hasta agotamiento

**Prioridades:**
- `critical`: Sin stock (0 unidades)
- `high`: Por debajo del mínimo
- `medium`: Se agotará en 7 días o menos

### Revenue Opportunity (revenue_opportunity)

Identifica oportunidades de crecimiento:
- Comparación de ingresos mes actual vs mes anterior
- Productos más rentables (top 3)
- Horarios pico de ventas

**Prioridades:**
- `low`: Información positiva (crecimiento, productos estrella)
- `medium`: Baja en ventas significativa (>10%)

### Cost Warning (cost_warning)

Detecta problemas de costos:
- Aumento anormal en gastos (>30%)
- Margen de ganancia bajo (<15%)
- Gastos concentrados en una categoría (>50%)

**Prioridades:**
- `critical`: Gastos superan ingresos
- `high`: Aumento de gastos >30%
- `medium`: Margen bajo o concentración de gastos

### Client Retention (client_retention)

Analiza comportamiento de clientes:
- Clientes sin compras en 60+ días
- Clientes frecuentes (5+ pedidos en 90 días)
- Tasa de retención general

**Prioridades:**
- `medium`: Baja retención (<30%) o muchos inactivos
- `low`: Identificación de clientes VIP

## 🚀 Instalación

### 1. Ejecutar Migración

```bash
php artisan migrate
```

Esto creará la tabla `business_insights` con:
- Campos principales: type, priority, title, description
- Metadata en formato JSON
- Índices optimizados para queries frecuentes
- Soft expiration (expires_at)

### 2. Verificar Rutas

Las rutas se agregaron automáticamente en `routes/api.php`:

```php
// GET /api/v1/insights
// GET /api/v1/insights/stats
// POST /api/v1/insights/generate
// PATCH /api/v1/insights/{id}/dismiss
```

### 3. (Opcional) Configurar Generación Automática

En `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generar insights diariamente para todos los usuarios
    $schedule->call(function () {
        \App\Models\User::chunk(100, function ($users) {
            foreach ($users as $user) {
                \App\Jobs\GenerateBusinessInsights::dispatch($user, null, true);
            }
        });
    })->daily()->at('06:00');
}
```

O generar manualmente:

```php
use App\Jobs\GenerateBusinessInsights;

// Generar para un usuario específico
GenerateBusinessInsights::dispatch($user);

// Generar y limpiar insights previos
GenerateBusinessInsights::dispatch($user, null, true);
```

## 💡 Uso Programático

### Generar Insights

```php
use App\Services\Insights\InsightService;

$insightService = app(InsightService::class);

// Generar insights para un usuario
$insights = $insightService->generateInsights(
    user: $user,
    organizationId: null,
    clearExisting: false
);

// Resultado: Collection de BusinessInsight models
```

### Obtener Insights

```php
// Obtener todos los insights activos
$insights = $insightService->getInsights($user);

// Con filtros
$insights = $insightService->getInsights($user, [
    'type' => 'stock_alert',
    'priority' => 'critical',
    'limit' => 5,
]);
```

### Descartar un Insight

```php
$insight = $insightService->dismissInsight($insightId, $user);
```

### Estadísticas

```php
$stats = $insightService->getStats($user);
// Retorna: ['total', 'by_type', 'by_priority', 'by_type_and_priority']
```

## 🔧 Extensibilidad

### Crear un Generador Personalizado

```php
namespace App\Services\Insights\Generators;

use App\Models\BusinessInsight;

class CustomInsightGenerator extends BaseInsightGenerator
{
    protected function getType(): string
    {
        return BusinessInsight::TYPE_CUSTOM; // Agregar tipo al modelo
    }

    protected function shouldRun(): bool
    {
        // Validar pre-requisitos
        return true;
    }

    protected function fetchData(): mixed
    {
        // Obtener datos necesarios
        return $this->getUserOrders(now()->subDays(30));
    }

    protected function analyze(mixed $data): array
    {
        $insights = [];

        // Lógica de análisis
        if ($someCondition) {
            $insights[] = $this->makeInsight(
                priority: BusinessInsight::PRIORITY_HIGH,
                title: "Título del insight",
                description: "Descripción detallada",
                metadata: ['key' => 'value'],
                actionLabel: "Ver detalles",
                actionRoute: "/route",
                expirationHours: 24
            );
        }

        return $insights;
    }
}
```

### Registrar Generador

```php
use App\Services\Insights\InsightService;

$insightService = app(InsightService::class);
$insightService->addGenerator(CustomInsightGenerator::class);
```

## 🎨 Integración con Frontend

### Colores de Prioridad

```dart
// Flutter
Color getPriorityColor(String priority) {
  return switch (priority) {
    'critical' => Color(0xFFEF4444), // red
    'high' => Color(0xFFF59E0B),     // orange
    'medium' => Color(0xFF3B82F6),   // blue
    'low' => Color(0xFF10B981),      // green
    _ => Color(0xFF6B7280),          // gray
  };
}
```

### Iconos de Tipo

```dart
IconData getTypeIcon(String type) {
  return switch (type) {
    'stock_alert' => Icons.inventory,
    'revenue_opportunity' => Icons.trending_up,
    'cost_warning' => Icons.warning,
    'trend' => Icons.show_chart,
    'client_retention' => Icons.people,
    'prediction' => Icons.psychology,
    'reminder' => Icons.notifications,
    _ => Icons.lightbulb,
  };
}
```

## 📝 Notas de Implementación

### Performance

- Los insights se generan de forma asíncrona via Jobs
- Índices optimizados en la tabla para queries frecuentes
- Soft expiration: insights expirados no se borran inmediatamente
- Generadores se ejecutan en paralelo, pero si uno falla los demás continúan

### Seguridad

- Todos los endpoints requieren autenticación Sanctum
- Rate limiting aplicado (100 req/min para lectura, 30 para escritura)
- Los insights solo son visibles para su propietario (user_id)
- Validación de inputs con Laravel Validation

### Limpieza

Los insights antiguos pueden limpiarse:

```php
// Limpiar insights descartados o expirados hace 30+ días
$insightService->clearOldInsights($user, null, 30);

// Expirar insights no descartados con 7+ días de antigüedad
$insightService->expireOldInsights($user, 7);
```

## 🐛 Troubleshooting

### No se generan insights

1. Verificar que el usuario tenga datos (productos, pedidos, clientes)
2. Revisar logs: `storage/logs/laravel.log`
3. Verificar que los generadores retornan datos en `fetchData()`

### Insights duplicados

Usar `clearExisting: true` al generar:

```php
GenerateBusinessInsights::dispatch($user, null, true);
```

### Performance lento

- Verificar índices de la tabla `business_insights`
- Usar `limit` en las queries de API
- Considerar cachear estadísticas

## 📚 Referencias

- Template Method Pattern: https://refactoring.guru/design-patterns/template-method
- Laravel Jobs: https://laravel.com/docs/queues
- Laravel Eloquent: https://laravel.com/docs/eloquent

---

**Versión**: 1.0.0
**Última actualización**: 2026-02-07
**Autor**: Claude Sonnet 4.5
