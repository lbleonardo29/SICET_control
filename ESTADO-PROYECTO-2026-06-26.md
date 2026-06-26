# SICET — Estado del proyecto

**Fecha:** 26 de junio 2026
**Repositorio:** https://github.com/lbleonardo29/SICET_control
**Entorno:** Laravel 9 · PHP 8.3 · MySQL remoto (`192.168.10.111`)

---

## 1. Roles del sistema (definición vigente)

| Rol | Qué puede hacer |
|---|---|
| **ADMIN** | CRUD completo de equipos, empleados y móviles · Asignar equipos · Ver todas las asignaciones · Ver estadísticas · Gestionar usuarios y roles |
| **USER** (empleado normal) | Ver sus equipos asignados · Aceptar/firmar/rechazar asignaciones pendientes · Descargar su carta responsiva en PDF · Ver su perfil |
| **RH** (Recursos Humanos) | **Solo lectura:** visualizar asignaciones (empleado + equipo + estado), computadoras y móviles |

> El antiguo rol **seguridad** fue reemplazado por **RH**. El módulo de reportes de entrada/salida fue retirado.

**Acceso de prueba (entorno local):** número de empleado + master password `1234`
- `#94` → admin · `#2360` (Edgar) → rh · cualquier otro → user

---

## 2. ✅ Completado y subido a GitHub

### 2.1 Corrección de bugs — commit `4aabe7c` (24-06-2026)
| Bug | Causa | Solución |
|---|---|---|
| Nombre de equipo se guardaba como "N/A" | `nombre_equipo` no estaba en `$fillable` del modelo `Equipo` | Agregado a `$fillable` (junto con `observaciones`, `fecha_baja`, `motivo_baja`, `tipo/capacidad_almacenamiento`) |
| Baja de equipos no guardaba motivo/fecha | Mismas columnas faltaban en `$fillable` | Corregido en el mismo cambio |
| Error `user_id cannot be null` al asignar | El login no vinculaba el `User` con su `Empleado` (`empleado_id` quedaba nulo) | Login ahora vincula `User.empleado_id` por `numero_empleado` |
| Asignar a empleado sin sesión fallaba | Columna `user_id` era obligatoria | Migración: `user_id` ahora acepta nulos + backfill de usuarios existentes |
| Correos no se mostraban | Faltaba re-sincronizar desde `tickets` | Re-sync: **249/254** empleados con correo |

### 2.2 Rol RH + retiro de reportes — commit `dbe6792` (24-06-2026)
- Rol `seguridad` renombrado a `rh` en datos, validaciones y filtros
- **RH** ve asignaciones (computadoras y móviles) en **solo lectura** — sin botones de crear/devolver/eliminar
- Rutas de reportes retiradas (`reportes.index/create/store/exportar`)
- Nuevo grupo de rutas `role:admin,rh` para los dashboards de asignaciones
- Sidebar reorganizado: bloque "Reportes" eliminado, bloque "Asignaciones" para RH
- Pantalla de gestión de usuarios: badge y selector usan "RH"

### 2.3 RBAC base — commit `ff8f859`
- `RoleMiddleware` soporta múltiples roles (`role:admin,rh`) mediante parámetros variádicos
- Directiva Blade `@role('admin','rh') ... @endrole` corregida para PHP 8.3 (procesa roles en tiempo de compilación)
- Rutas agrupadas por rol con bloqueo del lado del servidor

---

### 2.4 Catálogo unificado de equipos — commit `3008835` (26-06-2026)
- Un solo menú **"Equipos"** en el sidebar agrupa computadoras y móviles
- Ruta `/catalogo` muestra ambas tablas (`equipos` + `dispositivos_moviles`) mezcladas en una sola lista con paginación manual (`LengthAwarePaginator`)
- Etiqueta de tipo: 🖥️ **Computadora** / 📱 **Móvil**; identificador: No. Serie / IMEI
- Un buscador unificado encuentra por código, nombre, marca, modelo, serie o IMEI
- Filtros por tipo y por estado independientes
- Las tablas en BD siguen separadas — la unificación es solo de presentación
- Campo `direccion_mac` ahora se valida con formato `XX:XX:XX:XX:XX:XX` (regex) y se guarda en `store()`/`update()`

### 2.5 Sistema de notificaciones (campana) — commit `7d80c4e` (26-06-2026)
- Tabla `notifications` de Laravel activada (migración incluida)
- Clase `app/Notifications/SistemaNotificacion.php` — genérica, parámetros: titulo, mensaje, url, icono, tipo
- Clase `app/Http/Controllers/NotificacionController.php` — `index()`, `leer($id)`, `leerTodas()`
- **Campana del header:** contador de no leídas (rojo) · menú con últimas 8 · "Marcar todas" · "Ver todas" en `/notificaciones`
- **Disparadores:**
  - Admin asigna equipo → notificación al empleado
  - Empleado acepta → notificación a todos los admins
  - Empleado rechaza → notificación a todos los admins
- Vistas: `resources/views/notificaciones/index.blade.php` (historial completo)
- Comando de prueba: `php artisan sicet:test-mail <correo>`

