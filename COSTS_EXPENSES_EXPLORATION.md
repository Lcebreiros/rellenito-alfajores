# Costs/Expenses View Implementation Exploration

## Overview
The application has a comprehensive costs/expenses management system with multiple types of expenses, models, views, and routes organized in a clean, modular architecture.

---

## 1. Main Costs/Expenses View Component

### Primary Location
- **Main Entry View**: `/home/leandro/rellenito-alfajores/resources/views/expenses/index.blade.php`
- **Controller**: `/home/leandro/rellenito-alfajores/app/Http/Controllers/ExpenseController.php`
- **Route**: `expenses.index` → GET `/expenses`

### Index Page Features
The main expenses dashboard displays:

1. **Summary Cards Grid** (5 cards showing totals):
   - Proveedores (Supplier Expenses) - Blue icon
   - Servicios (Service Expenses) - Green icon
   - Terceros (Third-party Services) - Purple icon
   - Producción (Production Expenses) - Orange icon
   - Insumos (Supplies/Materials) - Amber icon

2. **Quick Access Cards** (5 cards linking to detail views):
   - Each card shows count of items in that category
   - Cards are clickable to navigate to detailed management views

### Layout
- Uses Tailwind CSS with responsive grid (1 col on mobile, up to 5 cols on XL screens)
- Dark mode support throughout
- Color-coded by expense type for visual organization

---

## 2. Types of Costs/Expenses

### A. Supplier Expenses (Gastos de Proveedores)
- **Model**: `App\Models\SupplierExpense`
- **View**: `/resources/views/expenses/suppliers.blade.php`
- **Route**: `expenses.suppliers` → GET/POST `/expenses/suppliers`
- **Fields**:
  - `supplier_name` - Name of supplier
  - `cost` - Base cost amount
  - `quantity` - Quantity purchased
  - `unit` - Unit of measurement
  - `frequency` - Payment frequency (única, diaria, semanal, mensual, anual)
  - `product_id` - Optional link to product
  - `description` - Additional notes
  - `is_active` - Boolean status flag
- **Key Calculations**:
  - `total_cost` = cost × quantity (accessor)
  - `annualized_cost` = total_cost × frequency_multiplier (accessor)
    - Multipliers: unica=1, diaria=365, semanal=52, mensual=12, anual=1

### B. Service Expenses (Gastos de Servicios)
- **Model**: `App\Models\ServiceExpense`
- **View**: `/resources/views/expenses/services.blade.php`
- **Route**: `expenses.services` → GET/POST `/expenses/services`
- **Fields**:
  - `expense_name` - Name of service expense
  - `cost` - Total cost amount
  - `expense_type` - Type classification (material, mano_obra, herramienta, otro)
  - `service_id` - Optional link to service
  - `description` - Additional notes
  - `is_active` - Boolean status flag
- **Note**: Service expenses are fixed costs, not frequency-based

### C. Third-Party Services (Servicios de Terceros)
- **Model**: `App\Models\ThirdPartyService`
- **View**: `/resources/views/expenses/third-party.blade.php`
- **Route**: `expenses.third-party` → GET/POST `/expenses/third-party`
- **Fields**:
  - `service_name` - Service name (e.g., "Hosting web")
  - `cost` - Cost amount
  - `frequency` - Payment frequency (única, diaria, semanal, mensual, anual)
  - `provider_name` - Provider name
  - `next_payment_date` - Optional payment date tracking
  - `description` - Additional notes
  - `is_active` - Boolean status flag
- **Key Calculations**:
  - `annualized_cost` = cost × frequency_multiplier (accessor)

### D. Production Expenses (Gastos de Producción)
- **Model**: `App\Models\ProductionExpense`
- **View**: `/resources/views/expenses/production.blade.php`
- **Route**: `expenses.production` → GET/POST `/expenses/production`
- **Fields**:
  - `expense_name` - Name of production expense
  - `cost_per_unit` - Cost per individual unit
  - `quantity` - Quantity units
  - `unit` - Unit name (unidad, kg, litro, hora, etc.)
  - `product_id` - Optional link to product
  - `description` - Additional notes
  - `is_active` - Boolean status flag
