# Costs/Expenses Implementation - Quick Reference

## Directory Structure

```
app/
├── Http/Controllers/
│   ├── ExpenseController.php          (Main expense management)
│   ├── ProductCostController.php      (Cost analysis & recipes)
│   └── CalculatorController.php       (Cost calculator interface)
├── Models/
│   ├── SupplierExpense.php
│   ├── ServiceExpense.php
│   ├── ThirdPartyService.php
│   ├── ProductionExpense.php
│   ├── Supply.php
│   ├── SupplyPurchase.php
│   └── Costing.php
└── Services/
    └── CostService.php                (Cost calculation logic)

resources/views/
├── expenses/
│   ├── index.blade.php                (Main dashboard with 5 card tiles)
│   ├── suppliers.blade.php
│   ├── services.blade.php
│   ├── third-party.blade.php
│   ├── production.blade.php
│   └── supplies.blade.php
├── livewire/dashboard/
│   └── revenue-widget.blade.php       (Dashboard cost chart)
└── costing/
    └── calculator.blade.php            (Cost calculator interface)

database/migrations/
└── 2025_11_10_200001_create_expense_tables.php
```

## Key File Paths (Absolute)

### Views
- `/home/leandro/rellenito-alfajores/resources/views/expenses/index.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/expenses/suppliers.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/expenses/services.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/expenses/third-party.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/expenses/production.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/expenses/supplies.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/livewire/dashboard/revenue-widget.blade.php`
- `/home/leandro/rellenito-alfajores/resources/views/costing/calculator.blade.php`

### Controllers
- `/home/leandro/rellenito-alfajores/app/Http/Controllers/ExpenseController.php`
- `/home/leandro/rellenito-alfajores/app/Http/Controllers/ProductCostController.php`
- `/home/leandro/rellenito-alfajores/app/Http/Controllers/CalculatorController.php`

### Models
- `/home/leandro/rellenito-alfajores/app/Models/SupplierExpense.php`
- `/home/leandro/rellenito-alfajores/app/Models/ServiceExpense.php`
- `/home/leandro/rellenito-alfajores/app/Models/ThirdPartyService.php`
- `/home/leandro/rellenito-alfajores/app/Models/ProductionExpense.php`
- `/home/leandro/rellenito-alfajores/app/Models/Supply.php`
- `/home/leandro/rellenito-alfajores/app/Models/Costing.php`

### Services
- `/home/leandro/rellenito-alfajores/app/Services/CostService.php`

### Database
- `/home/leandro/rellenito-alfajores/database/migrations/2025_11_10_200001_create_expense_tables.php`

## Route Map

```
/expenses                               → expenses.index (Dashboard)
├── /expenses/suppliers                 → expenses.suppliers (CRUD)
├── /expenses/services                  → expenses.services (CRUD)
├── /expenses/third-party               → expenses.third-party (CRUD)
├── /expenses/production                → expenses.production (CRUD + Calculator link)
└── /expenses/supplies                  → expenses.supplies (CRUD + Edit modal)

/costing/calculator                     → calculator.show (Calculator interface with tabs)
```

## Data Flow Diagram

```
User Creates/Updates Expense
         ↓
   ExpenseController method
         ↓
   Model (SupplierExpense, etc.)
         ↓
   Database Table
         ↓
   View/Table Display
         ↓
   User Sees Updated Data
```

## Expense Types at a Glance

| Type | Model | Has Frequency | Calculation | Link To |
|------|-------|---------------|-------------|---------|
| Supplier | SupplierExpense | Yes | qty × cost × freq_multiplier | Product |
| Service | ServiceExpense | No | cost (fixed) | Service |
| Third-Party | ThirdPartyService | Yes | cost × freq_multiplier | None |
| Production | ProductionExpense | No | qty × cost_per_unit | Product |
| Supply | Supply | No | stock × avg_cost | - |

## Frequency Multipliers

```
'unica'    → 1       (one-time)
'diaria'   → 365     (daily)
'semanal'  → 52      (weekly)
'mensual'  → 12      (monthly)
'anual'    → 1       (annual)
```

## Model Relationships

