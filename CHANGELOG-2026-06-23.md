# SICET — Changelog 23 de junio 2026

## Resumen del día

Sesión de desarrollo completa: desde cero errores de sintaxis PHP hasta autenticación corporativa, sincronización de empleados, gestión de usuarios y control de acceso por roles (RBAC).

---

## 1. Corrección de errores de sintaxis PHP (`exit 255`)

**Problema:** `php artisan` fallaba con código de salida 255 — ningún comando funcionaba.

**Causa raíz:** Un patrón `// }` y `// ];` en 6 archivos comentaba involuntariamente las llaves y corchetes de cierre, rompiendo el parser de PHP.

**Archivos corregidos:**
- `app/Exceptions/Handler.php`
- `app/Providers/AppServiceProvider.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Http/Middleware/EncryptCookies.php`
- `app/Http/Middleware/PreventRequestsDuringMaintenance.php`

**4 migraciones** con el mismo patrón en sus métodos `down()`:
- `2026_02_19_175615_add_role_to_users`
- `2026_02_19_205945_add_profile_photo_to_users_table`
- `2026_02_25_043201_add_observaciones_to_equipos_table`
- `2026_03_04_211150_add_carta_pdf_to_asignaciones_moviles_table`

---

## 2. Conexión a base de datos remota

**Servidor:** `192.168.10.111`

| Base de datos | Uso |
|---|---|
| `sicet` | Tablas propias del sistema (equipos, empleados, asignaciones, etc.) |
| `tickets` | Directorio corporativo — solo lectura (`tbl_empleados`) |

**Configuración en `.env`** (nunca se sube a git):
```
DB_HOST=192.168.10.111
DB_USERNAME=sicet_user
TICKETS_DB_HOST=192.168.10.111
TICKETS_DB_USERNAME=sicet_user
```

---

## 3. Autenticación corporativa

**Flujo implementado:**
1. El usuario ingresa su **número de empleado** (numérico)
2. Se busca en `tickets.tbl_empleados` (modelo `EmpleadoTicket`, solo lectura)
3. Se verifica la contraseña contra el hash bcrypt almacenado en `contrasenia`
4. Si es válido, se crea o actualiza el registro en `sicet.users` y se inicia sesión

**Modelo `EmpleadoTicket`:**
- `$connection = 'tickets'`
- `$table = 'tbl_empleados'`
- `$primaryKey = 'id_emp'`

**Campos verificados:** `activo = 'S'` (string, no entero)

**Archivos modificados:**
- `app/Http/Controllers/AdminController.php` — métodos `showLogin()`, `login()`
- `resources/views/admin/login.blade.php` — placeholder "Número de empleado"

---

## 4. Master password para pruebas

**Solo activo cuando `APP_ENV=local`** — ignorado completamente en producción.

```env
MASTER_PASSWORD=1234
```

```php
$masterPassword = config('app.env') === 'local' ? env('MASTER_PASSWORD') : null;
$passwordValida = Hash::check($password, $empleado->contrasenia)
    || ($masterPassword && $password === $masterPassword);
```

---

## 5. Plantas y sincronización de empleados

**Problema:** `sicet_user` no tiene acceso a `tickets.planta`, por lo que las plantas se crearon manualmente:

| ID Corp | Nombre |
|---|---|
| 1 | SAUCES |
| 2 | JARDIN |
| 3 | PARTIDAS |

**Sincronización (`php artisan sicet:sync-empleados`):**
- Antes: omitía empleados sin `id_planta` → solo 9 sincronizados
- Después: `planta_id` es nullable → **164 empleados sincronizados**

**Migración nueva:** `2026_06_23_100001_make_planta_id_nullable_in_empleados`

---

## 6. Gestión de usuarios (`/usuarios`)

**Vista:** tabla con número de empleado, nombre, avatar, rol actual y dropdown para cambiar rol.

**Roles disponibles:** `admin` · `seguridad` · `user`

**Reglas:**
- Un admin no puede quitarse su propio rol
- Solo admin puede acceder a `/usuarios`

**Roles iniciales asignados:**
- Empleado **#94** → `admin`
- Empleado **#2360** (Edgar) → `seguridad` (para pruebas)

---

## 7. RBAC — Control de acceso por roles

### Roles y permisos

| Permiso | admin | seguridad | user |
|---|:---:|:---:|:---:|
| CRUD equipos/empleados/móviles | ✓ | | |
| Asignar equipos | ✓ | | |
| Ver todas las asignaciones | ✓ | | |
| Ver estadísticas globales | ✓ | | |
| Ver todos los reportes | ✓ | | |
| Exportar reportes CSV | ✓ | | |
| Crear reportes entrada/salida | ✓ | ✓ | |
| Ver historial de sus reportes | ✓ | ✓ | |
| Ver sus equipos asignados | ✓ | ✓ | ✓ |
| Aceptar/rechazar asignaciones propias | ✓ | ✓ | ✓ |
| Ver perfil | ✓ | ✓ | ✓ |

### Archivos modificados

**`app/Http/Middleware/RoleMiddleware.php`**
- Antes: aceptaba un solo rol (`$role`)
- Después: variadic `...$roles` — permite `role:admin,seguridad` en rutas

**`routes/web.php`** — 4 grupos de middleware:
1. `auth` — todos los usuarios autenticados (dashboard, perfil, aceptar/rechazar)
2. `auth + role:admin,seguridad` — crear y ver reportes
3. `auth + role:admin` — CRUD completo, asignaciones globales, usuarios, exportar
4. `auth` — API de búsqueda de empleados

**Bug corregido:** `equipos.baja` y `moviles.baja` estaban fuera de cualquier middleware (sin protección). Ahora están dentro del grupo admin.

**`resources/views/layouts/sicet.blade.php`** — sidebar segmentado:
- **user:** Dashboard + Perfil
- **seguridad:** Dashboard + Reportes (historial + nuevo reporte)
- **admin:** Todo lo anterior + Computadoras, Dispositivos, Empleados, Sistema (Usuarios)

**`app/Http/Controllers/ReporteController.php`** — método `index()`:
- Admin ve todos los reportes
- Seguridad solo ve los suyos (`where('user_id', Auth::id())`)

### Directiva Blade `@role` / `@endrole`

**Corrección en `AppServiceProvider`:** la implementación anterior generaba un `str_replace` en runtime que fallaba en PHP 8.3 con dos argumentos. Ahora procesa los roles en tiempo de compilación:

```php
Blade::directive('role', function ($expression) {
    $roles = array_map(fn($r) => trim(trim($r), "\"'"), explode(',', $expression));
    $json  = json_encode($roles);
    return "<?php if(Auth::check() && in_array(Auth::user()->role, {$json})): ?>";
});
```

Uso en vistas:
```blade
@role('admin')
    {{-- solo visible para admin --}}
@endrole

@role('admin', 'seguridad')
    {{-- visible para admin o seguridad --}}
@endrole
```

---

## Acceso al sistema (entorno local)

| Empleado | Rol | Contraseña |
|---|---|---|
| #94 | admin | `1234` (master) |
| #2360 | seguridad | `1234` (master) |
| Cualquier otro | user | `1234` (master) |

> La master password (`1234`) solo funciona con `APP_ENV=local`. En producción, cada empleado usa su contraseña corporativa de tickets.

---

## Estado de la BD remota

- **Servidor:** `192.168.10.111` (MySQL)
- **Base SICET:** `sicet` — 42 migraciones aplicadas
- **Empleados sincronizados:** 164
- **Plantas:** SAUCES, JARDIN, PARTIDAS

---

*Generado el 23-06-2026 · SICET v1.0 · Fruitex de México*