- **Key Calculations**:
  - `total_cost` = cost_per_unit × quantity (accessor)
- **Note**: Provides quick access to Cost Calculator tool

### E. Supplies/Insumos (Inventory Items)
- **Model**: `App\Models\Supply`
- **View**: `/resources/views/expenses/supplies.blade.php`
- **Route**: `expenses.supplies` → GET/POST `/expenses/supplies`
- **Fields**:
  - `name` - Supply name
  - `description` - Description
  - `base_unit` - Base unit type (g, ml, u)
  - `stock_base_qty` - Stock in base units
  - `avg_cost_per_base` - Average cost per base unit
- **Related Model**: `SupplyPurchase`
  - Tracks individual purchases with unit conversions
  - Recalculates average cost automatically
- **Key Features**:
  - Supports unit conversion (kg→g, l→ml, etc.)
  - Automatic stock tracking
  - Integrated with recipe/product costing
  - Can initialize with first purchase when created

---

## 3. Current Display Implementation

### Display Format: Tables
All expense views use a consistent table layout:

#### Supplier Expenses Table Columns:
1. Proveedor (Supplier name)
2. Producto (Optional product link)
3. Costo (Base cost)
4. Cantidad (Quantity)
5. Frecuencia (Payment frequency)
6. Costo Anual (Annualized cost)
7. Estado (Active/Inactive status)
8. Acciones (Delete action)

#### Production Expenses Table Columns:
1. Gasto (Expense name)
2. Producto (Optional product link)
3. Costo/Unidad (Cost per unit)
4. Cantidad (Quantity)
5. Total (Total cost)
6. Estado (Active/Inactive status)
7. Acciones (Delete action)

#### Service Expenses Table Columns:
1. Gasto (Expense name)
2. Servicio (Optional service link)
3. Tipo (Expense type - with formatted display)
4. Costo (Cost amount)
5. Estado (Active/Inactive status)
6. Acciones (Delete action)

#### Third-Party Services Table Columns:
1. Servicio (Service name)
2. Proveedor (Provider name)
3. Costo (Cost amount)
4. Frecuencia (Frequency)
5. Costo Anual (Annualized cost)
6. Próximo Pago (Next payment date)
7. Estado (Active/Inactive status)
8. Acciones (Delete action)

#### Supplies Table Columns:
1. Nombre (Supply name with description)
2. Unidad Base (Base unit - G, ML, U)
3. Stock (Current stock with unit)
4. Costo Promedio (Average cost per base unit)
5. Valor Total (Total stock value)
6. Estado (In Stock/Sin Stock status)
7. Acciones (Edit/Delete actions)
- **Footer**: Total value in stock summary

### Form Inputs
Each expense type has a dedicated form:
- Input fields with validation (required, numeric, email, etc.)
- Dropdown selectors for categories/frequencies
- Text areas for descriptions
- All using Tailwind CSS styled inputs with dark mode support
- Visual feedback with success/error messages

### Status Indicators
- Active/Inactive status shown as colored badges
- Color coding: Emerald for active, Neutral for inactive
- Dark mode variants for all colors

---

## 4. Chart/Graph Components

### Current Chart Implementation
Located in: `/home/leandro/rellenito-alfajores/resources/views/livewire/dashboard/revenue-widget.blade.php`

**RevenueWidget Component** (`@livewire('dashboard.revenue-widget')`):
- Displays Revenue, Costs, and Gross Profit metrics
- Shows costs in rose/red color scheme
- Shows revenue in emerald/green color scheme
- Displays metrics for configurable day ranges (wire:poll.30s)

**Chart Type**: Simple bar chart (custom CSS grid-based)
- Uses CSS Grid with dynamic column template
- Each day shown as vertical stacked bars
- Responsive heights based on percentage of max value
- No external charting library used
- Custom tooltips on hover showing exact values
- Lightweight, performant implementation

**Data Displayed**:
- Daily revenue totals
- Daily cost totals
- Series data structure: `['revenue' => [...], 'cost' => [...]]`
- Max value calculation for scaling

**Location in Dashboard**:
- Top widget section
- Updates every 30 seconds (Livewire poll directive)
- Grid-based responsive layout

---