### 2.6 Correos — redirect de pruebas + recuperación flexible — commit `7dee15a` (26-06-2026)
- **Redirección de pruebas:** variable `MAIL_REDIRECT_TO` en `.env` → todos los correos van a esa dirección vía `Mail::alwaysTo()` en `AppServiceProvider`. En producción: dejar en blanco o eliminar.
  ```
  MAIL_REDIRECT_TO="becario.tecnologia@fruitex.com.mx"
  ```
- **Recuperación de contraseña** acepta **correo o número de empleado** (antes solo correo, y muchos tienen correo placeholder)
- Login re-sincroniza `users.email` con el correo corporativo real en cada inicio de sesión
- Columna "Acciones" oculta para RH y USER en los dashboards de asignaciones (`@role('admin')/@endrole`)

### 2.7 Firma electrónica en asignaciones — commits `59c9a86` + `dd7898f` (26-06-2026)
**Flujo completo:**
1. Admin asigna → correo al empleado con enlace "Iniciar sesión en SICET" + notificación en campana
2. Empleado entra → se abre automáticamente un **modal** con la carta responsiva provisional + **canvas de firma** (librería `signature_pad` CDN)
3. Empleado dibuja su firma y da clic en "Firmar y aceptar":
   - Canvas captura PNG en base64 y lo envía a `PUT /asignaciones/firmar/{id}`
   - `AsignacionController@firmar()` valida, guarda `firma` + `fecha_firma`, marca estado `aceptada`
   - Regenera la carta PDF con la firma incrustada (DomPDF acepta `data:image/png;base64,...`)
   - Notifica a los admins
4. El PDF aparece en "Mis equipos" con botón **"Descargar carta (PDF)"** en la tarjeta del empleado
5. Rutas de descarga en grupo `auth` (antes eran solo-admin); el controlador verifica que solo el dueño o un admin puedan descargar

**Plantillas PDF (basadas en documentos oficiales):**
- `resources/views/pdf/carta_asignacion.blade.php` — CARTA RESPONSIVA EQUIPO DE CÓMPUTO PORTÁTIL
  - Fecha = `fecha_firma` (momento exacto de la firma)
  - Zona de aceptación: imagen PNG de la firma + "Firmado electrónicamente el dd/mm/aaaa hh:mm" + NOMBRE + NÚMERO DE EMPLEADO
  - Zona de entrega: "Edgar Alcántara"
- `resources/views/pdf/carta_asignacion_movil.blade.php` — CARTA RESPONSIVA EQUIPO DE COMUNICACIÓN MÓVIL
  - Campos: Marca, Modelo, IMEI, Características, No. de SIM, No. telefónico
  - Misma zona de aceptación con firma + NOMBRE + NÚMERO + fecha

**Migraciones agregadas:**
- `2026_06_24_120000_fix_user_id_nullable_and_link_empleados` — `user_id` nullable + backfill
- `2026_06_24_130000_rename_seguridad_role_to_rh`
- `2026_06_26_101903_create_notifications_table`
- `2026_06_26_140000_add_firma_to_asignaciones` — campos `firma` + `fecha_firma` en ambas tablas

---

## 3. ⏳ Pendiente por implementar

- **Correos oficiales en producción:** muchos empleados tienen correo placeholder (`{num}@sicet.fruitex.mx`). Los correos reales se actualizarán cuando estén completos en la BD corporativa. El login ya los sincroniza automáticamente en cada inicio de sesión.
- **Mapeo de plantas:** 155 empleados tienen `planta_id` nulo (la tabla `tickets.planta` no es accesible con el usuario actual). Falta el mapeo real planta↔empleado.
- **Limpieza de código muerto:** las vistas `reportes/*`, `layouts/app.blade.php` y `partials/sidebar.blade.php` ya no se usan. Pueden eliminarse en una sesión de limpieza posterior.
- **Responsive móvil:** diferido explícitamente.
- **Write-through a `tbl_empleados`:** deshabilitado (`TICKETS_WRITE_THROUGH=false`) hasta tener permisos de escritura en la BD corporativa.

---

## 4. Seguridad / configuración

- `.env` **no** se sube a git (está en `.gitignore`)
- `MASTER_PASSWORD` solo funciona con `APP_ENV=local`; en producción se ignora automáticamente
- `MAIL_REDIRECT_TO` es solo para pruebas — eliminar o dejar vacío en producción
- Credenciales corporativas (`TICKETS_DB_*`) viven solo en `.env`
- Autenticación contra `tickets.tbl_empleados` (bcrypt, solo lectura); sesión en `sicet.users`

---

## 5. Arquitectura de datos (resumen)

```
tickets.tbl_empleados (lectura)
        ↓ bcrypt check + sync email
sicet.users (sesión + rol)
        ↓ empleado_id
sicet.empleados (datos completos)
        ↓
sicet.asignaciones / asignaciones_moviles
        ↓ firma PNG base64 + fecha_firma
sicet.notifications (campana)
        ↓ DomPDF
storage/public/cartas/*.pdf (descargable por el empleado y admin)
```

---

*Documento de estado · SICET · Fruitex de México · 26-06-2026*
