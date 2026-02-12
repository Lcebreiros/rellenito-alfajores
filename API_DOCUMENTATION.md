# API Documentation - Rellenito Alfajores

Contrato actualizado de la API REST.

**Base URL:** `https://tu-dominio.com/api/v1`

**Formato de respuesta:** JSON

**Rate limiting:**
- `api-auth`: 5 req/min (login/register)
- `api-read`: 100 req/min (lectura)
- `api-write`: 30 req/min (escritura)

## Autenticación y scopes

Todas las rutas protegidas requieren:
```
Authorization: Bearer {token}
```

- Tokens internos: abilities `*` (acceso completo).
- Tokens externos (tienda/app): ability `storefront`. Con este token:
  - Productos se devuelven sin costos internos.
  - Endpoints de insumos, proveedores y gastos responden 403 (`internal.only`).

### Login
**POST** `/auth/login`

Body:
```json
{ "email": "...", "password": "...", "device_name": "android-app" }
```

Respuestas:
- `200` OK (devuelve token y usuario)
- `422` credenciales inválidas o cuenta inactiva

### Logout
**POST** `/auth/logout` (revoca token actual)
**POST** `/auth/logout-all` (revoca todos los tokens)

### Me
**GET** `/auth/me` → datos del usuario autenticado.

## Productos

### Listar
**GET** `/products`
Params: `q`, `category`, `is_active`, `per_page` (max 100), `page`.
Responde paginado (`data`, `meta`).
- Token `storefront`: campos públicos (id, name, sku, barcode, price, stock, min_stock, is_active, is_low_stock, image).
- Token interno: campos completos.

### Buscar
**GET** `/products/search` (`q` requerido, máx 20 resultados)

### Ver
**GET** `/products/{id}`

### Crear / Actualizar / Eliminar
**POST** `/products`
**PUT** `/products/{id}`
**DELETE** `/products/{id}`

Validaciones clave: SKU único por company, `price` ≥ 0, imagen opcional base64.

### Ajustar stock
**PATCH** `/products/{id}/stock` (body: `stock`, `reason` opcional)

## Pedidos

### Listar / Ver
**GET** `/orders` (filtros `status`, `payment_status`, `client_id`, `from_date`, `to_date`, `is_scheduled`, `per_page`)
**GET** `/orders/{id}`

### Crear
**POST** `/orders`
Body: `client_id` opcional, `items` requerido (array `{product_id, quantity, price?}`), `discount`, `tax_amount`, `scheduled_for`, `is_scheduled`.

### Actualizar (solo draft)
**PUT** `/orders/{id}`

### Ítems
**POST** `/orders/{id}/items` agrega item (solo draft)
**DELETE** `/orders/{id}/items/{itemId}` elimina item (solo draft)

### Finalizar / Cancelar
**POST** `/orders/{id}/finalize` (body: `payment_status` `paid|pending|partial`, `payment_method_id` opcional). Si falta stock → `409`.
**POST** `/orders/{id}/cancel`

### Eliminar
**DELETE** `/orders/{id}`

## Clientes

**GET** `/clients` (filtros `q`, `city`, `province`, `per_page`)
**GET** `/clients/search` (q requerido)
**GET** `/clients/{id}`
**POST** `/clients`
**PUT** `/clients/{id}`
**DELETE** `/clients/{id}` (no si tiene pedidos)

## Métodos de pago

**GET** `/payment-methods` (filtros `is_active`, `is_global`)
**GET** `/payment-methods/{id}`
**POST** `/payment-methods`
**PUT** `/payment-methods/{id}`
**DELETE** `/payment-methods/{id}`
**POST** `/payment-methods/{id}/toggle`

## Stock (consultas)

**GET** `/stock` (filtros `low_stock`, `out_of_stock`, `q`, `per_page`)
**GET** `/stock/history` (filtros `product_id`, `from_date`, `to_date`)
**GET** `/stock/low-stock`
**GET** `/stock/out-of-stock`
**GET** `/stock/summary`

## Servicios

**GET** `/services` (filtros `q`, `service_category_id`, `is_active`, `per_page`)
**GET** `/services/search` (q requerido)
**GET** `/services/{id}`
**POST** `/services` (permite `variants` y `new_category`)
**PUT** `/services/{id}`
**DELETE** `/services/{id}`

## Insumos (solo tokens internos)

**GET** `/supplies` (`q`, `per_page`)
**GET** `/supplies/{id}`
**POST** `/supplies`
**PUT** `/supplies/{id}`
**DELETE** `/supplies/{id}`
**POST** `/supplies/{id}/purchase` (compra y recalcula stock/costo)

## Proveedores (solo tokens internos)

**GET** `/suppliers` (`q`, `is_active`, `per_page`)
**GET** `/suppliers/{id}`
**POST** `/suppliers`
**PUT** `/suppliers/{id}`
**DELETE** `/suppliers/{id}`

## Gastos (solo tokens internos)

### Proveedores
**GET** `/expenses/suppliers` (filtros `supplier_id`, `product_id`, `frequency`, `is_active`, `per_page`)
**POST** `/expenses/suppliers`
**PUT** `/expenses/suppliers/{id}`
**DELETE** `/expenses/suppliers/{id}`

### Servicios
**GET** `/expenses/services` (filtros `service_id`, `expense_type`, `is_active`, `per_page`)
**POST** `/expenses/services`
**PUT** `/expenses/services/{id}`
**DELETE** `/expenses/services/{id}`

### Producción
**GET** `/expenses/production` (filtros `product_id`, `is_active`, `per_page`)
**POST** `/expenses/production`
**PUT** `/expenses/production/{id}`
**DELETE** `/expenses/production/{id}`

### Resumen
**GET** `/expenses/summary` → totales anualizados por categoría.

## Búsqueda global

**GET** `/search` → búsqueda rápida de productos/pedidos/clientes (máx 5 de cada).

## Health

**GET** `/health` → `{ status: "ok", timestamp: ... }`

## Errores comunes

- `401` Token inválido/ausente.
- `403` Sin permiso o endpoint interno para tokens externos.
- `404` Recurso no encontrado o fuera del tenant.
- `409` Conflicto de stock al finalizar pedido.
- `422` Validación fallida.

## Multitenancy

- Los recursos se filtran por `company_id` del usuario (rootCompany).
- Validaciones `exists` limitan a la empresa (clientes, productos, pagos, gastos, insumos).

## CORS

Configura `CORS_ALLOWED_ORIGINS` (CSV) con los dominios autorizados. Si no se define, se usa el host de `APP_URL`. Habilita `CORS_SUPPORTS_CREDENTIALS` solo si necesitas cookies/sesión.

## Emisión de tokens

- Token externo (tienda/app) con scope `storefront`:
  ```bash
  php artisan tinker <<'EOF'
  $u = \App\Models\User::first();
  echo $u->createToken('storefront-token', ['storefront'])->plainTextToken;
  EOF
  ```
- Token interno (acceso completo):
  ```bash
  php artisan tinker <<'EOF'
  $u = \App\Models\User::first();
  echo $u->createToken('internal-token', ['*'])->plainTextToken;
  EOF
  ```

Incluye el token en el header `Authorization: Bearer ...`.
