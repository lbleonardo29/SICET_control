# Informe Técnico — SICET
**Sistema Integral de Control y Entrega de Tecnologías**
**Versión:** 2.0 — Backend completo y estabilizado
**Fecha:** 23 de junio de 2026
**Repositorio:** https://github.com/lbleonardo29/SICET_control.git

---

## 1. ¿Qué es SICET?

SICET es un sistema web interno para gestionar la entrega y control de equipos de cómputo y dispositivos móviles a empleados de una organización con múltiples plantas (Jardín, Partidas, Sauces).

**Qué hace:**
- Registra computadoras y dispositivos móviles con su información técnica completa
- Asigna equipos a empleados mediante un flujo de aprobación (el empleado acepta/rechaza desde el sistema)
- Genera cartas responsivas en PDF como evidencia de entrega
- Sincroniza el directorio de empleados con la base de datos corporativa `tickets`
- Maneja reportes de inconsistencias de activos por el personal de seguridad
- Notifica por correo electrónico en cada paso del flujo

---

## 2. Stack Tecnológico

### Backend
| Tecnología | Versión | Uso |
|---|---|---|
| PHP | 8.3.x | Lenguaje principal |
| Laravel | 9.x | Framework MVC |
| MySQL | (Laragon local) | Base de datos principal (`sicet`) |
| Laravel DomPDF | 2.2.x | Generación de cartas responsivas en PDF |
| Doctrine DBAL | 3.x | Modificación de columnas en migraciones |
| Laravel Sanctum | 3.x | Tokens de API |
| Guzzle | 7.x | Cliente HTTP |

### Frontend
| Tecnología | Versión | Uso |
|---|---|---|
| Blade | Laravel 9 | Motor de plantillas |
| Bootstrap | 5.3.0 (CDN) | Framework CSS |
| Bootstrap Icons | 1.11.3 (CDN) | Iconografía |
| Vite | 4.x | Bundler de assets |
| Axios | 1.1.2 | Peticiones asíncronas |

### Entorno de desarrollo
| Herramienta | Uso |
|---|---|
| Laragon | Servidor local (Apache + MySQL 8 + PHP 8.3) |
| HeidiSQL | GUI para administrar la base de datos |
| Composer | Dependencias PHP |
| npm | Dependencias JavaScript |
| Git + GitHub | Control de versiones |

---

## 3. Arquitectura MVC

```
HTTP Request
     │
     ▼
 routes/web.php ──► Middleware (auth, role)
     │
     ▼
 Controller ──► Eloquent Model ──► MySQL (sicet)
     │                │
     │                └──► Corp\Model ──► MySQL (tickets)  [si flag activo]
     │
     ▼
 Blade View / JSON / PDF / Mail
```

### 3.1 Modelos Eloquent

| Modelo | Tabla | Descripción |
|---|---|---|
| `Empleado` | `empleados` | Directorio local de empleados (espejo de `tickets`) |
| `User` | `users` | Usuarios con acceso al sistema (credenciales, rol) |
| `Planta` | `plantas` | Ubicaciones físicas de la organización |
| `Equipo` | `equipos` | Computadoras y laptops |
| `DispositivoMovil` | `dispositivos_moviles` | Celulares y tablets |
| `Asignacion` | `asignaciones` | Asignaciones de computadoras a empleados |
| `AsignacionMovil` | `asignaciones_moviles` | Asignaciones de dispositivos móviles |
| `Reporte` | `reportes` | Reportes de inconsistencias de seguridad |
| `Corp\EmpleadoTicket` | `tbl_empleados` (BD `tickets`) | Modelo de lectura/escritura corporativo |
| `Corp\PlantaTicket` | `planta` (BD `tickets`) | Plantas en el sistema corporativo |

### 3.2 Controladores

