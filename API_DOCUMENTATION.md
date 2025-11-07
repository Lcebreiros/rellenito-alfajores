# API Documentation - Rellenito Alfajores

API REST para consumo desde aplicación Android.

**Base URL:** `https://tu-dominio.com/api/v1`

**Formato de respuesta:** JSON

---

## Autenticación

Todas las rutas protegidas requieren el token de autenticación en el header:

```
Authorization: Bearer {token}
```

### Login

**POST** `/auth/login`

Autentica un usuario y devuelve un token de acceso.

**Body:**
```json
{
  "email": "usuario@example.com",
  "password": "password123",
  "device_name": "android-app" // opcional
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "usuario@example.com",
      "hierarchy_level": 0,
      "subscription_level": "premium",
      "is_active": true
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
  }
}
```

**Errores:**
- `422`: Credenciales incorrectas
- `422`: Cuenta desactivada

---

### Register

**POST** `/auth/register`

Registra un nuevo usuario (opcional, si está habilitado).

**Body:**
```json
{
  "name": "Juan Pérez",
  "email": "usuario@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "device_name": "android-app" // opcional
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "usuario@example.com",
      "hierarchy_level": 0
    },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
  }
}
```

---

### Logout

**POST** `/auth/logout`

Cierra la sesión actual (revoca el token actual).

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Logout exitoso"
}
```

---

### Logout All

**POST** `/auth/logout-all`

Cierra todas las sesiones (revoca todos los tokens del usuario).

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Todos los dispositivos han sido desconectados"
}
```

---

### Me

**GET** `/auth/me`

Obtiene la información del usuario autenticado.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@example.com",
    "hierarchy_level": 0,
    "subscription_level": "premium",
    "is_active": true,
    "organization_context": null,
    "created_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

---

## Productos

### Listar Productos

**GET** `/products`

Lista todos los productos disponibles para el usuario.

**Query Parameters:**
- `q` (string): Buscar por nombre, SKU o código de barras
- `category` (string): Filtrar por categoría
- `is_active` (boolean): Filtrar por estado activo
- `per_page` (int): Items por página (default: 20, max: 100)
- `page` (int): Número de página

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Alfajor de Chocolate",
      "sku": "ALF-001",
      "barcode": "7891234567890",
      "price": 150.00,
      "cost_price": 80.00,
      "stock": 50,
      "min_stock": 10,
      "is_active": true,
      "is_low_stock": false,
      "category": "Alfajores",
      "unit": "unidad",
      "image": "products/abc123.jpg",
      "user_id": 1,
      "company_id": 1
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

---

### Buscar Productos

**GET** `/products/search`

Busca productos para agregar a pedidos (límite de 20 resultados).

**Query Parameters:**
- `q` (string, requerido): Término de búsqueda

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Alfajor de Chocolate",
      "sku": "ALF-001",
      "barcode": "7891234567890",
      "price": 150.00,
      "stock": 50,
      "image": "products/abc123.jpg",
      "is_active": true
    }
  ]
}
```

---

### Ver Producto

**GET** `/products/{id}`

Obtiene los detalles de un producto específico.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Alfajor de Chocolate",
    "sku": "ALF-001",
    "barcode": "7891234567890",
    "description": "Delicioso alfajor relleno de dulce de leche",
    "category": "Alfajores",
    "unit": "unidad",
    "price": 150.00,
    "cost_price": 80.00,
    "stock": 50,
    "min_stock": 10,
    "is_active": true,
    "is_shared": false,
    "image": "products/abc123.jpg"
  }
}
```

---

### Crear Producto

**POST** `/products`

Crea un nuevo producto.

**Body:**
```json
{
  "name": "Alfajor de Chocolate",
  "sku": "ALF-001", // opcional
  "barcode": "7891234567890", // opcional
  "price": 150.00,
  "cost_price": 80.00, // opcional
  "stock": 50, // opcional
  "min_stock": 10, // opcional
  "description": "Descripción del producto", // opcional
  "category": "Alfajores", // opcional
  "unit": "unidad", // opcional
  "is_active": true, // opcional
  "is_shared": false, // opcional
  "image": "data:image/png;base64,..." // opcional (base64)
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Producto creado exitosamente",
  "data": { /* producto creado */ }
}
```

