## 📋 **Introducción**

Amarena es una **tienda online completa** construida con **PHP bajo patrón MVC**, MySQL y desplegada en XAMPP. Implementa un sistema robusto de **autenticación, autorización y gestión de órdenes** con trazabilidad completa.

---

## 🏗️ **Arquitectura MVC**

Tu proyecto sigue el patrón **Model-View-Controller** de forma disciplinada:

### **Controllers** (Controllers)
- **Orquestan** las peticiones HTTP
- **Validan** permisos y entrada
- **Delegan** la lógica en modelos
- Ejemplos: `OrderManagementController`, `ManagementController`

### **Models** (Models)
- **Encapsulan** acceso a base de datos
- **Ejecutan** transacciones y lógica de negocio
- Clases como `Order`, `Product`, `OrderStatus`, `Permission`

### **Views** (vistas)
- **Presentan** datos al usuario
- Organizadas por área: `tienda/`, `admin/`, `checkout/`

### **Punto de Entrada** (`public/index.php`)
Actúa como **Front Controller** centralizando todo el flujo:
1. Procesa la URI y método HTTP
2. Aplica middleware de compatibilidad y permisos
3. Busca la ruta en routes.php
4. Instancia el controlador y ejecuta la acción

---

## 🔐 **Sistema de Roles y Permisos**

Tu aplicación implementa un **sistema híbrido**: compatibilidad con roles heredados + permisos granulares.

### **Roles Disponibles**
- **Administrador**: acceso total a gestión
- **Cliente**: navegación y compra

### **Flujo de Autenticación** (`app/Utils/Auth.php`)

```php
// El usuario inicia sesión
Auth::isLoggedIn()          // Verifica sesión
Auth::getUserRole()         // Obtiene 'Administrador' o 'Cliente'
Auth::isAdmin()             // true si idrol = 1
```

### **Control de Permisos** (`app/Utils/PermissionManager.php`)

Cada acción sensible requiere un permiso específico:

```
PermissionManager::hasPermission('manage.orders')
PermissionManager::hasPermission('manage.products')
PermissionManager::hasPermission('view.own.orders')
```

### **Middleware de Permisos** (`app/Middleware/PermissionMiddleware.php`)

Los controladores validan permisos antes de ejecutar:

````php
public function index()
{
    PermissionMiddleware::requireOrderManagement();
    // Solo llega aquí si tiene permiso
}
````

---

## 🔄 **Flujo de Compatibilidad de Rutas** (`app/Middleware/CompatibilityMiddleware.php`)

Tu aplicación migró de rutas antiguas (`/admin/productos`) a nuevas (`/management/products`). El middleware maneja esto automáticamente:

**Mapeo automático:**
```
/admin/productos          → /management/products
/admin/categorias/crear   → /management/categories/store
/admin/ordenes            → /management/orders
```

**Beneficio**: URLs antiguas siguen funcionando con redirección 301 permanente.

---

## 🛒 **Flujo Completo de Compra**

### **1️⃣ Navegación del Cliente**
- Visualiza catálogo (`/catalog`)
- Filtra por categoría
- Busca productos

### **2️⃣ Carrito**
- Agrega productos (`/cart`)
- Sistema gestiona items en sesión
- Calcula subtotal dinámicamente

### **3️⃣ Checkout** (`CheckoutFlowController`)
```
Paso 1: Datos del cliente
   ↓
Paso 2: Validar stock
   ↓
Paso 3: Resumen de orden
   ↓
Paso 4: Generar QR/token de pago
   ↓
Paso 5: Simular confirmación de pago
```

### **4️⃣ Creación de Orden** (`app/Models/Order`)

```
INSERT en tabla 'compra' (cliente_id, total, fecha)
   ↓
INSERT en 'compraitem' (compra_id, producto_id, cantidad, precio)
   ↓
INSERT en 'compraestado' (compra_id, estado='iniciada', fecha_inicio)
```

### **5️⃣ Confirmación de Pago**

```
Order::markAsPaid()
   ↓
CLOSE estado anterior: UPDATE compraestado SET fecha_fin = NOW()
   ↓
CREATE nuevo estado: INSERT compraestado (estado='aceptada', fecha_inicio)
```

**Resultado**: Trazabilidad completa. Cada cambio de estado queda registrado con timestamp.

---

## 📊 **Gestión Administrativa**

### **Dashboard de Órdenes** (`ManagementController`)

```php
// El admin ve:
- Total de órdenes
- Órdenes por estado (iniciada, aceptada, enviada, entregada)
- Productos con stock bajo
- Última actualización en tiempo real
```

### **Panel de Órdenes** (`OrderManagementController`)

El admin puede:
1. **Listar** todas las órdenes
2. **Ver detalles**: items, cliente, direcciones, historial de estados
3. **Cambiar estado**: transiciones válidas según el estado actual
4. **Ver historial**: cada cambio de estado con fecha/hora exacta

### **Transiciones de Estado Validadas** (`app/Models/OrderStatus`)

```
iniciada → aceptada → procesada → enviada → entregada
           cancelada
```

Solo permite transiciones lógicas. Ej: no puede pasar de "entregada" a "cancelada".

---