## 5. Models and API Endpoints

### Models

#### Core Expense Models
1. **SupplierExpense** (`app/Models/SupplierExpense.php`)
   - Uses BelongsToUser, SoftDeletes traits
   - Relations: belongsTo(Product), belongsTo(User)
   - Accessors: getTotalCostAttribute(), getAnnualizedCostAttribute()
   - Fillable: user_id, product_id, supplier_name, description, cost, quantity, unit, frequency, is_active

2. **ServiceExpense** (`app/Models/ServiceExpense.php`)
   - Uses BelongsToUser, SoftDeletes traits
   - Relations: belongsTo(Service), belongsTo(User)
   - Fillable: user_id, service_id, expense_name, description, cost, expense_type, is_active

3. **ThirdPartyService** (`app/Models/ThirdPartyService.php`)
   - Uses BelongsToUser, SoftDeletes traits
   - Relations: belongsTo(User)
   - Accessor: getAnnualizedCostAttribute()
   - Fillable: user_id, service_name, provider_name, description, cost, frequency, next_payment_date, is_active

4. **ProductionExpense** (`app/Models/ProductionExpense.php`)
   - Uses BelongsToUser, SoftDeletes traits
   - Relations: belongsTo(Product), belongsTo(User)
   - Accessor: getTotalCostAttribute()
   - Fillable: user_id, product_id, expense_name, description, cost_per_unit, quantity, unit, is_active

5. **Supply** (`app/Models/Supply.php`)
   - Uses BelongsToUser trait
   - Relations: hasMany(SupplyPurchase)
   - Methods: scopeAvailableFor(), recomputeFromPurchases()
   - Accessor: getFormattedStockAttribute()
   - Fillable: user_id, name, description, base_unit, stock_base_qty, avg_cost_per_base

6. **SupplyPurchase** (referenced in migrations)
   - Tracks individual supply purchases
   - Fields: qty, unit, unit_to_base, total_cost
   - Enables unit conversion tracking

7. **Costing** (`app/Models/Costing.php`)
   - Uses BelongsToUser trait
   - Stores cost analysis snapshots
   - Fields: source, yield_units, unit_total, batch_total, lines (JSON), product_id, product_name
   - Casts: lines as array

#### Supporting Models
- **Product** - Referenced for product-specific expense tracking
- **Service** - Referenced for service-specific expense tracking

### Controllers

#### ExpenseController (`app/Http/Controllers/ExpenseController.php`)

**Public Methods**:

1. `index()` - GET /expenses
   - Retrieves all expense types
   - Calculates totals for each category
   - Returns view with: supplierExpenses, serviceExpenses, thirdPartyServices, productionExpenses, supplies
   - Totals: totalSupplier, totalService, totalThirdParty, totalProduction, totalSupplies

2. `suppliers()` - GET /expenses/suppliers
   - Lists supplier expenses with product relations
   - Returns: expenses, products

3. `storeSupplier()` - POST /expenses/suppliers
   - Validates: product_id, supplier_name, description, cost, quantity, unit, frequency
   - Creates SupplierExpense record

4. `updateSupplier()` - PUT /expenses/suppliers/{expense}
   - Updates existing supplier expense

5. `destroySupplier()` - DELETE /expenses/suppliers/{expense}
   - Soft deletes supplier expense

6. `services()` - GET /expenses/services
   - Lists service expenses with service relations
   - Returns: expenses, services

7. `storeService()` - POST /expenses/services
   - Validates: service_id, expense_name, description, cost, expense_type
   - Creates ServiceExpense record

8. `updateService()` - PUT /expenses/services/{expense}
   - Updates existing service expense

9. `destroyService()` - DELETE /expenses/services/{expense}
   - Soft deletes service expense

10. `thirdParty()` - GET /expenses/third-party
    - Lists third-party services
    - Returns: services

11. `storeThirdParty()` - POST /expenses/third-party
    - Validates: service_name, provider_name, description, cost, frequency, next_payment_date
    - Creates ThirdPartyService record

12. `updateThirdParty()` - PUT /expenses/third-party/{service}
    - Updates existing third-party service

13. `destroyThirdParty()` - DELETE /expenses/third-party/{service}
    - Soft deletes third-party service