| Controlador | Responsabilidad |
|---|---|
| `AdminController` | Login, logout, dashboard (admin y usuario) |
| `EmpleadoController` | CRUD de empleados + write-through a BD corporativa |
| `EquipoController` | CRUD de computadoras, historial, baja |
| `DispositivoMovilController` | CRUD de dispositivos móviles, historial, baja |
| `AsignacionController` | Flujo completo: asignar, PDF, aceptar/rechazar, devolver |
| `AsignacionMovilController` | Mismo flujo para móviles |
| `ReporteController` | Crear, listar y exportar reportes de seguridad |
| `ProfileController` | Perfil de usuario: foto, datos, cambio de contraseña |
| `PasswordResetController` | Recuperación de contraseña por correo electrónico |

### 3.3 Middleware

| Middleware | Función |
|---|---|
| `Authenticate` | Verifica que el usuario esté autenticado; redirige a `/login` si no |
| `RoleMiddleware` | Verifica que el usuario tenga el rol necesario (`admin`, `user`, `seguridad`) |

### 3.4 Servicios y soporte

| Clase | Ubicación | Función |
|---|---|---|
| `TicketsEmpleadoService` | `app/Services/` | Write-through local→corporativo (best-effort) |
| `EmpleadoMapper` | `app/Support/` | Traducción de columnas entre esquemas SICET ↔ `tickets` |
| `SyncEmpleados` | `app/Console/Commands/` | Comando Artisan de sincronización masiva desde `tickets` |

---

## 4. Base de Datos

### 4.1 Mapa de relaciones

```
                      ┌──────────────────────┐
                      │        plantas        │
                      │  id (PK)              │
                      │  nombre               │
                      │  id_planta_corp ──────┼──► planta.id_planta (tickets)
                      └──────┬───────────────┘
                             │ 1
                    ┌────────┴────────────┐
                    │                     │
                    │ N                   │ N
           ┌────────┴────────┐    ┌───────┴──────────┐
           │    empleados    │    │      equipos      │
           │  id (PK)        │    │  id (PK)          │
           │  numero_empleado│    │  codigo_interno   │
           │  nombre_completo│    │  marca / modelo   │
           │  correo         │    │  planta_id (FK)   │
           │  planta_id (FK) │    │  estado           │
           │  activo         │    └───────┬──────────┘
           └────┬────────┬───┘           │ 1
                │ 1      │ 1             │
         ┌──────┘        └──────┐      N │
         │ N                    │ N  ┌───┴──────────────┐
    ┌────┴────┐          ┌──────┴──┐ │   asignaciones   │
    │  users  │          │asigna-  │ │  equipo_id (FK)  │
    │ id (PK) │          │ciones_  │ │  empleado_id(FK) │
    │empleado_│◄─────────│moviles  │ │  user_id (FK)    │
    │  id(FK) │          │movil_id │ │  estado_asignac. │
    │ email   │          │empleado │ │  carta_pdf       │
    │ role    │          │user_id  │ └──────────────────┘
    │primer_  │          │estado_  │
    │inicio   │          │asignac. │      ┌────────────────┐
    └────┬────┘          │carta_pdf│      │   reportes     │
         │ 1             └─────────┘      │  user_id (FK)  │
         │ N                              │numero_empleado │
    ┌────┴──────┐                         │ (→empleados)   │
    │ reportes  │                         └────────────────┘
    └───────────┘
```

### 4.2 Tablas detalladas

#### `plantas`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK, auto_increment |
| nombre | varchar(255) | NO | UNIQUE |
| id_planta_corp | int unsigned | SÍ | INDEX — puente con `tickets` |
| created_at / updated_at | timestamp | SÍ | |

---

#### `empleados` ★ tabla central
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK, auto_increment |
| numero_empleado | varchar(255) | NO | UNIQUE — pivote con BD corporativa |
| nombre_completo | varchar(255) | NO | |
| correo | varchar(255) | SÍ | |
| planta_id | bigint unsigned | NO | FK → plantas.id |
| activo | tinyint(1) | NO | default 1 |
| created_at / updated_at | timestamp | SÍ | |

