# Configuración de API para Android

Este documento explica cómo configurar y usar la API REST de Rellenito Alfajores.

## Configuración Inicial

### 1. Verificar que Sanctum esté instalado

Sanctum ya está instalado en el proyecto. Verificar que exista:
- `config/sanctum.php`
- `vendor/laravel/sanctum`

### 2. Configurar Variables de Entorno

Asegurarse de que `.env` tenga las siguientes configuraciones:

```env
# URL de la aplicación (debe ser accesible desde internet para Android)
APP_URL=https://tu-dominio.com

# Dominios permitidos para CORS (opcional, por defecto permite todos)
SANCTUM_STATEFUL_DOMAINS=localhost,tu-dominio.com

# Tiempo de expiración de tokens (en minutos, null = sin expiración)
SANCTUM_EXPIRATION=null
```

### 3. Migrar la base de datos (si es necesario)

Si no se han ejecutado las migraciones de Sanctum:

```bash
php artisan migrate
```

### 4. Configurar CORS

El archivo `config/cors.php` ya está configurado para permitir todas las peticiones desde cualquier origen. Si necesitas restringir el acceso:

```php
'allowed_origins' => [
    'https://tu-dominio.com',
    'http://localhost',
],
```

### 5. Probar la API

#### Crear un usuario de prueba

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Usuario API',
    'email' => 'api@test.com',
    'password' => bcrypt('password123'),
    'hierarchy_level' => 0,
    'is_active' => true,
]);
```

#### Probar el login

```bash
curl -X POST https://tu-dominio.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "api@test.com",
    "password": "password123",
    "device_name": "test"
  }'
```

Respuesta esperada:
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": { ... },
    "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
  }
}
```

#### Probar endpoint protegido

```bash
curl -X GET https://tu-dominio.com/api/v1/auth/me \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

---

## Endpoints Disponibles

Ver [API_DOCUMENTATION.md](API_DOCUMENTATION.md) para la documentación completa de todos los endpoints.

### Resumen de Endpoints

**Autenticación:**
- `POST /api/v1/auth/login` - Login
- `POST /api/v1/auth/register` - Registro (opcional)
- `POST /api/v1/auth/logout` - Logout
- `GET /api/v1/auth/me` - Perfil del usuario

**Productos:**
- `GET /api/v1/products` - Listar productos
- `GET /api/v1/products/search` - Buscar productos
- `POST /api/v1/products` - Crear producto
- `PUT /api/v1/products/{id}` - Actualizar producto
- `PATCH /api/v1/products/{id}/stock` - Actualizar stock

**Pedidos:**
- `GET /api/v1/orders` - Listar pedidos
- `POST /api/v1/orders` - Crear pedido
- `POST /api/v1/orders/{id}/items` - Agregar item
- `POST /api/v1/orders/{id}/finalize` - Finalizar pedido

**Clientes:**
- `GET /api/v1/clients` - Listar clientes
- `GET /api/v1/clients/search` - Buscar clientes
- `POST /api/v1/clients` - Crear cliente

**Stock:**
- `GET /api/v1/stock` - Consultar inventario
- `GET /api/v1/stock/summary` - Resumen de stock
- `GET /api/v1/stock/low-stock` - Productos con stock bajo

---

## Seguridad

### Headers Requeridos

Todas las peticiones a la API deben incluir:

```
Content-Type: application/json
Accept: application/json
```

Para endpoints protegidos, también:

```
Authorization: Bearer {token}
```

### Buenas Prácticas

1. **Almacenar tokens de forma segura**: Usa EncryptedSharedPreferences en Android
2. **HTTPS obligatorio**: Nunca uses HTTP en producción
3. **Validar certificados SSL**: No desactives la validación de certificados
4. **Manejo de errores**: Implementa retry logic y manejo de errores de red
5. **Caché local**: Implementa caché para mejorar la experiencia offline
6. **Rate limiting**: Ten en cuenta los límites de peticiones por minuto

### Manejo de Tokens Expirados

Si recibes un error 401 (Unauthorized):
1. Verificar que el token sea válido
2. Si el token expiró, redirigir al login
3. Guardar el estado de la app para restaurar después del login

---

## Optimizaciones para Android

### 1. Paginación

Usa paginación para cargar datos en lotes:

```kotlin
// Cargar 20 productos a la vez
productService.getProducts(page = 1, perPage = 20)
```

### 2. Búsqueda con Debounce

Implementa debounce en búsquedas para evitar peticiones excesivas:

```kotlin
searchFlow
    .debounce(300)
    .distinctUntilChanged()
    .flatMapLatest { query ->
        productService.search(query)
    }