---

### Actualizar Producto

**PUT** `/products/{id}`

Actualiza un producto existente.

**Body:** (igual que crear, todos los campos son opcionales)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Producto actualizado exitosamente",
  "data": { /* producto actualizado */ }
}
```

---

### Actualizar Stock

**PATCH** `/products/{id}/stock`

Actualiza el stock de un producto.

**Body:**
```json
{
  "stock": 100,
  "reason": "Ajuste de inventario" // opcional
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Stock actualizado exitosamente",
  "data": { /* producto con stock actualizado */ }
}
```

---

### Eliminar Producto

**DELETE** `/products/{id}`

Elimina un producto (solo si no tiene pedidos asociados).

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Producto eliminado exitosamente"
}
```

---

## Pedidos

### Listar Pedidos

**GET** `/orders`

Lista todos los pedidos del usuario.

**Query Parameters:**
- `status` (string): Filtrar por estado (draft, completed, cancelled)
- `payment_status` (string): Filtrar por estado de pago (pending, paid, partial)
- `client_id` (int): Filtrar por cliente
- `from_date` (date): Fecha desde
- `to_date` (date): Fecha hasta
- `is_scheduled` (boolean): Filtrar pedidos agendados
- `per_page` (int): Items por página (default: 20, max: 100)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-001",
      "status": "completed",
      "payment_status": "paid",
      "total": 450.00,
      "discount": 0,
      "tax_amount": 0,
      "notes": "Pedido especial",
      "is_scheduled": false,
      "sold_at": "2025-01-01T12:00:00.000000Z",
      "created_at": "2025-01-01T10:00:00.000000Z",
      "client": {
        "id": 1,
        "name": "Cliente Ejemplo",
        "phone": "+54 9 11 1234-5678"
      },
      "items": [
        {
          "id": 1,
          "quantity": 3,
          "price": 150.00,
          "subtotal": 450.00,
          "product": {
            "id": 1,
            "name": "Alfajor de Chocolate",
            "price": 150.00
          }
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 50
  }
}
```

---

### Ver Pedido

**GET** `/orders/{id}`

Obtiene los detalles completos de un pedido.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-001",
    "status": "completed",
    "payment_status": "paid",
    "total": 450.00,
    "client": { /* datos del cliente */ },
    "items": [ /* items del pedido */ ],
    "payment_methods": [ /* métodos de pago usados */ ]
  }
}
```

---

### Crear Pedido

**POST** `/orders`

Crea un nuevo pedido en estado borrador.

**Body:**
```json
{
  "client_id": 1, // opcional
  "notes": "Notas del pedido", // opcional
  "discount": 0, // opcional
  "tax_amount": 0, // opcional
  "scheduled_for": "2025-01-15T10:00:00Z", // opcional
  "is_scheduled": false, // opcional
  "items": [
    {
      "product_id": 1,
      "quantity": 3,
      "price": 150.00 // opcional, usa el precio del producto si no se envía
    }
  ]
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Pedido creado exitosamente",
  "data": { /* pedido creado */ }
}
```

---

### Actualizar Pedido

**PUT** `/orders/{id}`

Actualiza un pedido (solo en estado borrador).

**Body:**
```json
{
  "client_id": 1,
  "notes": "Notas actualizadas",
  "discount": 10.00,
  "tax_amount": 5.00
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Pedido actualizado exitosamente",
  "data": { /* pedido actualizado */ }
}
```

---

### Agregar Item al Pedido

**POST** `/orders/{id}/items`

Agrega un producto al pedido (solo en estado borrador).

**Body:**
```json
{
  "product_id": 2,
  "quantity": 5,
  "price": 200.00 // opcional
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Item agregado exitosamente",
  "data": { /* item creado */ }
}
```

---

### Eliminar Item del Pedido

**DELETE** `/orders/{id}/items/{itemId}`

Elimina un producto del pedido (solo en estado borrador).

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Item eliminado exitosamente"
}
```

---

### Finalizar Pedido

**POST** `/orders/{id}/finalize`

Finaliza un pedido, descuenta el stock y lo marca como completado.

**Body:**
```json
{
  "payment_status": "paid", // paid, pending, partial
  "payment_method_id": 1 // opcional
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Pedido finalizado exitosamente",
  "data": { /* pedido finalizado */ }
}
```

---

### Cancelar Pedido

**POST** `/orders/{id}/cancel`

Cancela un pedido.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Pedido cancelado exitosamente",
  "data": { /* pedido cancelado */ }
}
```

