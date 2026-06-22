# 📋 Informe Técnico — Proyecto SICET
**Sistema de Control de Equipos de Trabajo**
**Fecha:** 18 de junio de 2026
**Repositorio:** https://github.com/lbleonardo29/SICET_control.git
**Directorio local:** `C:\SICET`

---

## 1. Descripción General del Proyecto

**SICET** es un sistema web interno para la gestión y control de equipos de cómputo y dispositivos móviles dentro de una organización con múltiples plantas (Jardín, Partidas, Sauces). Permite:

- Registrar y dar seguimiento a **computadoras** y **dispositivos móviles**
- **Asignar** equipos a empleados mediante cartas responsivas en PDF
- Controlar el **ciclo de vida** de los equipos: disponible → asignado → devuelto → baja
- Manejar **reportes de seguridad** (inconsistencias en activos)
- Gestionar **empleados**, **usuarios** y **perfiles**
- Sistema de **aceptación/rechazo** de asignaciones por parte del empleado
- **Recuperación de contraseña** por correo electrónico

---

## 2. Stack Tecnológico

### Backend
| Tecnología | Versión | Uso |
|---|---|---|
| **PHP** | 8.3.30 | Lenguaje principal |
| **Laravel** | 9.x | Framework web MVC |
| **MySQL** | (Laragon) | Base de datos relacional |
| **Laravel Sanctum** | 3.x | Autenticación API (tokens) |
| **Laravel DomPDF** | 2.2.x | Generación de cartas responsivas PDF |
| **Doctrine DBAL** | 3.x | Modificación de columnas en migraciones |
| **Guzzle** | 7.x | Cliente HTTP |
| **yajra/laravel-oci8** | 9.x | Conector Oracle (residual, NO se usa actualmente) |

### Frontend
| Tecnología | Versión | Uso |
|---|---|---|
| **Vite** | 4.x | Bundler de assets (CSS/JS) |
| **Bootstrap** | 5.3.0 | Framework CSS (cargado por CDN) |
| **Bootstrap Icons** | 1.11.3 | Iconografía |
| **Google Fonts (Inter)** | - | Tipografía |
| **Axios** | 1.1.2 | Peticiones HTTP asíncronas |
| **Blade** | (Laravel 9) | Motor de templates |

### Entorno de Desarrollo
| Herramienta | Uso |
|---|---|
| **Laragon** | Servidor local (Apache + MySQL + PHP) |
| **HeidiSQL** | Administración de base de datos (incluido en Laragon) |
| **Composer** | Gestor de dependencias PHP |
| **npm** | Gestor de dependencias JavaScript |
| **Git** | Control de versiones |
| **GitHub** | Repositorio remoto |

---

## 3. Arquitectura de la Aplicación

### 3.1 Modelos (Eloquent ORM)

| Modelo | Tabla | Descripción |
|---|---|---|
| `User` | `users` | Usuarios del sistema (admin, user, seguridad) |
| `Empleado` | `empleados` | Empleados de la organización |
| `Equipo` | `equipos` | Computadoras/laptops |
| `DispositivoMovil` | `dispositivos_moviles` | Celulares, tablets |
| `Asignacion` | `asignaciones` | Asignaciones de computadoras a empleados |
| `AsignacionMovil` | `asignaciones_moviles` | Asignaciones de móviles a empleados |
| `Planta` | `plantas` | Ubicaciones/plantas de la empresa |
| `Reporte` | `reportes` | Reportes de seguridad/inconsistencias |

### 3.2 Controladores

| Controlador | Responsabilidad |
|---|---|
| `AdminController` | Login, logout, dashboard principal |
| `EquipoController` | CRUD de computadoras, historial, dar de baja |
| `EmpleadoController` | CRUD de empleados, activar/desactivar |
| `AsignacionController` | Asignar/devolver computadoras, cartas responsivas PDF, aceptar/rechazar |
| `AsignacionMovilController` | Asignar/devolver móviles, cartas responsivas PDF, aceptar/rechazar |
| `DispositivoMovilController` | CRUD de dispositivos móviles, historial, dar de baja |
| `ReporteController` | Crear y listar reportes de seguridad, exportar |
| `ProfileController` | Perfil de usuario, foto, cambio de contraseña |
| `PasswordResetController` | Recuperación de contraseña por email |