```
SupplierExpense
├── belongsTo(Product)
├── belongsTo(User)
└── uses: BelongsToUser, SoftDeletes

ServiceExpense
├── belongsTo(Service)
├── belongsTo(User)
└── uses: BelongsToUser, SoftDeletes

ThirdPartyService
├── belongsTo(User)
└── uses: BelongsToUser, SoftDeletes

ProductionExpense
├── belongsTo(Product)
├── belongsTo(User)
└── uses: BelongsToUser, SoftDeletes

Supply
├── hasMany(SupplyPurchase)
├── belongsTo(User)
└── uses: BelongsToUser

Costing
├── belongsTo(Product)
└── uses: BelongsToUser
```

## API Response Examples

### storeAnalysis() / analyses() Response
```json
{
  "ok": true,
  "costing": {
    "id": 123,
    "source": "recipe",
    "yield_units": 100,
    "unit_total": 5.50,
    "batch_total": 550.00,
    "lines": [
      {
        "id": null,
        "name": "Flour",
        "base_unit": "g",
        "per_unit_qty": 50,
        "per_unit_cost": 0.10,
        "perc": 45.45
      }
    ],
    "created_at": "2025-11-11T12:00:00.000000Z"
  }
}
```

## CostService Methods

### productCost()
```php
$cost = CostService::productCost($product);
// Returns: ['batch_cost' => float, 'unit_cost' => float, 'yield' => int]
```

### suggestPrice()
```php
$price = CostService::suggestPrice($unitCost, 0.60); // 60% margin
// Returns: float (suggested selling price)
```

## Common Tasks

### Add a New Supplier Expense
1. POST to `/expenses/suppliers`
2. Required: supplier_name, cost, quantity, unit, frequency
3. Optional: product_id, description
4. Model saves with BelongsToUser auto-filling user_id

### List All Expenses
1. GET `/expenses`
2. Controller calculates totals for each type
3. Returns view with 5 summary cards and quick links

### Calculate Product Cost
1. GET `/products/{product}/cost`
2. CostService iterates through recipe items
3. Applies waste % and unit conversions
4. Returns batch cost and unit cost

### Track Supply Purchases
1. POST `/expenses/supplies` with qty, unit, total_cost
2. Creates SupplyPurchase record
3. Calls recomputeFromPurchases()
4. Updates Supply stock_base_qty and avg_cost_per_base

### View Cost Dashboard
1. GET `/dashboard` (includes RevenueWidget)
2. RevenueWidget pulls daily costs and revenue
3. Displays as stacked bar chart (CSS Grid based)
4. Updates every 30 seconds via Livewire poll

## Important Notes

1. **Multi-Tenant**: All models filter by user_id automatically via BelongsToUser trait
2. **Soft Deletes**: All expenses can be recovered (except supplies)
3. **No External Chart Library**: Uses custom CSS Grid for dashboard widget
4. **Unit Conversion**: Supply purchases support g/kg, ml/l/cm3 conversions
5. **Accessors**: Calculations are done via accessors (not stored in DB)
6. **Cost Calculator**: Separate interface with Livewire components for advanced costing

## Recent Commits

1. `5e7b3b6` - ahora recibe los insumos en producto y servicio
2. `4729b04` - ahora la calculadora manda los calculos directo a los productos y a gastos de produccion
3. `48debeb` - solucionado bug no se veian los insumos en gastos
4. `b617780` - Add expense tables and service supplies functionality

## Testing Endpoints

```bash
# Get all expenses
curl GET /expenses

# Create supplier expense
curl POST /expenses/suppliers \
  -d "supplier_name=ABC&cost=100&quantity=1&unit=unidad&frequency=mensual"

# Get expense detail
curl GET /expenses/suppliers

# Update expense
curl PUT /expenses/suppliers/1 \
  -d "supplier_name=ABC Updated&cost=120&quantity=1&unit=unidad&frequency=mensual"

# Delete expense
curl DELETE /expenses/suppliers/1

# Calculate cost
curl GET /products/1/cost

# Save analysis
curl POST /costings \
  -d "source=recipe&yield_units=100&unit_total=5.50&batch_total=550&lines=[...]"
```

