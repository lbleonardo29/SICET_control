# SICET — Estado del proyecto

**Fecha:** 24 de junio 2026
**Repositorio:** https://github.com/lbleonardo29/SICET_control
**Entorno:** Laravel 9 · PHP 8.3 · MySQL remoto (`192.168.10.111`)

---

## 1. Roles del sistema (definición vigente)

| Rol | Qué puede hacer |
|---|---|
| **ADMIN** | CRUD completo de equipos, empleados y móviles · Asignar equipos · Ver todas las asignaciones · Ver estadísticas · Gestionar usuarios y roles |
| **USER** (empleado normal) | Ver sus equipos asignados · Aceptar/rechazar asignaciones pendientes · Ver su perfil |
| **RH** (Recursos Humanos) | **Solo lectura:** visualizar asignaciones (empleado + equipo + estado), computadoras y móviles |

> El antiguo rol **seguridad** fue reemplazado por **RH**. El módulo de reportes de entrada/salida fue retirado.

**Acceso de prueba (entorno local):** número de empleado + master password `1234`
- `#94` → admin · `#2360` (Edgar) → rh · cualquier otro → user

---

## 2. ✅ Completado y subido a GitHub

### 2.1 Corrección de bugs (commit `4aabe7c`)
| Bug | Causa | Solución |
|---|---|---|
| Nombre de equipo se guardaba como "N/A" | `nombre_equipo` no estaba en `$fillable` del modelo `Equipo` | Agregado a `$fillable` (junto con `observaciones`, `fecha_baja`, `motivo_baja`, `tipo/capacidad_almacenamiento`) |
| Baja de equipos no guardaba motivo/fecha | Mismas columnas faltaban en `$fillable` | Corregido en el mismo cambio |
| Error `user_id cannot be null` al asignar | El login no vinculaba el `User` con su `Empleado` (`empleado_id` quedaba nulo) | Login ahora vincula `User.empleado_id` por `numero_empleado` |
| Asignar a empleado sin sesión fallaba | Columna `user_id` era obligatoria | Migración: `user_id` ahora acepta nulos + backfill de usuarios existentes |
| Correos no se mostraban | Faltaba re-sincronizar desde `tickets` | Re-sync: **249/254** empleados con correo |

### 2.2 Rol RH + retiro de reportes (commit `dbe6792`)
- Rol `seguridad` renombrado a `rh` en datos, validaciones y filtros
- **RH** ve asignaciones (computadoras y móviles) en **solo lectura** — sin botones de crear/devolver/eliminar
- Rutas de reportes retiradas (`reportes.index/create/store/exportar`)
- Nuevo grupo de rutas `role:admin,rh` para los dashboards de asignaciones
- Sidebar reorganizado: bloque "Reportes" eliminado, agregado bloque "Asignaciones" para RH
- Pantalla de gestión de usuarios: badge y selector usan "RH"

### 2.3 RBAC base (entregado previamente — commit `ff8f859`)
- `RoleMiddleware` soporta múltiples roles (`role:admin,rh`)
- Directiva Blade `@role('admin','rh') ... @endrole`
- Rutas agrupadas por rol con bloqueo del lado del servidor

---

## 3. ⏳ Pendiente por implementar

### Fase 2 — Unificar equipos (computadoras + móviles)
**Decisión:** unificar solo a nivel de menú/listado (UI). Las tablas `equipos` y `dispositivos_moviles` se mantienen separadas.
- Un solo menú "Equipos" que muestra ambos tipos
- En las búsquedas/listados, etiqueta de tipo (**Computadora** / **Móvil**)
- Consolidar las dos secciones del sidebar en una sola

### Fase 3 — Sistema de notificaciones
- Activar notificaciones de Laravel (canal de base de datos)
- Campana del header funcional (contador + lista)
- Notificar en eventos: nueva asignación, aceptación/rechazo, devolución, cambios relevantes

### Fase 4 — Confirmación de asignación con firma electrónica
**Decisión:** firma dibujada en canvas, incrustada en PDF.
- Al crear una asignación → correo al empleado con enlace de confirmación
- El empleado firma electrónicamente (dibujo en recuadro)
- Se genera la carta responsiva en PDF con la firma incrustada
- Requiere ajustes en la base de datos (campos de firma/confirmación)
- **Pendiente del cliente:** plantilla oficial de la carta (se usará una provisional)

---

## 4. Otros pendientes / notas

- **Mapeo de plantas:** 155 empleados tienen `planta_id` nulo (la tabla `tickets.planta` no es accesible con el usuario actual). Falta el mapeo real planta↔empleado.
- **Plantilla de carta responsiva:** pendiente de entrega por el cliente.
- **Write-through a `tbl_empleados`:** deshabilitado (`TICKETS_WRITE_THROUGH=false`) hasta tener permisos de escritura.
- **Limpieza de código muerto:** las vistas `reportes/*`, `layouts/app.blade.php` y `partials/sidebar.blade.php` ya no se usan (referencian rutas retiradas). Se pueden eliminar en una limpieza posterior.
- **Responsive móvil:** diferido explícitamente.

---

## 5. Seguridad / configuración

- `.env` **no** se sube a git (está en `.gitignore`)
- `MASTER_PASSWORD` solo funciona con `APP_ENV=local`; en producción se ignora
- Credenciales corporativas (`TICKETS_DB_*`) viven solo en `.env`
- Autenticación contra `tickets.tbl_empleados` (bcrypt, solo lectura)

---

*Documento de estado · SICET · Fruitex de México · 24-06-2026*