14. `production()` - GET /expenses/production
    - Lists production expenses with product relations
    - Returns: expenses, products

15. `storeProduction()` - POST /expenses/production
    - Validates: product_id, expense_name, description, cost_per_unit, quantity, unit
    - Creates ProductionExpense record

16. `updateProduction()` - PUT /expenses/production/{expense}
    - Updates existing production expense

17. `destroyProduction()` - DELETE /expenses/production/{expense}
    - Soft deletes production expense

18. `supplies()` - GET /expenses/supplies
    - Lists supplies with purchase relations
    - Returns: supplies

19. `storeSupply()` - POST /expenses/supplies
    - Validates: name, description, base_unit, plus optional purchase: qty, unit, total_cost
    - Creates Supply record
    - Optionally creates first SupplyPurchase and recalculates averages
    - Includes unit family validation (g↔kg, ml↔l↔cm3, u alone)

20. `updateSupply()` - PUT /expenses/supplies/{supply}
    - Updates supply metadata

21. `destroySupply()` - DELETE /expenses/supplies/{supply}
    - Deletes supply

#### ProductCostController (`app/Http/Controllers/ProductCostController.php`)

1. `edit()` - GET /products/{product}/recipe
   - Shows product recipe editor
   - Returns: product with recipe items and supplies

2. `addRecipeItem()` - POST /products/{product}/recipe/items
   - Validates: supply_id, qty, unit, waste_pct
   - Updates or creates ProductRecipe

3. `cost()` - GET /products/{product}/cost
   - Calculates product cost using CostService
   - Returns: product, cost breakdown, price, profit, profitPct, saved analyses

4. `storeAnalysis()` - POST /costings
   - Validates cost analysis data
   - Stores Costing record
   - Returns JSON response with saved analysis

5. `analyses()` - GET /products/{product}/costings
   - Returns JSON list of cost analyses for product
   - Limit: 24 recent analyses
   - Maps to standardized JSON format

#### CalculatorController (`app/Http/Controllers/CalculatorController.php`)

1. `show()` - GET /costing/calculator
   - Calculator interface
   - Retrieves: products, allSupplies (paginated), savedAnalyses (if product selected)
   - Returns: costing.calculator view

### Services

#### CostService (`app/Services/CostService.php`)

**Static Methods**:

1. `productCost(Product $product): array`
   - Calculates total cost for product based on recipe
   - Returns: ['batch_cost' => float, 'unit_cost' => float, 'yield' => int]
   - Factors in: supply costs, waste percentages, unit conversions
   - Formula: (qty_with_waste × supply_cost_per_base) summed across items

2. `suggestPrice(float $unitCost, float $targetMargin): float`
   - Calculates suggested selling price based on cost and margin
   - Margin can be 0-100 or 0-1
   - Formula: unitCost / (1 - targetMargin)

### Routes Configuration

All expense routes are nested under middleware and grouped (typically in authenticated routes):

```
GET    /expenses                           → expenses.index
GET    /expenses/suppliers                 → expenses.suppliers
POST   /expenses/suppliers                 → expenses.suppliers.store
PUT    /expenses/suppliers/{expense}       → expenses.suppliers.update
DELETE /expenses/suppliers/{expense}       → expenses.suppliers.destroy

GET    /expenses/services                  → expenses.services
POST   /expenses/services                  → expenses.services.store
PUT    /expenses/services/{expense}        → expenses.services.update
DELETE /expenses/services/{expense}        → expenses.services.destroy

GET    /expenses/third-party               → expenses.third-party
POST   /expenses/third-party               → expenses.third-party.store
PUT    /expenses/third-party/{service}     → expenses.third-party.update
DELETE /expenses/third-party/{service}     → expenses.third-party.destroy

GET    /expenses/production                → expenses.production
POST   /expenses/production                → expenses.production.store
PUT    /expenses/production/{expense}      → expenses.production.update
DELETE /expenses/production/{expense}      → expenses.production.destroy

GET    /expenses/supplies                  → expenses.supplies
POST   /expenses/supplies                  → expenses.supplies.store
PUT    /expenses/supplies/{supply}         → expenses.supplies.update
DELETE /expenses/supplies/{supply}         → expenses.supplies.destroy

GET    /costing/calculator                 → calculator.show (redirects to primary route)
GET    /costing/calculator                 → Primary calculator interface
POST   /costings                           → products.costings.store
GET    /products/{product}/costings        → products.costings.index
```