### 3.3 Middleware

| Middleware | Uso |
|---|---|
| `Authenticate` | Verificar que el usuario esté logueado, redirige a `/login` |
| `RoleMiddleware` | Verificar que el usuario tenga el rol requerido (`admin`, `user`, `seguridad`) |

### 3.4 Estructura de Rutas

```
/ → Redirige a /login

PÚBLICAS:
  GET  /login                    → Formulario de login
  POST /login                    → Procesar login
  POST /logout                   → Cerrar sesión
  GET  /forgot-password          → Formulario de recuperación
  POST /forgot-password          → Enviar enlace de reset
  GET  /reset-password/{token}   → Formulario nueva contraseña
  POST /reset-password           → Procesar nueva contraseña

AUTENTICADO (cualquier rol):
  GET  /dashboard                → Panel principal (diferente para admin vs user)
  GET  /perfil                   → Ver perfil
  PUT  /perfil                   → Actualizar perfil
  GET  /cambiar-password         → Formulario cambio contraseña (primer inicio)
  POST /cambiar-password         → Procesar cambio contraseña
  GET  /asignaciones             → Ver asignaciones de computadoras
  GET  /asignaciones/dashboard   → Dashboard de asignaciones
  GET  /asignaciones-moviles     → Dashboard de asignaciones móviles
  PUT  /asignaciones/aceptar/{id}   → Aceptar asignación
  PUT  /asignaciones/rechazar/{id}  → Rechazar asignación
  GET  /empleados                → Listar empleados
  GET  /moviles                  → Listar móviles

SOLO ADMIN:
  CRUD /equipos                  → Gestión completa de computadoras
  CRUD /empleados                → Crear, editar, activar/desactivar empleados
  CRUD /moviles                  → Gestión completa de dispositivos móviles
  POST /asignaciones             → Crear asignaciones de computadoras
  POST /asignaciones-moviles     → Crear asignaciones de móviles
  GET  /reportes                 → Ver reportes de seguridad
  GET  /reportes/exportar        → Exportar reportes

SOLO SEGURIDAD:
  GET  /reportes/create          → Formulario crear reporte
  POST /reportes                 → Guardar reporte

API (en web.php):
  GET  /api/empleado/{id}/computadoras  → Computadoras asignadas a un empleado
  GET  /api/empleados/search            → Búsqueda de empleados (autocompletado)
```

### 3.5 Vistas (Blade Templates)

```
resources/views/
├── layouts/           → Layout principal (navbar, sidebar, etc.)
├── admin/             → Login y dashboard
├── equipos/           → CRUD de computadoras
├── empleados/         → CRUD de empleados
├── asignaciones/      → Gestión de asignaciones de computadoras
├── asignaciones_moviles/ → Gestión de asignaciones de móviles
├── moviles/           → CRUD de dispositivos móviles
├── reportes/          → Reportes de seguridad
├── perfil/            → Perfil de usuario
├── pdf/               → Templates para cartas responsivas PDF
├── auth/              → Vistas de recuperación de contraseña
├── emails/            → Templates de correo electrónico
├── partials/          → Componentes reutilizables
└── vendor/            → Vistas de paquetes de terceros
```

---

## 4. Esquema de Base de Datos

### Tablas principales (14 tablas en total)

#### `users`
```
id, name, email, password, role (admin|user|seguridad), 
profile_photo, empleado_id (FK → empleados.id), 
primer_inicio, password_original, area, numero_empleado,
email_verified_at, remember_token, timestamps
```

#### `empleados`
```
id, numero_empleado (unique), nombre_completo, correo,
planta_id (FK → plantas.id), activo (boolean), timestamps
```

#### `equipos`
```
id, codigo_interno (auto: SICET-0001), marca, modelo, 
numero_serie, color, procesador, ram, ssd, cargador,
fecha_adquisicion, planta_id (FK → plantas.id), 
estado (Disponible|Asignado|Pendiente|Baja),
observaciones, direccion_mac, 
motivo_baja, fecha_baja, estado_baja,
sistema_operativo, tipo_disco, almacenamiento,
timestamps
```