---

### Eliminar Pedido

**DELETE** `/orders/{id}`

Elimina un pedido.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Pedido eliminado exitosamente"
}
```

---

## Clientes

### Listar Clientes

**GET** `/clients`

Lista todos los clientes.

**Query Parameters:**
- `q` (string): Buscar por nombre, email, teléfono o documento
- `city` (string): Filtrar por ciudad
- `province` (string): Filtrar por provincia
- `per_page` (int): Items por página (default: 20, max: 100)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Cliente Ejemplo",
      "email": "cliente@example.com",
      "phone": "+54 9 11 1234-5678",
      "document_number": "12345678",
      "company": "Empresa SA",
      "address": "Calle Falsa 123",
      "city": "Buenos Aires",
      "province": "CABA",
      "country": "Argentina",
      "balance": 0.00
    }
  ],
  "meta": { /* paginación */ }
}
```

---

### Buscar Clientes

**GET** `/clients/search`

Busca clientes para autocompletado (límite de 20 resultados).

**Query Parameters:**
- `q` (string, requerido): Término de búsqueda

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Cliente Ejemplo",
      "email": "cliente@example.com",
      "phone": "+54 9 11 1234-5678",
      "address": "Calle Falsa 123",
      "balance": 0.00
    }
  ]
}
```

---

### Ver Cliente

**GET** `/clients/{id}`

Obtiene los detalles de un cliente.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Cliente Ejemplo",
    "email": "cliente@example.com",
    "phone": "+54 9 11 1234-5678",
    "orders": [ /* últimos 10 pedidos */ ]
  }
}
```

---

### Crear Cliente

**POST** `/clients`

Crea un nuevo cliente.

**Body:**
```json
{
  "name": "Cliente Nuevo",
  "email": "nuevo@example.com", // opcional
  "phone": "+54 9 11 1234-5678", // opcional
  "document_number": "12345678", // opcional
  "company": "Empresa SA", // opcional
  "address": "Calle Falsa 123", // opcional
  "city": "Buenos Aires", // opcional
  "province": "CABA", // opcional
  "country": "Argentina", // opcional
  "notes": "Notas adicionales", // opcional
  "tags": ["vip", "mayorista"], // opcional
  "balance": 0.00 // opcional
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "data": { /* cliente creado */ }
}
```

---

### Actualizar Cliente

**PUT** `/clients/{id}`

Actualiza un cliente existente.

**Body:** (igual que crear, todos los campos son opcionales)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Cliente actualizado exitosamente",
  "data": { /* cliente actualizado */ }
}
```

---

### Eliminar Cliente

**DELETE** `/clients/{id}`

Elimina un cliente (solo si no tiene pedidos asociados).

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Cliente eliminado exitosamente"
}
```

---

## Métodos de Pago

### Listar Métodos de Pago

**GET** `/payment-methods`

Lista los métodos de pago disponibles.

**Query Parameters:**
- `is_active` (boolean): Filtrar por estado activo
- `is_global` (boolean): Filtrar por alcance global

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Efectivo",
      "description": "Pago en efectivo",
      "is_active": true,
      "is_global": true,
      "requires_reference": false,
      "icon": "cash"
    }
  ]
}
```

---

### Crear Método de Pago

**POST** `/payment-methods`

Crea un nuevo método de pago.

**Body:**
```json
{
  "name": "Transferencia",
  "description": "Transferencia bancaria", // opcional
  "is_active": true, // opcional
  "is_global": false, // opcional
  "requires_reference": true, // opcional
  "icon": "bank" // opcional
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Método de pago creado exitosamente",
  "data": { /* método de pago creado */ }
}
```

---

### Activar/Desactivar Método de Pago

**POST** `/payment-methods/{id}/toggle`

Cambia el estado activo/inactivo de un método de pago.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Estado actualizado exitosamente",
  "data": { /* método de pago actualizado */ }
}
```

---

## Stock

### Consultar Stock

**GET** `/stock`

Consulta el inventario de productos.