> `numero_empleado` equivale a `id_emp` en la BD corporativa `tickets`.
> Es el único puente seguro entre los dos sistemas.

---

#### `users`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK, auto_increment |
| empleado_id | bigint unsigned | SÍ | FK → empleados.id |
| numero_empleado | varchar(255) | SÍ | UNIQUE — permite login por nº empleado |
| name | varchar(255) | NO | |
| email | varchar(255) | NO | UNIQUE |
| password | varchar(255) | NO | bcrypt |
| role | varchar(255) | NO | admin / user / seguridad |
| primer_inicio | tinyint | NO | default 1 → fuerza cambio de contraseña |
| profile_photo | varchar(255) | SÍ | |
| area | varchar(255) | SÍ | |
| email_verified_at | timestamp | SÍ | |
| remember_token | varchar(100) | SÍ | |
| created_at / updated_at | timestamp | SÍ | |

---

#### `equipos`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| codigo_interno | varchar(255) | NO | UNIQUE (SICET-0001…) |
| marca / modelo | varchar(255) | NO | |
| numero_serie | varchar(255) | NO | UNIQUE |
| direccion_mac | varchar(100) | SÍ | |
| color / procesador / ram / ssd | varchar(255) | SÍ | |
| tipo_almacenamiento / capacidad_almacenamiento | varchar(255) | SÍ | |
| cargador | tinyint(1) | NO | default 1 |
| fecha_adquisicion | date | SÍ | |
| planta_id | bigint unsigned | NO | FK → plantas.id |
| observaciones | text | SÍ | |
| estado | enum | SÍ | Disponible / Asignado / Pendiente / En reparación / Baja |
| motivo_baja / fecha_baja | text / timestamp | SÍ | |
| created_at / updated_at | timestamp | SÍ | |

---

#### `dispositivos_moviles`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| codigo_interno | varchar(255) | SÍ | UNIQUE |
| marca / modelo | varchar(255) | NO | |
| imei | varchar(255) | NO | UNIQUE |
| numero_sim / numero_telefono | varchar(255) | SÍ | |
| caracteristicas | text | SÍ | |
| estado | enum | SÍ | Disponible / Asignado / Pendiente / En reparación / Baja |
| asignado | tinyint(1) | NO | default 0 |
| motivo_baja / fecha_baja | text / timestamp | SÍ | |
| created_at / updated_at | timestamp | SÍ | |

---

#### `asignaciones`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| equipo_id | bigint unsigned | NO | FK → equipos.id |
| empleado_id | bigint unsigned | NO | FK → empleados.id |
| user_id | bigint unsigned | SÍ | FK → users.id |
| fecha_asignacion | date | NO | |
| fecha_devolucion | date | SÍ | NULL = activa |
| estado_asignacion | enum | NO | pendiente / aceptada / rechazada |
| fecha_respuesta | timestamp | SÍ | Cuándo aceptó o rechazó |
| carta_pdf | varchar(255) | SÍ | Ruta relativa al storage |
| created_at / updated_at | timestamp | SÍ | |

---

#### `asignaciones_moviles`
Idéntica a `asignaciones` pero con `dispositivo_movil_id` (FK → dispositivos_moviles.id) en lugar de `equipo_id`.

---

#### `reportes`
| Columna | Tipo | Nulo | Extra |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| matricula | varchar(255) | NO | Código del equipo reportado |
| area | varchar(255) | NO | |
| inconsistencias | text | SÍ | Descripción del problema |
| tipo | enum | NO | entrada / salida |
| user_id | bigint unsigned | NO | FK → users.id |
| numero_empleado | varchar(255) | SÍ | Referencia textual a empleados.numero_empleado |
| created_at / updated_at | timestamp | SÍ | |

---

#### `password_reset_tokens`
| Columna | Tipo | Extra |
|---|---|---|
| email | varchar(255) | PK |
| token | varchar(255) | |
| created_at | timestamp | |

---

