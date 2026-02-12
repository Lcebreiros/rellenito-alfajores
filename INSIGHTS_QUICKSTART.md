# Business Insights - Guía de Instalación Rápida

## 🚀 Pasos de Instalación

### 1. Ejecutar Migración

```bash
php artisan migrate
```

Esto creará la tabla `business_insights` en la base de datos.

### 2. Probar Generación de Insights

#### Opción A: Via Comando Artisan (Recomendado para testing)

```bash
# Generar para un usuario específico (modo síncrono)
php artisan insights:generate --user=1 --sync

# Generar para todos los usuarios (asíncrono, usa queues)
php artisan insights:generate --all

# Limpiar insights antiguos y generar nuevos
php artisan insights:generate --user=1 --clear --sync
```

#### Opción B: Via API

```bash
# Generar insights (requiere autenticación)
curl -X POST http://localhost:8000/api/v1/insights/generate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"clear_existing": false}'

# Obtener insights
curl -X GET http://localhost:8000/api/v1/insights \
  -H "Authorization: Bearer YOUR_TOKEN"

# Obtener estadísticas
curl -X GET http://localhost:8000/api/v1/insights/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Opción C: Via Tinker

```bash
php artisan tinker
```

```php
// Obtener un usuario
$user = User::first();

// Generar insights manualmente
$service = app(\App\Services\Insights\InsightService::class);
$insights = $service->generateInsights($user);

// Ver resultados
$insights->count(); // Cantidad de insights generados
$insights->pluck('title'); // Títulos de los insights

// Ver insights activos
$active = $service->getInsights($user);
$active->each(fn($i) => dump($i->title, $i->priority, $i->description));

// Ver estadísticas
$stats = $service->getStats($user);
dump($stats);
```

### 3. Verificar Resultados

```bash
# Entrar a tinker
php artisan tinker
```

```php
// Ver insights generados para un usuario
use App\Models\BusinessInsight;

$user = User::first();
$insights = BusinessInsight::forUser($user->id)->active()->get();

foreach ($insights as $insight) {
    echo "\n";
    echo "🔔 {$insight->priority} - {$insight->type}\n";
    echo "   {$insight->title}\n";
    echo "   {$insight->description}\n";
}
```

## 📊 Endpoints de API

Todos los endpoints están bajo `/api/v1` y requieren autenticación Sanctum:

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/insights` | Obtener insights activos |
| GET | `/insights/stats` | Obtener estadísticas |
| POST | `/insights/generate` | Generar nuevos insights |
| PATCH | `/insights/{id}/dismiss` | Descartar un insight |

## 🔄 Generación Automática (Opcional)

Para generar insights automáticamente cada día, agrega en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Generar insights diarios a las 6 AM
    $schedule->command('insights:generate --all')
        ->daily()
        ->at('06:00')
        ->withoutOverlapping();
}
```

Asegúrate de que el cron esté configurado:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing Manual

### Escenario 1: Producto con Stock Bajo

```php
// Crear un producto con stock bajo
$user = User::first();
$product = Product::create([
    'user_id' => $user->id,
    'name' => 'Producto Test',
    'current_stock' => 2,
    'min_stock' => 10,
    'price' => 100,
]);

// Generar insights
$service = app(\App\Services\Insights\InsightService::class);
$insights = $service->generateInsights($user);

// Debería generar un insight de tipo 'stock_alert' con prioridad 'high'
$stockInsight = $insights->where('type', 'stock_alert')->first();
dump($stockInsight->title); // "📦 Stock bajo: Producto Test"
```

### Escenario 2: Crecimiento de Ventas

```php
$user = User::first();

// Crear pedidos del mes anterior (poco ingreso)
$lastMonth = now()->subMonth();
Order::create([
    'user_id' => $user->id,
    'total_price' => 1000,
    'status' => 'completed',
    'created_at' => $lastMonth,
]);

// Crear pedidos del mes actual (mucho ingreso)
for ($i = 0; $i < 5; $i++) {
    Order::create([
        'user_id' => $user->id,
        'total_price' => 1000,
        'status' => 'completed',
        'created_at' => now(),
    ]);
}

// Generar insights
$service = app(\App\Services\Insights\InsightService::class);
$insights = $service->generateInsights($user);

// Debería generar un insight de crecimiento
$revenueInsight = $insights->where('type', 'revenue_opportunity')->first();
dump($revenueInsight->title); // "📈 ¡Excelente crecimiento!"
```

## 🎯 Próximos Pasos

1. **Integrar con Mobile App**:
   - Consumir endpoints desde Flutter
   - Mostrar insights en dashboard como tarjetas deslizables
   - Implementar notificaciones push para insights críticos

2. **Personalización**:
   - Crear generadores personalizados según necesidades del negocio
   - Ajustar umbrales de prioridad en cada generador
   - Agregar más tipos de insights

3. **Mejoras**:
   - Cachear estadísticas frecuentes
   - Implementar sistema de notificaciones
   - Agregar Machine Learning para predicciones más precisas

## ❓ Troubleshooting

### No se generan insights

**Problema**: Al ejecutar `insights:generate` no se crean insights

**Solución**:
1. Verificar que el usuario tenga datos (productos, pedidos, clientes)
2. Revisar logs: `tail -f storage/logs/laravel.log`
3. Ejecutar en modo sync para ver errores: `php artisan insights:generate --user=1 --sync`

### Error de migración

**Problema**: Error al ejecutar `php artisan migrate`

**Solución**:
```bash
# Verificar conexión a BD
php artisan db:show

# Ver estado de migraciones
php artisan migrate:status

# Ejecutar solo la migración de insights
php artisan migrate --path=/database/migrations/2026_02_07_000000_create_business_insights_table.php
```

### Endpoints retornan 404

**Problema**: Las rutas de API no funcionan

**Solución**:
```bash
# Limpiar cache de rutas
php artisan route:clear

# Verificar que las rutas existan
php artisan route:list | grep insights

# Debería mostrar:
# GET|HEAD   api/v1/insights ..................... insights.index
# GET|HEAD   api/v1/insights/stats ............... insights.stats
# POST       api/v1/insights/generate ............ insights.generate
# PATCH      api/v1/insights/{id}/dismiss ........ insights.dismiss
```

## 📚 Documentación Completa

Ver [BUSINESS_INSIGHTS.md](./BUSINESS_INSIGHTS.md) para documentación detallada.

---

**¿Necesitas ayuda?** Revisa los logs en `storage/logs/laravel.log`