**Query Parameters:**
- `low_stock` (boolean): Solo productos con stock bajo
- `out_of_stock` (boolean): Solo productos sin stock
- `q` (string): Buscar por nombre o SKU
- `per_page` (int): Items por página (default: 50, max: 100)

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Alfajor de Chocolate",
      "sku": "ALF-001",
      "stock": 50,
      "min_stock": 10,
      "unit": "unidad",
      "is_active": true
    }
  ],
  "meta": { /* paginación */ }
}
```

---

### Historial de Ajustes

**GET** `/stock/history`

Obtiene el historial de ajustes de stock.

**Query Parameters:**
- `product_id` (int): Filtrar por producto
- `from_date` (date): Fecha desde
- `to_date` (date): Fecha hasta
- `per_page` (int): Items por página

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "previous_stock": 45,
      "new_stock": 50,
      "adjustment": 5,
      "reason": "Reposición",
      "created_at": "2025-01-01T10:00:00.000000Z",
      "product": {
        "id": 1,
        "name": "Alfajor de Chocolate",
        "sku": "ALF-001"
      },
      "user": {
        "id": 1,
        "name": "Juan Pérez"
      }
    }
  ],
  "meta": { /* paginación */ }
}
```

---

### Productos con Stock Bajo

**GET** `/stock/low-stock`

Obtiene productos con stock menor o igual al mínimo.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [ /* productos */ ],
  "count": 5
}
```

---

### Productos sin Stock

**GET** `/stock/out-of-stock`

Obtiene productos sin stock.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [ /* productos */ ],
  "count": 3
}
```

---

### Resumen de Stock

**GET** `/stock/summary`

Obtiene un resumen del estado del inventario.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_products": 150,
    "in_stock": 120,
    "low_stock": 25,
    "out_of_stock": 5
  }
}
```

---

## Códigos de Error

- `200`: Operación exitosa
- `201`: Recurso creado exitosamente
- `400`: Solicitud inválida
- `401`: No autenticado
- `403`: Sin permisos
- `404`: Recurso no encontrado
- `422`: Error de validación
- `500`: Error interno del servidor

**Formato de error:**
```json
{
  "success": false,
  "message": "Descripción del error"
}
```

---

## Notas para Android

1. **Almacenar el token**: Después del login, guarda el token de forma segura (SharedPreferences o DataStore encriptado)
2. **Header de autorización**: Incluye el token en todas las peticiones: `Authorization: Bearer {token}`
3. **Manejo de errores**: Verifica el código HTTP y el campo `success` en la respuesta
4. **Renovar token**: Si recibes un error 401, redirige al login
5. **Paginación**: Usa los parámetros `page` y `per_page` para cargar datos en lotes
6. **Imágenes**: Para subir imágenes, convierte a base64 e incluye en el campo `image`

---

## Ejemplo de Uso en Android (Retrofit)

```kotlin
// AuthService.kt
interface AuthService {
    @POST("auth/login")
    suspend fun login(@Body credentials: LoginRequest): ApiResponse<LoginResponse>

    @POST("auth/logout")
    suspend fun logout(): ApiResponse<Unit>

    @GET("auth/me")
    suspend fun getProfile(): ApiResponse<User>
}

// ProductService.kt
interface ProductService {
    @GET("products")
    suspend fun getProducts(
        @Query("page") page: Int,
        @Query("per_page") perPage: Int = 20,
        @Query("q") query: String? = null
    ): ApiResponse<PaginatedResponse<Product>>

    @GET("products/{id}")
    suspend fun getProduct(@Path("id") id: Int): ApiResponse<Product>

    @POST("products")
    suspend fun createProduct(@Body product: CreateProductRequest): ApiResponse<Product>
}

// OrderService.kt
interface OrderService {
    @GET("orders")
    suspend fun getOrders(
        @Query("page") page: Int,
        @Query("status") status: String? = null
    ): ApiResponse<PaginatedResponse<Order>>

    @POST("orders")
    suspend fun createOrder(@Body order: CreateOrderRequest): ApiResponse<Order>

    @POST("orders/{id}/finalize")
    suspend fun finalizeOrder(
        @Path("id") orderId: Int,
        @Body request: FinalizeOrderRequest
    ): ApiResponse<Order>
}
```