### 4.3 Dos bases de datos: `sicet` y `tickets`

SICET utiliza **dos conexiones MySQL**:

```
┌─────────────────────────────┐     ┌──────────────────────────────┐
│   BD LOCAL: sicet           │     │   BD CORPORATIVA: tickets    │
│   (siempre disponible)      │     │   (opcional, flag controlado)│
│                             │     │                              │
│  empleados ──────────────── │ ◄── │  tbl_empleados               │
│  plantas (id_planta_corp) ──│ ◄── │  planta                      │
│  asignaciones               │     │                              │
│  users (login propio)       │     │  (resto de tablas no se      │
│  equipos, moviles, etc.     │     │   tocan desde SICET)         │
└─────────────────────────────┘     └──────────────────────────────┘
         App\Models\*                    App\Models\Corp\*
         connection: mysql               connection: tickets
```

**Variables `.env` para la segunda conexión:**
```
TICKETS_DB_HOST=127.0.0.1
TICKETS_DB_PORT=3306
TICKETS_DB_DATABASE=tickets
TICKETS_DB_USERNAME=root
TICKETS_DB_PASSWORD=
TICKETS_DB_TIMEOUT=5

TICKETS_WRITE_THROUGH=false   # true = SICET escribe de vuelta al corp
TICKETS_SYNC_ENABLED=false    # true = sync horario activo
```

---

## 5. Integración con Base de Datos Corporativa (`tickets`)

### 5.1 Estrategia: espejo local + write-through

```
                    BD corporativa (tickets)
                    tbl_empleados / planta
                           │
                    [sync horario]
                           │
                           ▼
                    BD local (sicet)
                    empleados / plantas
                           │
                   ┌───────┴──────────┐
                   │                  │
              [SICET lee]        [write-through]
              siempre local       al guardar/editar
              sin red             best-effort
```

**Principio local-first:** SICET guarda primero en su BD local. Luego intenta escribir al corporativo. Si `tickets` está caído, la operación local ya se guardó y el sistema avisa al usuario. La próxima sincronización horaria reconcilia.

### 5.2 Pivote entre sistemas

```
empleados.numero_empleado  ==  tbl_empleados.id_emp
plantas.id_planta_corp     ==  planta.id_planta
```

No existen Foreign Keys entre bases de datos. Todo el vínculo es lógico.

### 5.3 Flujo de sincronización (`sicet:sync-empleados`)

```
php artisan sicet:sync-empleados [--dry-run] [--solo-plantas] [--solo-empleados]
```

```
1. Lee planta[] de tickets
   └─► upsert en plantas por id_planta_corp (o nombre normalizado)
   └─► construye mapa: id_planta_corp → planta_id local

2. Lee tbl_empleados[] de tickets (chunks de 500)
   └─► por cada empleado:
       ├─ updateOrCreate(numero_empleado) en empleados local
       │   → PRESERVA el id local (no rompe FK de asignaciones/users)
       └─ traduce columnas: nombre+apellidos → nombre_completo
                            'S'/'N' → 1/0

3. Empleados que ya no están en tickets → activo=0 (nunca DELETE)

Scheduler: cada hora, withoutOverlapping()
```

### 5.4 EmpleadoMapper (traducción de columnas)

```
BD corporativa (tickets)          BD local (sicet)
─────────────────────────         ────────────────────────
id_emp (INT)              ◄──►   numero_empleado (VARCHAR)
nombre (VARCHAR)          ──┐
apellidos (VARCHAR)       ──┴►   nombre_completo (VARCHAR)
email (VARCHAR)           ◄──►   correo (VARCHAR)
activo ('S' / 'N')        ◄──►   activo (1 / 0)
id_planta (INT)           ◄──►   planta_id via id_planta_corp
```

### 5.5 Write-through en EmpleadoController