## 🗄️ **Estructura de Base de Datos**

### **Tabla: `usuarios`**
```sql
- idusuario (PK)
- nombre, email, contraseña
- idrol (FK) → roles
- fecha_creacion
```

### **Tabla: `roles`**
```sql
- idrol (PK)
- nombre (Administrador, Cliente)
```

### **Tabla: `permisos`**
```sql
- idpermiso (PK)
- codigo (manage.orders, manage.products, etc)
- descripcion
```

### **Tabla: `rol_permiso`** (relación many-to-many)
```sql
- idrol (FK)
- idpermiso (FK)
```

### **Tabla: `compra` (Órdenes)**
```sql
- idcompra (PK)
- idusuario (FK)
- total, estado_actual
- fecha_creacion
```

### **Tabla: `compraitem` (Detalles)**
```sql
- idcompraitem (PK)
- idcompra (FK)
- idproducto (FK)
- cantidad, precio_unitario
```

### **Tabla: `compraestado` (Historial)**
```sql
- idcompraestado (PK)
- idcompra (FK)
- idcompraestadotipo (FK) → tipo de estado
- fecha_inicio, fecha_fin
```

**Ventaja**: Historial inmutable. Cada estado tiene fecha de inicio y fin, permitiendo auditoría completa.

---

## 🔒 **Seguridad Implementada**

### **1. Autenticación**
- ✅ Validación de sesión en cada petición
- ✅ Redireccionamiento al login si no está autenticado
- ✅ Almacenamiento seguro de contraseña (hash)

### **2. Autorización**
- ✅ Middleware valida permisos antes de acceder a recursos
- ✅ No se pueden acceder a rutas administrativas sin permisos
- ✅ Los usuarios cliente no ven panel admin

### **3. Validación de Datos**
- ✅ Stock se valida antes de crear orden
- ✅ Totales se recalculan en servidor (no confía en cliente)
- ✅ Solo cambios de estado válidos se permiten

### **4. Integridad de Datos**
- ✅ Transacciones en creación de órdenes
- ✅ Historial de estados inmutable
- ✅ FK garantizan consistencia

---

## 🚀 **Ejemplos de Flujos Clave**

### **Ejemplo 1: Admin cambia estado de orden**

```
GET /management/orders/123
   ↓
OrderManagementController::show(123)
   ↓
PermissionMiddleware::requireOrderManagement()  ← Valida permiso
   ↓
OrderModel::getOrderWithUserDetails(123)
OrderModel::getValidTransitions()
   ↓
Vista muestra botones solo con transiciones válidas
   ↓
POST /management/orders/123/update-status
   ↓
PermissionMiddleware::requireOrderManagement()
OrderStatus::createStatusRecord($orderId, 'enviada')
   ↓
Email enviado automáticamente al cliente
   ↓
Historial actualizado en compraestado
```

### **Ejemplo 2: Cliente compra**

```
POST /checkout/process
   ↓
CheckoutFlowController::process()
   ↓
PermissionMiddleware::requirePurchaseAccess()  ← Solo clientes autenticados
   ↓
Valida stock de cada item
Valida datos del cliente
   ↓
Order::create() → Crea compra + items + estado inicial
   ↓
OrderStatus::setInitialStatus('iniciada')
   ↓
Genera QR para pago
   ↓
POST /checkout/confirm-payment
   ↓
Order::markAsPaid()  ← Cierra estado anterior, crea 'aceptada'
   ↓
Email de confirmación
   ↓
Redirige a /my-orders
```

---

## 📈 **Dinámismo con Base de Datos**

Tu aplicación es completamente **dinámica**:

- ✅ **Catálogo**: Productos se cargan de BD, no están hardcodeados
- ✅ **Permisos**: Se validan contra tabla `permisos`, no en código
- ✅ **Estados**: Las transiciones válidas se obtienen de BD
- ✅ **Historial**: Cada acción del admin queda registrada
- ✅ **Stock**: Se valida en tiempo real contra BD
- ✅ **Emails**: Se envían dinámicamente según estado

---

## 📝 **Criterios de Calidad Cumplidos**

| Criterio                              | Estado | Implementación |
|---------------------------------------|--------|---------------------------------------|
| **Validación de autenticación**       |   ✅   | `PermissionMiddleware` valida sesión |
| **Autorización explícita**            |   ✅   | `PermissionManager` verifica permisos específicos |
| **Separación de responsabilidades**   |   ✅   | Controladores orquestan, modelos ejecutan, vistas presentan |
| **Registro de cambios de estado**     |   ✅   | Tabla `compraestado` con timestamp inicio/fin |
| **Integridad transaccional**          |   ✅   | Órdenes se crean atómicamente con items y estado |
| **Trazabilidad completa**             |   ✅   | Historial inmutable de estados con fechas exactas |

---

## 🎯 **Conclusión**

Amarena es una aplicación **profesional y robusta** que demuestra:
- ✅ Arquitectura MVC clara y mantenible
- ✅ Sistema de permisos granular y flexible
- ✅ Flujo de compra completo y trazable
- ✅ Seguridad en múltiples capas
- ✅ Base de datos normalizada e íntegra
- ✅ Código escalable y reutilizable