#### `dispositivos_moviles`
```
id, codigo_interno, marca, modelo, imei, 
numero_sim, numero_telefono, caracteristicas,
estado (Disponible|Asignado|Pendiente|Baja), 
asignado (boolean),
motivo_baja, fecha_baja, estado_baja, timestamps
```

#### `asignaciones`
```
id, equipo_id (FK → equipos.id), empleado_id (FK → empleados.id),
user_id (FK → users.id), fecha_asignacion, fecha_devolucion,
carta_pdf, estado_asignacion (pendiente|aceptada|rechazada),
estado, token_confirmacion, timestamps
```

#### `asignaciones_moviles`
```
id, dispositivo_movil_id (FK → dispositivos_moviles.id),
empleado_id (FK → empleados.id), user_id (FK → users.id),
fecha_asignacion, fecha_devolucion, carta_pdf,
estado_asignacion (pendiente|aceptada|rechazada),
estado, token_confirmacion, timestamps
```

#### `plantas`
```
id, nombre (Jardín, Partidas, Sauces), timestamps
```

#### `reportes`
```
id, matricula, area, inconsistencias, tipo,
user_id (FK → users.id), numero_empleado, timestamps
```

#### Otras tablas
- `password_resets` — Tokens de recuperación de contraseña
- `password_reset_tokens` — Tokens adicionales de reset
- `failed_jobs` — Jobs fallidos de Laravel
- `personal_access_tokens` — Tokens de Sanctum
- `migrations` — Control de migraciones

---

## 5. Sistema de Autenticación

### Login
- Se ingresa con **número de empleado** (solo números) + contraseña
- El sistema busca al usuario por `empleado_id` o `email`
- Se implementó un **MASTER_PASSWORD** en `.env` que permite acceder a cualquier cuenta sin verificar el hash de contraseña (para acceso de emergencia/administración)
- Al primer inicio (`primer_inicio = 1`), se redirige a cambiar contraseña

### Roles
| Rol | Permisos |
|---|---|
| `admin` | Acceso total: CRUD de equipos, empleados, móviles, asignaciones, reportes |
| `user` | Ver dashboard, aceptar/rechazar asignaciones, ver perfil |
| `seguridad` | Todo lo de user + crear reportes de inconsistencias |

### Flujo de contraseña
1. Admin crea empleado → se genera usuario con contraseña temporal
2. Empleado inicia sesión → se fuerza cambio de contraseña (`primer_inicio`)
3. Si olvida contraseña → recuperación por email (SMTP Gmail)

---