```
Admin guarda empleado
       │
       ├─► Eloquent::save() en BD local  ← SIEMPRE ocurre
       │
       └─► TicketsEmpleadoService::pushUpsert()
               │
               ├─ [flag OFF]  → no hace nada, retorna ''
               ├─ [flag ON, corp UP]  → actualiza tbl_empleados
               └─ [flag ON, corp DOWN] → Log::error + retorna aviso al usuario
                                         (la op. local ya se guardó)
```

---

## 6. Sistema de Autenticación

### 6.1 Login

El formulario acepta **número de empleado** o **correo electrónico** en el mismo campo:

```
Input del usuario
       │
       ├─ is_numeric() → busca por users.empleado_id
       └─ es texto    → busca por users.email

Auth::attempt($credentials)
       │
       ├─ primer_inicio = 1 → redirect a /cambiar-password (forzar cambio)
       └─ primer_inicio = 0 → redirect a /dashboard
```

### 6.2 Roles

| Rol | Acceso |
|---|---|
| `admin` | Todo: CRUD de equipos, empleados, móviles, asignaciones, reportes, usuarios |
| `user` | Dashboard personal, aceptar/rechazar asignaciones, ver perfil |
| `seguridad` | Todo lo de `user` + crear reportes de inconsistencias |

### 6.3 Recuperación de contraseña

```
Usuario ingresa su correo electrónico
       │
       ▼
PasswordResetController::sendResetLink()
       │
       ├─ Busca User::where('email', $correo)
       ├─ Si no existe → mismo mensaje genérico (no revela si el correo existe)
       ├─ Genera token Str::random(60)
       ├─ Guarda en password_reset_tokens (válido 24 h)
       └─ Envía correo con enlace /reset-password/{token}
              │
              ▼
       Usuario hace clic en el enlace
              │
              ▼
       showResetForm($token) → valida token y expiración
              │
              ▼
       resetPassword() → Hash::make($nueva_contraseña) → guarda → delete token
```

---

## 7. Sistema de Correo Electrónico

### 7.1 Configuración SMTP

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=microsoftoffice365ofertas@gmail.com
MAIL_PASSWORD=[contraseña de aplicación de Google]
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=microsoftoffice365ofertas@gmail.com
MAIL_FROM_NAME="SICET"
```

### 7.2 Correos que envía el sistema

| Evento | Destinatario | Clase Mail | Plantilla |
|---|---|---|---|
| Admin crea empleado con rol | `$empleado->correo` | `CredencialesUsuario` | `emails/credenciales_usuario` |
| Admin edita empleado (crea usuario nuevo) | `$empleado->correo` | `CredencialesUsuario` | `emails/credenciales_usuario` |
| Se crea asignación de computadora | `$empleado->correo` | `AsignacionPendiente` | — |
| Se crea asignación de móvil | `$empleado->correo` | `AsignacionPendiente` | — |
| Recuperación de contraseña | correo que ingresó el usuario | Mail::send directo | `emails/password-reset` |
| Carta responsiva por correo | — | `CartaAsignacionMail` | `emails/carta_asignacion` (+ adjunto PDF) |

### 7.3 Flujo de correo de credenciales

```
EmpleadoController::store()
       │
       ├─ Crea Empleado en BD
       ├─ Si tiene rol → crea User con password temporal (8 chars random)
       └─ Mail::to($empleado->correo)
              └─ CredencialesUsuario($empleado, $password)
                     └─► Blade: nombre, correo, contraseña temporal
                         Nota: primer_inicio=1 fuerza cambio al entrar