---

## 6. Database Schema

### Expense Tables (from migration `2025_11_10_200001_create_expense_tables.php`)

```
supplier_expenses
├── id (PK)
├── user_id (FK → users)
├── product_id (FK → products, nullable)
├── supplier_name (string)
├── description (text, nullable)
├── cost (decimal:15,2)
├── quantity (decimal:10,3, default=1)
├── unit (string, default='unidad')
├── frequency (enum: unica/diaria/semanal/mensual/anual, default='mensual')
├── is_active (boolean, default=true)
├── timestamps
└── soft_deletes

service_expenses
├── id (PK)
├── user_id (FK → users)
├── service_id (FK → services, nullable)
├── expense_name (string)
├── description (text, nullable)
├── cost (decimal:15,2)
├── expense_type (enum: material/mano_obra/herramienta/otro, default='otro')
├── is_active (boolean, default=true)
├── timestamps
└── soft_deletes

third_party_services
├── id (PK)
├── user_id (FK → users)
├── service_name (string)
├── provider_name (string, nullable)
├── description (text, nullable)
├── cost (decimal:15,2)
├── frequency (enum: unica/diaria/semanal/mensual/anual, default='mensual')
├── next_payment_date (date, nullable)
├── is_active (boolean, default=true)
├── timestamps
└── soft_deletes

production_expenses
├── id (PK)
├── user_id (FK → users)
├── product_id (FK → products, nullable)
├── expense_name (string)
├── description (text, nullable)
├── cost_per_unit (decimal:15,2)
├── quantity (decimal:10,3, default=1)
├── unit (string, default='unidad')
├── is_active (boolean, default=true)
├── timestamps
└── soft_deletes

service_supplies
├── id (PK)
├── service_id (FK → services)
├── supply_id (FK → supplies)
├── qty (decimal:14,3)
├── unit (string:10)
├── waste_pct (decimal:5,2, default=0)
├── timestamps
└── unique constraint on [service_id, supply_id]
```

---

## 7. Multi-Tenant Considerations

All expense models use the `BelongsToUser` trait which provides:
- Automatic user_id filtering via global scope
- Ensures data isolation between users
- Supply model has advanced `scopeAvailableFor()` method supporting:
  - Master users (access all)
  - Company-level inventory sharing
  - Branch-level inventory sharing
  - Regular user fallback to parent/company inventory

---

## 8. Key Features & Functionality

1. **Frequency-Based Annualization**
   - Supplier, Third-party, and some Production expenses support frequency-based calculations
   - Automatic annualization for reporting purposes

2. **Unit Conversion**
   - Supply purchases support unit conversion (kg↔g, l↔ml, etc.)
   - Tracked via `unit_to_base` factor

3. **Soft Deletes**
   - All expense models use soft deletes for data preservation

4. **Cost Calculation Service**
   - Centralized CostService for product costing
   - Accounts for waste percentages and unit conversions
   - Provides price suggestions based on margin targets

5. **Live Dashboard Integration**
   - RevenueWidget displays costs alongside revenue
   - Real-time data with Livewire polling

6. **Status Tracking**
   - All expenses can be marked active/inactive
   - Clean filtering capability

---

## 9. Summary Table

| Category | Model | View File | Routes | Key Feature |
|----------|-------|-----------|--------|------------|
| Suppliers | SupplierExpense | suppliers.blade.php | 4 routes (CRUD) | Frequency-based annualization |
| Services | ServiceExpense | services.blade.php | 4 routes (CRUD) | Type classification (material/labor/tool) |
| Third-Party | ThirdPartyService | third-party.blade.php | 4 routes (CRUD) | Payment date tracking |
| Production | ProductionExpense | production.blade.php | 4 routes (CRUD) | Per-unit costing, calculator link |
| Supplies | Supply | supplies.blade.php | 4 routes (CRUD) | Unit conversion, stock tracking |