## 6. Configuración de Correo Electrónico

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="microsoftoffice365ofertas@gmail.com"
MAIL_PASSWORD="[contraseña de aplicación Gmail]"
```

Se usa para:
- Enviar cartas responsivas por correo
- Enviar enlaces de recuperación de contraseña

---

## 7. Errores Encontrados y Correcciones Realizadas

### 🔴 Error 1: Extensión OCI8 faltante
- **Problema:** `composer install` fallaba porque el paquete `yajra/laravel-oci8` requiere la extensión `ext-oci8` de Oracle
- **Causa:** El paquete es residual de una fase anterior y no se usa (el proyecto usa MySQL)
- **Solución:** `composer install --ignore-platform-req=ext-oci8`
- **Estado:** ⚠️ Temporal. Se debería remover `yajra/laravel-oci8` del `composer.json`

### 🔴 Error 2: Migraciones con tabla inexistente `tbl_empleados`
- **Problema:** Varias migraciones referencian `tbl_empleados` con PK `id_emp`, pero la tabla real se creó como `empleados` con PK `id`
- **Archivos afectados:**
  - `2026_02_23_184803_add_empleado_id_to_users_table.php` — Referencia `tbl_empleados.id_emp`
  - `2026_02_24_190226_fix_foreign_empleado_asignaciones.php` — Intenta alterar FK a `tbl_empleados`
  - `2026_02_27_200413_create_asignaciones_moviles_table.php` — FK a `tbl_empleados.id_emp`
  - `2026_04_23_161214_add_numero_empleado_to_tbl_empleados_table.php` — Altera tabla inexistente
- **Correcciones aplicadas:**
  - Migración `add_empleado_id_to_users_table`: Cambiado `tbl_empleados.id_emp` → `empleados.id`
  - Migración `fix_foreign_empleado_asignaciones`: Convertida en **no-op** (la FK ya es correcta)
  - Migración `create_asignaciones_moviles_table`: Cambiado a `foreignId('empleado_id')->constrained('empleados')`
  - Migración `add_numero_empleado_to_tbl_empleados_table`: Convertida en **no-op**

### 🔴 Error 3: Tipo de columna incompatible en FK
- **Problema:** La migración `fix_foreign_empleado_asignaciones` cambiaba `empleado_id` de `unsignedBigInteger` a `integer`, lo cual es incompatible con la FK a `empleados.id` (que es `unsignedBigInteger`)
- **Solución:** Migración convertida en no-op

### 🔴 Error 4: Modelo `Empleado` desalineado con la BD
- **Problema:** El modelo definía `$table = 'tbl_empleados'` y `$primaryKey = 'id_emp'`, pero la tabla real es `empleados` con PK `id`
- **Corrección aplicada:** Actualizado a `$table = 'empleados'`, `$primaryKey = 'id'`, y `$fillable` ajustado a las columnas reales (`numero_empleado`, `nombre_completo`, `correo`, `activo`, `planta_id`)

### 🟡 Error 5: `.env` expuesto en el repositorio
- **Problema:** El archivo `.env` con credenciales sensibles (MAIL_PASSWORD, APP_KEY) estaba trackeado en Git
- **Solución:** Se agregó `.env` al `.gitignore` y se removió del tracking con `git rm --cached .env`
- **Nota:** Se revirtió temporalmente para que un compañero pudiera clonar las credenciales, y luego se volvió a proteger

---

## 8. Bugs Pendientes (NO corregidos aún)

> [!CAUTION]
> Los siguientes archivos todavía referencian `tbl_empleados` y/o `id_emp`. **El sistema no funcionará correctamente** en los módulos afectados hasta que se corrijan.

### Modelos con referencias obsoletas
| Archivo | Línea | Problema |
|---|---|---|
| `app/Models/User.php` | L38 | `belongsTo(Empleado::class, 'empleado_id', 'id_emp')` → debería ser `'id'` |
| `app/Models/Asignacion.php` | L49-52 | `belongsTo(Empleado::class, 'empleado_id', 'id_emp')` → debería ser `'id'` |
| `app/Models/AsignacionMovil.php` | L44-47 | `belongsTo(Empleado::class, 'empleado_id', 'id_emp')` → debería ser `'id'` |
| `app/Models/Reporte.php` | L28 | `belongsTo(Empleado::class, 'numero_empleado', 'id_emp')` → debería ser `'id'` |

### Controladores con queries a tabla inexistente
| Archivo | Línea(s) | Problema |
|---|---|---|
| `EmpleadoController.php` | L23, 39, 56, 63, 81, 89, 119, 134 | Usa `id_emp` en queries, validaciones y relaciones |
| `AsignacionController.php` | L124, 179, 394 | Valida contra `tbl_empleados,id_emp` y query con `id_emp` |
| `AsignacionMovilController.php` | L117 | Valida contra `tbl_empleados,id_emp` |
| `ReporteController.php` | L22, 25, 35 | Usa `id_emp` en selects y validaciones |
| `PasswordResetController.php` | L28-29 | `DB::table('tbl_empleados')` directo |

### Rutas con queries directos
| Archivo | Línea(s) | Problema |
|---|---|---|
| `routes/web.php` | L170-193 | API de búsqueda usa `DB::table('tbl_empleados')` con columnas viejas (`nombre`, `apellidos`, `area`, `id_emp`) |

### Vistas Blade con referencias obsoletas
| Vista | Problema |
|---|---|
| `empleados/index.blade.php` | Referencias a `id_emp` |
| `empleados/edit.blade.php` | Referencias a `id_emp` |
| `asignaciones/dashboard.blade.php` | Referencias a `id_emp` |
| `asignaciones/index.blade.php` | Referencias a `id_emp` |
| `asignaciones_moviles/dashboard.blade.php` | Referencias a `id_emp` |
| `asignaciones_moviles/create.blade.php` | Referencias a `id_emp` |
| `moviles/asignar.blade.php` | Referencias a `id_emp` |
| `reportes/create.blade.php` | Referencias a `id_emp` |
| `pdf/carta_asignacion_movil.blade.php` | Referencias a `id_emp` |

---

## 9. Resumen del Estado Actual

| Aspecto | Estado |
|---|---|
| Repositorio clonado | ✅ |
| `.env` protegido en `.gitignore` | ✅ |
| Dependencias PHP instaladas | ✅ (con `--ignore-platform-req=ext-oci8`) |
| Dependencias JS instaladas | ✅ |
| Migraciones ejecutadas | ✅ (39/39 DONE) |
| Modelo `Empleado` corregido | ✅ |
| Migraciones con `tbl_empleados` corregidas | ✅ |
| MASTER_PASSWORD implementado | ✅ (`SicetMaster2026`) |
| Usuario admin creado | ✅ (empleado_id=1, email=admin@sicet.com) |
| Login funcional | ✅ |
| **Modelos con `id_emp` pendientes** | ❌ 4 archivos |
| **Controladores con `tbl_empleados`/`id_emp` pendientes** | ❌ 5 archivos |
| **Rutas API pendientes** | ❌ 1 archivo |
| **Vistas Blade pendientes** | ❌ 9 archivos |

### Estimación de reparación completa: ~1.5 horas

El problema es **mecánico y repetitivo**: reemplazar `tbl_empleados` → `empleados` y `id_emp` → `id` en ~19 archivos. La lógica de negocio y la estructura general del proyecto están bien implementadas.

---

## 10. Módulos del Sistema (Catálogo)

### Módulo 1: Autenticación (`AdminController`)
- Login por número de empleado
- MASTER_PASSWORD para acceso de emergencia
- Logout con invalidación de sesión
- Primer inicio → forzar cambio de contraseña

### Módulo 2: Dashboard (`AdminController`)
- **Admin:** Estadísticas (total equipos, disponibles, asignados, total empleados, total móviles)
- **User/Seguridad:** Sus equipos asignados + asignaciones pendientes de aceptar

### Módulo 3: Equipos/Computadoras (`EquipoController`)
- CRUD completo
- Código interno auto-generado (`SICET-0001`, `SICET-0002`...)
- Estados: Disponible, Asignado, Pendiente, Baja
- Historial de asignaciones por equipo
- Dar de baja con motivo

### Módulo 4: Dispositivos Móviles (`DispositivoMovilController`)
- CRUD completo
- Gestión de IMEI, SIM, número de teléfono
- Estados: Disponible, Asignado, Pendiente, Baja
- Historial de asignaciones

### Módulo 5: Empleados (`EmpleadoController`)
- CRUD completo
- Vinculación con planta
- Activar/desactivar empleados
- Creación automática de usuario al crear empleado

### Módulo 6: Asignaciones de Computadoras (`AsignacionController`)
- Asignar equipo a empleado
- Generar carta responsiva PDF
- Flujo: pendiente → aceptada/rechazada
- Devolver equipo (libera el equipo)
- Historial por empleado y por equipo

### Módulo 7: Asignaciones de Móviles (`AsignacionMovilController`)
- Mismo flujo que asignaciones de computadoras
- Carta responsiva PDF específica para móviles
- Aceptar/rechazar asignación

### Módulo 8: Reportes de Seguridad (`ReporteController`)
- Creación por usuarios con rol `seguridad`
- Listado y exportación por admin
- Vinculación con número de empleado

### Módulo 9: Perfil de Usuario (`ProfileController`)
- Ver y editar perfil
- Subir/eliminar foto de perfil
- Cambiar contraseña

### Módulo 10: Recuperación de Contraseña (`PasswordResetController`)
- Solicitar enlace por número de empleado
- Envío de email con token
- Formulario de nueva contraseña
- Validación de token

---

*Informe generado el 18 de junio de 2026 para continuidad del proyecto SICET.*