```

### 7.4 CartaAsignacionMail — seguridad de adjuntos

El PDF se genera con nombre predecible (`carta_equipo_{id}.pdf`, `carta_movil_{id}.pdf`) y se guarda en `storage/app/public/cartas/`. Al adjuntarlo al correo se valida que la ruta empiece con `cartas/` para prevenir que un valor manipulado en BD pueda adjuntar archivos del servidor:

```php
if (!str_starts_with($path, 'cartas/')) {
    throw new \InvalidArgumentException('Ruta de carta PDF inválida.');
}
```

---

## 8. Flujo Completo de una Asignación

```
ADMIN                          SISTEMA                         EMPLEADO
  │                               │                               │
  ├─ Entra a /equipos/disponibles │                               │
  ├─ Selecciona equipo ──────────►│                               │
  ├─ Selecciona empleado          │                               │
  ├─ Confirma asignación ────────►│                               │
  │                               ├─ Crea Asignacion              │
  │                               │   estado = 'pendiente'        │
  │                               ├─ Cambia Equipo.estado         │
  │                               │   = 'Pendiente'               │
  │                               ├─ Genera PDF (DomPDF)          │
  │                               │   → storage/cartas/           │
  │                               ├─ Guarda ruta en carta_pdf     │
  │                               └─ Mail::to($empleado->correo)  │
  │                                         └──────────────────► │
  │                               │                               ├─ Recibe notificación
  │                               │                               ├─ Entra al sistema
  │                               │                               ├─ Ve asignación pendiente
  │                               │                               │
  │                               │          [ACEPTA] ────────── ►│
  │                               ├─ estado = 'aceptada'          │
  │                               ├─ Equipo.estado = 'Asignado'   │
  │                               └─ fecha_respuesta = now()      │
  │                                                               │
  │                               │          [RECHAZA] ──────────►│
  │                               ├─ estado = 'rechazada'         │
  │                               ├─ Equipo.estado = 'Disponible' │
  │                               └─ fecha_devolucion = now()     │
  │                               │                               │
  ├─ Admin registra devolución ──►│                               │
  │                               ├─ fecha_devolucion = now()     │
  │                               └─ Equipo.estado = 'Disponible' │
```

---

## 9. Rutas del Sistema

```
PÚBLICAS
  GET  /login                       → Formulario de login
  POST /login                       → Autenticar
  POST /logout                      → Cerrar sesión
  GET  /forgot-password             → Formulario recuperación (pide correo)
  POST /forgot-password             → Enviar enlace de reset
  GET  /reset-password/{token}      → Formulario nueva contraseña
  POST /reset-password              → Guardar nueva contraseña

AUTENTICADO (cualquier rol)
  GET  /dashboard                   → Panel principal
  GET/PUT /perfil                   → Ver y editar perfil
  GET/POST /cambiar-password        → Cambio obligatorio de contraseña (primer_inicio)
  GET  /asignaciones                → Lista de asignaciones
  GET  /asignaciones/dashboard      → Dashboard asignaciones computadoras
  GET  /asignaciones-moviles        → Dashboard asignaciones móviles
  PUT  /asignaciones/aceptar/{id}   → Aceptar asignación de computadora
  PUT  /asignaciones/rechazar/{id}  → Rechazar asignación de computadora
  PUT  /asignaciones-moviles/aceptar/{id}  → Aceptar asignación de móvil
  PUT  /asignaciones-moviles/rechazar/{id} → Rechazar asignación de móvil
  GET  /empleados                   → Listar empleados

SOLO ADMIN
  CRUD /equipos                     → Computadoras (crear, editar, baja, historial)
  CRUD /empleados                   → Empleados (crear, editar, activar/desactivar)
  CRUD /moviles                     → Dispositivos móviles
  POST /asignaciones                → Crear asignación computadora
  POST /asignaciones-moviles        → Crear asignación móvil
  GET  /asignaciones/devolver/{id}  → Registrar devolución
  GET  /asignaciones/descargar/{id} → Descargar carta PDF
  GET  /reportes                    → Ver todos los reportes
  GET  /reportes/exportar           → Exportar reportes

SOLO SEGURIDAD
  GET  /reportes/create             → Crear reporte de inconsistencia
  POST /reportes                    → Guardar reporte

API INTERNA (web.php)
  GET  /api/empleados/search        → Autocompletado de empleados (retorna JSON)
  GET  /api/empleado/{id}/computadoras → Computadoras del empleado (JSON)
