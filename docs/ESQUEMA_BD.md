# 🗄️ SICET — Esquema de Base de Datos y Cambios de Estabilización

**Fecha:** 22 de junio de 2026
**Fase:** 1 — Estabilización del backend (completada y verificada en vivo)
**Diagrama visual:** ver `sicet_esquema_bd.excalidraw` (abrir en https://excalidraw.com)

---

## PARTE 1 — Qué cambios se hicieron y por qué

### El problema raíz

La tabla de empleados se **migró a medias**: pasó de `tbl_empleados`/`id_emp` a `empleados`/`id`,
y además se **renombraron columnas**. El código (modelos, controladores, vistas) siguió apuntando
al esquema viejo, mezclando "tres idiomas" y referenciando columnas inexistentes.

#### Mapa del cambio (viejo → real)

| Concepto | Código viejo (roto) | Columna/clave real |
|---|---|---|
| Tabla | `tbl_empleados` | `empleados` |
| Llave primaria | `id_emp` | `id` |
| Nombre | `nombre` + `apellidos` (2 campos) | `nombre_completo` (1 campo) |
| Correo | `email` | `correo` |
| Activo | `activo = 'S'` / `'N'` (texto) | `activo` = `1` / `0` (booleano) |
| Planta | `id_planta` | `planta_id` |
| Nº empleado | (no se guardaba) | `numero_empleado` (único, obligatorio) |

**Consecuencias:** relaciones Eloquent devolvían `null` en silencio, búsquedas usaban columnas
inexistentes y **crear un empleado fallaba**.

### Cambios por archivo

#### Modelos (4) — relaciones rotas
- `app/Models/User.php`, `Asignacion.php`, `AsignacionMovil.php` → relación con empleado: `id_emp` → `id`.
- `app/Models/Reporte.php` → caso especial: enlaza `numero_empleado` ↔ `numero_empleado` (texto), **no** `id`.

#### Controladores (5)
- **`EmpleadoController`** (reescrito): `store()` validaba `apellidos` (no enviado) y no guardaba
  `numero_empleado`/`planta_id` (obligatorios). Ahora usa columnas reales. **Decisión:** creación en
  **un paso** (empleado + usuario opcional si se asigna rol). `edit/update/destroy/toggle`: `id_emp`→`id`,
  `email`→`correo`.
- **`AsignacionController`** / **`AsignacionMovilController`**: `exists:tbl_empleados,id_emp`→`exists:empleados,id`;
  `CONCAT(nombre,apellidos)`→`nombre_completo`; `activo='S'`→`activo=1`; `$empleado->email`→`correo`.
- **`ReporteController`**: carga de empleados con columnas reales; validación `exists:empleados,numero_empleado`;
  export `nombre.apellidos`→`nombre_completo`.
- **`PasswordResetController`**: `DB::table('tbl_empleados')`→`empleados`, `activo='S'`→`1`, lee `correo`.

#### API (1) — `routes/web.php`
- `/api/empleados/search` reescrita al esquema nuevo (`nombre_completo`/`correo`/`numero_empleado`).

#### Vistas Blade (11)
- `empleados/create` y `empleados/edit`: se quitó el campo **"Apellidos"**, `nombre`→`nombre_completo`,
  `email`→`correo`, `id_planta`→`planta_id`, rol `usuario`(inválido)→`user`, **planta obligatoria**.
- `empleados/index`, `asignaciones/index`+`dashboard`, `asignaciones_moviles/create`+`dashboard`,
  `moviles/asignar`, `reportes/create`+`index`, `emails/password-reset`, `pdf/carta_asignacion_movil`:
  `id_emp`→`id` (o `numero_empleado` donde aplica), `email`→`correo`, `nombre apellidos`→`nombre_completo`.

### Verificación realizada
- Lint PHP: 10/10 sin errores · Blades: compilan todas · App arranca (`route:list`).
- Grep de regresión: **0** referencias viejas en código vivo.
- En vivo contra la BD: relaciones `Empleado↔User` resuelven, queries de API/controladores OK,
  flujo completo de creación de empleado validado (en transacción revertida).

---

## PARTE 2 — Esquema de la base de datos (final)

### Diagrama de relaciones (ASCII)

```
                    ┌──────────────┐
                    │   plantas    │
                    └──────┬───────┘
                     1 ────┴──── 1
              ┌────────────┐   ┌────────────┐
              │ empleados  │   │  equipos   │
              └──┬──────┬──┘   └─────┬──────┘
          1 ─────┘      └──── N      │ N
     ┌────────┐    ┌──────────────┐  │   ┌──────────────────────┐
     │ users  │◄1:1┤ asignaciones │◄─┘   │ dispositivos_moviles │
     └───┬────┘    │ (equipo+emp  │      └──────────┬───────────┘
       1 │ N       │  +user)      │               N │
     ┌───▼────┐    └──────────────┘      ┌──────────▼─────────────┐
     │reportes│                          │  asignaciones_moviles  │
     └────────┘                          │ (movil+emp+user)       │
   reportes.numero_empleado ┄┄►          └────────────────────────┘
   empleados.numero_empleado
```

### Tablas detalladas (tipos exactos)

#### `plantas`
| Columna | Tipo | Nulo | Llave |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| nombre | varchar(255) | NO | UNIQUE |
| created_at / updated_at | timestamp | SÍ | |

#### `empleados` ⭐
| Columna | Tipo | Nulo | Llave | Nota |
|---|---|---|---|---|
| id | bigint unsigned | NO | PK | |
| numero_empleado | varchar(255) | NO | UNIQUE | nº interno |
| nombre_completo | varchar(255) | NO | | (antes nombre+apellidos) |
| correo | varchar(255) | SÍ | | (antes email) |
| planta_id | bigint unsigned | **NO** | FK→plantas | obligatorio |
| activo | tinyint(1) | NO | | def 1 (antes 'S'/'N') |
| created_at / updated_at | timestamp | SÍ | | |

#### `users`
| Columna | Tipo | Nulo | Llave | Nota |
|---|---|---|---|---|
| id | bigint unsigned | NO | PK | |
| empleado_id | bigint unsigned | SÍ | FK→empleados | |
| name | varchar(255) | NO | | |
| email | varchar(255) | NO | UNIQUE | |
| numero_empleado | varchar(255) | SÍ | UNIQUE | duplicado (deuda) |
| profile_photo | varchar(255) | SÍ | | |
| email_verified_at | timestamp | SÍ | | |
| password | varchar(255) | NO | | hash |
| password_original | varchar(255) | SÍ | | ⚠️ texto plano (a quitar) |
| remember_token | varchar(100) | SÍ | | |
| role | varchar(255) | NO | | def `user` |
| area | varchar(255) | SÍ | | |
| primer_inicio | tinyint | NO | | def 1 |
| created_at / updated_at | timestamp | SÍ | | |

#### `equipos`
| Columna | Tipo | Nulo | Llave |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| nombre_equipo | varchar(255) | SÍ | |
| codigo_interno | varchar(255) | NO | UNIQUE |
| marca / modelo | varchar(255) | NO | |
| numero_serie | varchar(255) | NO | UNIQUE |
| direccion_mac | varchar(100) | SÍ | |
| color / procesador / ram / ssd | varchar(255) | SÍ | |
| tipo_almacenamiento / capacidad_almacenamiento | varchar(255) | SÍ | |
| cargador | tinyint(1) | NO | def 1 |
| fecha_adquisicion | date | SÍ | |
| planta_id | bigint unsigned | NO | FK→plantas |
| observaciones | text | SÍ | |
| estado | enum('Disponible','Asignado','Pendiente','En reparación','Baja') | SÍ | def Disponible |
| fecha_baja | timestamp | SÍ | |
| motivo_baja | text | SÍ | |
| created_at / updated_at | timestamp | SÍ | |

#### `dispositivos_moviles`
| Columna | Tipo | Nulo | Llave |
|---|---|---|---|
| id | bigint unsigned | NO | PK |
| codigo_interno | varchar(255) | SÍ | UNIQUE |
| marca / modelo | varchar(255) | NO | |
| imei | varchar(255) | NO | UNIQUE |
| numero_sim / numero_telefono | varchar(255) | SÍ | |
| caracteristicas | text | SÍ | |
| estado | enum('Disponible','Asignado','Pendiente','En reparación','Baja') | SÍ | def Disponible |
| asignado | tinyint(1) | NO | def 0 |
| fecha_baja | timestamp | SÍ | |
| motivo_baja | text | SÍ | |
| created_at / updated_at | timestamp | SÍ | |

#### `asignaciones` (equipo ↔ empleado)
| Columna | Tipo | Nulo | Llave | Nota |
|---|---|---|---|---|
| id | bigint unsigned | NO | PK | |
| equipo_id | bigint unsigned | NO | FK→equipos | |
| empleado_id | bigint unsigned | NO | FK→empleados | |
| user_id | bigint unsigned | NO | FK→users | quién asignó |
| fecha_asignacion | date | NO | | |
| fecha_devolucion | date | SÍ | | |
| estado_asignacion | enum('pendiente','aceptada','rechazada') | NO | | ✅ en uso |
| fecha_respuesta | timestamp | SÍ | | |
| carta_pdf | varchar(255) | SÍ | | ruta PDF |
| estado | enum('pendiente','activo','rechazado') | NO | | ⚠️ DEPRECADO |
| token_confirmacion | varchar(255) | SÍ | | ⚠️ DEPRECADO |
| created_at / updated_at | timestamp | SÍ | | |

#### `asignaciones_moviles` (móvil ↔ empleado)
Igual que `asignaciones` pero con `dispositivo_movil_id` (FK→dispositivos_moviles) en vez de `equipo_id`,
y fechas como `timestamp`. También arrastra las columnas deprecadas `estado` y `token_confirmacion`.

#### `reportes`
| Columna | Tipo | Nulo | Llave | Nota |
|---|---|---|---|---|
| id | bigint unsigned | NO | PK | |
| matricula | varchar(255) | NO | | código del equipo |
| area | varchar(255) | NO | | |
| inconsistencias | text | SÍ | | |
| tipo | enum('entrada','salida') | NO | | |
| user_id | bigint unsigned | NO | FK→users | |
| numero_empleado | varchar(255) | SÍ | | ↔ empleados.numero_empleado |
| created_at / updated_at | timestamp | SÍ | | |

---

## Deuda técnica diferida (fases posteriores)

1. **Columnas duplicadas** en `asignaciones`/`asignaciones_moviles`: `estado` y `token_confirmacion` (legado muerto).
2. **`numero_empleado` en dos tablas** (`empleados` y `users`) → riesgo de desincronización.
3. **`password_original`** en texto plano → eliminar (fase de seguridad).
4. **No se modificó el esquema** en esta fase, solo el código que lo usaba.