```

### 3. Caché de Respuestas

Usa Room Database para cachear respuestas:

```kotlin
suspend fun getProducts(): List<Product> {
    return try {
        val products = api.getProducts()
        database.insertProducts(products)
        products
    } catch (e: Exception) {
        database.getProducts() // Fallback a caché
    }
}
```

### 4. Sincronización Offline

Implementa WorkManager para sincronizar cambios cuando hay conexión:

```kotlin
class SyncWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {
    override suspend fun doWork(): Result {
        return try {
            syncPendingOrders()
            syncProductUpdates()
            Result.success()
        } catch (e: Exception) {
            Result.retry()
        }
    }
}
```

---

## Ejemplo de Implementación en Android

### Configurar Retrofit

```kotlin
// ApiClient.kt
object ApiClient {
    private const val BASE_URL = "https://tu-dominio.com/api/v1/"

    private val okHttpClient = OkHttpClient.Builder()
        .addInterceptor { chain ->
            val token = TokenManager.getToken()
            val request = chain.request().newBuilder()
                .addHeader("Accept", "application/json")
                .addHeader("Content-Type", "application/json")
                .apply {
                    if (token != null) {
                        addHeader("Authorization", "Bearer $token")
                    }
                }
                .build()
            chain.proceed(request)
        }
        .build()

    val retrofit: Retrofit = Retrofit.Builder()
        .baseUrl(BASE_URL)
        .client(okHttpClient)
        .addConverterFactory(GsonConverterFactory.create())
        .build()
}

// Services
val authService = ApiClient.retrofit.create(AuthService::class.java)
val productService = ApiClient.retrofit.create(ProductService::class.java)
val orderService = ApiClient.retrofit.create(OrderService::class.java)
```

### Models

```kotlin
data class ApiResponse<T>(
    val success: Boolean,
    val message: String? = null,
    val data: T? = null
)

data class PaginatedResponse<T>(
    val data: List<T>,
    val meta: PaginationMeta
)

data class PaginationMeta(
    val current_page: Int,
    val last_page: Int,
    val per_page: Int,
    val total: Int
)

data class LoginRequest(
    val email: String,
    val password: String,
    val device_name: String = "android-app"
)

data class LoginResponse(
    val user: User,
    val token: String
)

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val hierarchy_level: Int,
    val subscription_level: String?,
    val is_active: Boolean
)

data class Product(
    val id: Int,
    val name: String,
    val sku: String?,
    val barcode: String?,
    val price: Double,
    val stock: Double,
    val min_stock: Double,
    val is_active: Boolean,
    val image_url: String?
)

data class Order(
    val id: Int,
    val order_number: String,
    val status: String,
    val payment_status: String,
    val total: Double,
    val created_at: String,
    val client: Client?,
    val items: List<OrderItem>
)
```

### Repository Pattern

```kotlin
class ProductRepository(private val api: ProductService) {

    suspend fun getProducts(page: Int = 1): Result<List<Product>> {
        return try {
            val response = api.getProducts(page)
            if (response.success) {
                Result.success(response.data?.data ?: emptyList())
            } else {
                Result.failure(Exception(response.message ?: "Error desconocido"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun searchProducts(query: String): Result<List<Product>> {
        return try {
            val response = api.searchProducts(query)
            if (response.success) {
                Result.success(response.data ?: emptyList())
            } else {
                Result.failure(Exception(response.message ?: "Error desconocido"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
```

---

## Troubleshooting

### Error 401 - Unauthorized

- Verificar que el token sea válido
- Verificar que el header Authorization esté presente
- Verificar que el token no haya expirado

### Error 403 - Forbidden

- El usuario no tiene permisos para acceder al recurso
- Verificar la jerarquía del usuario (company, admin, user)

### Error 419 - CSRF Token Mismatch

- Este error solo aplica a la web, no a la API
- Asegurarse de usar `/api/v1/*` y no rutas web

### Error 500 - Internal Server Error

- Revisar logs del servidor: `php artisan pail`
- Verificar conexión a base de datos
- Verificar permisos de escritura en storage/

### CORS Issues

- Verificar que `config/cors.php` esté configurado correctamente
- En desarrollo, permitir `allowed_origins => ['*']`
- En producción, especificar dominios permitidos

---

## Comandos Útiles

```bash
# Ver rutas API
php artisan route:list --path=api/v1

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver logs en tiempo real
php artisan pail

# Generar token manualmente para testing
php artisan tinker
$user = User::find(1);
$token = $user->createToken('test-token')->plainTextToken;
echo $token;

# Revocar todos los tokens de un usuario
php artisan tinker
$user = User::find(1);
$user->tokens()->delete();
```

---

## Próximos Pasos

1. **Testing**: Implementar tests automatizados para la API
2. **Rate Limiting**: Configurar límites de peticiones por minuto
3. **Notificaciones Push**: Integrar Firebase Cloud Messaging
4. **Webhooks**: Implementar webhooks para eventos importantes
5. **Versioning**: Mantener versionado de la API (`v1`, `v2`)

---

## Soporte

Para reportar problemas o sugerencias:
- Email: soporte@rellenito-alfajores.com
- GitHub Issues: (si aplica)

---

**Última actualización:** 2025-01-06