```

---

## 10. Generación de PDFs (Cartas Responsivas)

```
AsignacionController::store()
       │
       ├─ Crea directorio storage/app/public/cartas/ si no existe
       ├─ Pdf::loadView('pdf.carta_asignacion', [equipo, empleado, asignacion])
       ├─ Genera nombre: carta_equipo_{id}.pdf
       ├─ Storage::disk('public')->put('cartas/carta_equipo_{id}.pdf', $pdf->output())
       └─ asignacion->update(['carta_pdf' => 'cartas/carta_equipo_{id}.pdf'])

Descarga:  GET /asignaciones/descargar/{id}
           → Storage::disk('public')->download($asignacion->carta_pdf)

Vista PDF: GET /asignaciones/carta/{equipo_id}
           → $pdf->stream(...)  [abre en el navegador]
```

Los PDFs se guardan en `storage/app/public/cartas/` y son accesibles públicamente a través del symlink `public/storage/`.

---

## 11. Seguridad Implementada

| Medida | Descripción |
|---|---|
| Autenticación por sesión | Laravel Auth + `middleware('auth')` en todas las rutas privadas |
| Control de roles | `RoleMiddleware` verifica el rol antes de ejecutar el controlador |
| Hash de contraseñas | `Hash::make()` (bcrypt) en todas las contraseñas |
| Tokens de reset | `Str::random(60)` + expiración de 24 horas |
| Mensajes genéricos en reset | No revela si un correo está registrado o no |
| Validación de rutas PDF | `CartaAsignacionMail` valida que el path empiece con `cartas/` |
| `.env` fuera de git | `.gitignore` incluye `.env` y archivos de sesión/caché |
| Sesión regenerada en login | `$request->session()->regenerate()` |
| CSRF | `@csrf` en todos los formularios |

---

## 12. Estado Actual del Proyecto

### Backend — completado

| Módulo | Estado |
|---|---|
| Schema drift estabilizado (tbl_empleados→empleados) | ✅ |
| Modelos, controladores y vistas alineados con el esquema real | ✅ |
| Correos enviados al destinatario correcto (no hardcodeados) | ✅ |
| Recuperación de contraseña por correo electrónico | ✅ |
| Integración con BD corporativa `tickets` (detrás de flags) | ✅ |
| Sync horario con Artisan + Scheduler | ✅ |
| Write-through local-first | ✅ |
| MASTER_PASSWORD hasheada (solo entorno local) | ✅ |
| Validación de path traversal en adjunto PDF | ✅ |
| config/auth.php → tabla correcta | ✅ |
| Columnas obsoletas eliminadas (password_original, estado viejo, token_confirmacion) | ✅ (migración lista) |
| .gitignore: sessions, cache, logs | ✅ |

### Pendiente — fase frontend

- Rediseño de UI/UX
- Responsive completo para móviles
- Mejoras de usabilidad en flujos de asignación

---

## 13. Cómo Levantar el Proyecto

```bash
# 1. Clonar
git clone https://github.com/lbleonardo29/SICET_control.git
cd SICET_control

# 2. Instalar dependencias PHP
composer install --ignore-platform-req=ext-oci8

# 3. Instalar dependencias JS
npm install

# 4. Configurar entorno
cp .env.example .env
# Editar .env: APP_KEY, DB_*, MAIL_*

# 5. Generar clave de app
php artisan key:generate

# 6. Ejecutar migraciones
php artisan migrate

# 7. Compilar assets
npm run dev   # desarrollo
npm run build # producción

# 8. Levantar con Laragon (Apache + MySQL)
#    o: php artisan serve

# 9. (Opcional) Activar integración corporativa
#    Importar dump tickets en MySQL, luego en .env:
#    TICKETS_SYNC_ENABLED=true
#    TICKETS_WRITE_THROUGH=true
#    php artisan sicet:sync-empleados --dry-run
```

---

*Informe actualizado el 23 de junio de 2026 — SICET v2.0, backend estabilizado e integrado.*
