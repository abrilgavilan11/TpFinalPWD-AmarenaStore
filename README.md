# Presentación — Proyecto Amarena

Buenos días. En esta presentación voy a exponer el proyecto Amarena: una tienda online construida con PHP siguiendo un patrón MVC, utilizando MySQL como base de datos y desplegada típicamente sobre XAMPP.

Mi objetivo es mostrar cómo está organizada la aplicación, qué funcionalidades ofrece para los roles de Usuario y Admin, y demostrar cómo se cumplen los criterios de validación, autorización, separación de responsabilidades y registro de estados en todo el flujo de compra.

**Estructura del proyecto (resumen rápido)**

- `app/Controllers/`: controladores que coordinan las peticiones y validaciones.
- `app/Models/`: modelos que encapsulan lógica de negocio y acceso a datos (`Product`, `Order`, `OrderStatus`, `User`).
- `app/Views/vistas/`: vistas organizadas por áreas (`tienda`, `admin`, `checkout`).
- `app/Middleware/`: middleware para autenticación y control de permisos.
- `app/Utils/`: utilidades (sesión, envío de email, helpers).
- `public/`: activos públicos y punto de entrada para el servidor web.

Ahora describo las funcionalidades más importantes, pensadas como un guion que leeré en voz alta en la demo.

**Funcionalidades para el rol Usuario (cliente)**

1. Navegación por catálogo: el cliente puede listar productos, aplicar filtros por categoría y buscar por texto.
2. Carrito y resumen: el cliente agrega productos al carrito, revisa cantidades y subtotales; el total se calcula dinámicamente.
3. Checkout por pasos: se solicitan los datos del cliente, se valida stock, se muestra resumen y se genera un QR/token para pago.
4. Confirmación de pago: al confirmar el pago (simulado), el sistema marca la orden como pagada y actualiza el historial de estados.
5. Historial de órdenes: el cliente puede ver sus órdenes y el estado actual.

**Funcionalidades para el rol Admin (gestión)**

1. Panel de órdenes: listado de órdenes con estado actual y búsqueda.
2. Detalle de orden: vista completa con items, totales y el historial cronológico de estados (fechas de inicio y fin).
3. Gestión de productos: crear, editar, publicar/despublicar y controlar stock.
4. Cambio de estados: el administrador puede cambiar estados de la orden; cada cambio queda registrado en la tabla `compraestado`.

A continuación, lector, explico y demuestro cómo el proyecto cumple los criterios de calidad solicitados. Lee esto tal como lo diría en la exposición.

**1) Proceso de validación: control de autenticación**

En Amarena, todas las rutas y controladores que pertenecen al área administrativa invocan un middleware de permisos. En la práctica esto significa que antes de ejecutar la lógica de un controlador comprobamos que exista una sesión válida y que el usuario esté autenticado. Por ejemplo, `OrderManagementController::index()` llama a `PermissionMiddleware::requireOrderManagement()`; si la comprobación falla, el acceso se niega y se redirige al login.

Lectura en voz: "Antes de mostrar listas administrativas validamos la sesión y el rol. Si no hay sesión válida, no se continúa."

**2) Proceso de validación: control de permisos por recurso**

Además de la autenticación, cada recurso sensible está protegido por una autorización explícita. El middleware no sólo verifica que el usuario esté logueado sino que tenga el permiso requerido para la acción (por ejemplo: gestión de órdenes). Esto evita accesos directos por URL y asegura que las transiciones de estado solo las ejecuten usuarios con privilegios.

Lectura en voz: "Cada endpoint sensible valida el permiso específico antes de ejecutar la acción."

**3) Las acciones conocen el proceso — separación de responsabilidades**

La arquitectura sigue la regla: los controladores orquestan, los modelos ejecutan. Las acciones de los controladores se limitan a validar entradas, instanciar el modelo o servicio correspondiente y llamar al método que ejecuta la transacción. Por ejemplo, `CheckoutFlowController` prepara los datos y delega en `Order::create()` y en `OrderStatus` para registrar estados.

Lectura en voz: "Los controladores no contienen la lógica de negocio; esta está centralizada en los modelos."

**4) Ejecución completa de un proceso de compra**

Describo el flujo en voz:

- El cliente agrega productos al carrito y solicita checkout.
- El sistema valida stock y datos del cliente.
- Se crea la orden (registro en `compra` y `compraitem`) y se añade un estado inicial `iniciada` en `compraestado`.
- Se genera un QR/token para el pago y se espera la confirmación.
- Al recibir la confirmación, `Order::markAsPaid()` cierra el estado anterior (pone `cefechafin`) y crea una nueva fila `aceptada` con `cefechaini`.

Lectura en voz: "Este flujo garantiza integridad y trazabilidad desde la creación hasta la confirmación del pago."

**5) Registro correcto de cambios de estado**

Cada transición se registra en `compraestado` con `cefechaini` y, cuando corresponde, se rellena `cefechafin` del estado anterior. Esto permite reconstruir el historial cronológico completo de una orden.

Lectura en voz: "El historial está disponible en la vista administrativa y puede consultarse con una simple consulta SQL."

**6) Estructura independiente de roles y menú dinámico**

La aplicación está diseñada para que los modelos sean independientes del rol; la interfaz (vistas y menú) se adapta según los permisos del usuario. El menú se genera dinámicamente consultando la sesión y mostrando sólo los enlaces permitidos.

Lectura en voz: "Agregar o modificar roles no requiere cambiar la capa de datos."

**Comprobaciones y comandos que utilizaré en la demo (leer y ejecutar)**

1. Ejecutar localmente (XAMPP): asegúrate de que `amarena` esté en `C:/xampp/htdocs/`, inicia Apache y MySQL desde el panel de XAMPP y abre `http://localhost/amarena/`.

2. Alternativa de desarrollo (servidor embebido PHP):

```bash
cd C:/xampp/htdocs/amarena
php -S localhost:8000 -t public
```

3. Consulta SQL para mostrar el historial de una orden (leer en voz y luego ejecutar):

```sql
SELECT ce.*, cet.cetdescripcion, cet.cetdetalle
FROM compraestado ce
JOIN compraestadotipo cet ON ce.idcompraestadotipo = cet.idcompraestadotipo
WHERE ce.idcompra = 123
ORDER BY ce.cefechaini DESC;
```

Reemplazar `123` por el `idcompra` de la orden que vayamos a mostrar.

**Diagrama de secuencia**

He incluido un diagrama de secuencia en `docs/sequence.puml` que muestra la interacción entre `CheckoutFlowController`, `Order` y `OrderStatus` durante la compra y la confirmación de pago. En la demo lo abriré para repasar las llamadas y los puntos donde se guardan los estados.

**Archivos que voy a abrir en la demo (mención para lectura en voz)**

- `app/Controllers/CheckoutFlowController.php` — para seguir el flujo de checkout.
- `app/Models/Order.php` — para explicar `create`, `getStatusHistory` y `markAsPaid`.
- `app/Views/vistas/admin/order_detail.php` — para mostrar el historial con `cefechaini` y `cefechafin`.

**Cierre (últimas líneas para la exposición)**

Con esto concluye la presentación técnica de Amarena. Hemos recorrido la estructura del proyecto, mostrado las funcionalidades clave para cada rol y verificado que los criterios de autenticación, autorización, separación de responsabilidades y trazabilidad de estados están implementados y son verificables.

Gracias. Ahora procedo a abrir los archivos en el entorno para la demostración en vivo.
