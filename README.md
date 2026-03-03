# Motor de Fusiones FTTH

Sistema completo de automatización de fusiones de fibra óptica para redes FTTH, desarrollado durante un proyecto real en una empresa de telecomunicaciones.

Construido en dos fases: primero como motor SQL en PostgreSQL, después como aplicación web en Laravel para que los operarios pudieran ejecutarlo sin tocar la base de datos.

---

## El problema

En redes de fibra óptica FTTH (**Fiber To The Home**), los cables deben fusionarse físicamente entre sí en puntos de conexión llamados **Telecom Premises (TP)**. La red tiene esta forma:

```
[CTO-001] ---Cable A--- [CTO-002] ---Cable B--- [CTO-003] ---Cable C--- [CTO-Final]
```

Cada cable lleva varios pares de fibras. Cada par del cable A debe fusionarse con el par equivalente del cable B, en el mismo orden de posición. El **último par** de cada cable va siempre al **splitter** del TP (marcado como `div1` / `div2`), que distribuye la señal hacia los usuarios finales.

Este proceso se documentaba completamente a mano. El objetivo fue automatizarlo a partir de los datos existentes en la base de datos de producción, sin modificar el modelo de datos heredado.

---

## Estructura del repositorio

```
Proyecto-real-telecomunicaciones/
│
├── sql/
│   ├── legacy/
│   │   └── 01_fusion_original.sql      ← Motor SQL original usado en producción
│   ├── improved/
│   │   ├── 01_schema.sql               ← Rediseño con modelo normalizado
│   │   └── 02_funciones.sql            ← Funciones refactorizadas (set-based)
│   └── sample_data/
│       └── datos_ejemplo.sql           ← Dataset reproducible para pruebas
│
├── web/                                ← Aplicación Laravel
│   ├── app/Http/Controllers/
│   │   └── TablaFusionController.php   ← Lógica principal portada a PHP
│   ├── database/migrations/            ← Esquema de tablas propias
│   ├── resources/views/TablaFusion/    ← Vistas Blade
│   └── routes/web.php                  ← Rutas de la aplicación
│
└── docs/
    └── decisiones_tecnicas.md          ← Contexto, reglas de negocio y comparativa
```

---

## Fase 1 — Motor SQL (`sql/`)

### El problema del modelo heredado

El sistema GIS de la empresa almacenaba las fibras de cada cable como un campo de texto:

```
FIBRAS_ACT = '1-2,3-4,5-6'
```

Cada elemento separado por `,` es un par de filamentos `A-B`. La posición en el string indica a qué TP del recorrido corresponde esa fusión. El modelo estaba en producción y **no era modificable**, por lo que se resolvió con parsing manual usando `string_to_array` y `split_part`.

### Versión original — `sql/legacy/`

Tres funciones PL/pgSQL que trabajan en cadena:

**`fusion_cable_tp(cable_origen, cable_destino, tp_origen, tp_destino)`**
Genera las fusiones entre dos cables consecutivos. Convierte los campos de texto en arrays, los recorre posición a posición y aplica la regla del splitter al último elemento.

**`fusion_tp_tp(cable_origen, tp_origen, tp_destino)`**
Caso especial para el final del recorrido. Cuando no existe cable siguiente, el último cable llega directamente al TP final y todos sus filamentos activos van al splitter.

**`recorridoCable(cable_origen, ciudad)`**
Motor de recorrido automático. Dado el primer cable de un trayecto, navega hasta el final insertando todas las fusiones en `tabla_fusion`. El campo `ciudad` evita colisiones entre proyectos de distintas ciudades que comparten códigos de origen/destino.

### Versión mejorada — `sql/improved/`

Rediseño propuesto con modelo normalizado: una fila por par de fibras, sin parsing de texto, lógica set-based con JOINs en lugar de loops.

| Aspecto | Versión original | Versión mejorada |
|---|---|---|
| Fibras | Campo texto `'1-2,3-4'` | Una fila por par (normalizado) |
| Parsing | `string_to_array` + `split_part` | `JOIN` por columna `orden` |
| Regla del splitter | `IF` dentro del loop | `CASE` en la query |
| Queries por iteración | 4 `RETURN QUERY` separadas | 1 `INSERT` con `UNION ALL` |
| Navegación de red | 3 `SELECT INTO` por iteración | 1 query con múltiples `JOIN` |
| FROM syntax | `FROM tabla1, tabla2` (implicit) | `JOIN ... ON` explícito |

---

## Fase 2 — Aplicación web (`web/`)

Interfaz Laravel que expone el motor de fusiones mediante formularios web. El operario introduce el ID del primer cable y la ciudad; el sistema genera automáticamente todas las fusiones del trayecto y las muestra en pantalla.

### Flujo de uso

```
Formulario → recorridoCableDistribucion() → fusionCableTp() × N → fusionTpTp() → Resultado
```

### Rutas

```
GET  /                          → Página de inicio
GET  /fusion/distribucion       → Formulario fusión distribución
GET  /fusion/alimentacion       → Formulario fusión alimentación
POST /fusion/distribucion       → Ejecutar recorrido de distribución
POST /fusion/alimentacion       → Ejecutar recorrido de alimentación
```

### Tablas propias

El proyecto incluye migraciones para las cuatro tablas del sistema:

- `cable` — Tramos de fibra óptica con sus pares de filamentos
- `telecom_premises` — Puntos de conexión de la red
- `tabla_fusion_laravel` — Resultado de fusiones de distribución
- `tabla_fusion_alimentacion` — Resultado de fusiones de alimentación

---

## Cómo ejecutar

### Motor SQL (versión mejorada con datos de ejemplo)

```bash
# Requisito: PostgreSQL 12+
psql -U tu_usuario -d tu_base_datos

\i sql/improved/01_schema.sql
\i sql/sample_data/datos_ejemplo.sql
\i sql/improved/02_funciones.sql

-- Ejecutar recorrido completo
SELECT recorrido_red(1, 'CIUDAD EJEMPLO');

-- Ver resultados
SELECT cod_cable_origen, cod_cable_destino,
       filamento_origen, filamento_destino,
       tipo, splitter
FROM fusion_resultado
ORDER BY cable_origen_id, tipo, filamento_origen;
```

### Aplicación Laravel

```bash
cd web
cp .env.example .env

# Configurar conexión PostgreSQL en .env:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=nombre_base_datos
# DB_USERNAME=usuario
# DB_PASSWORD=contraseña

composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

Abrir en el navegador: `http://localhost:8000`

---

## Tecnologías

- **PostgreSQL / PL/pgSQL** — Motor de base de datos y lógica procedural
- **PHP 8 / Laravel 9** — Framework web
- **Blade** — Motor de plantillas de Laravel

---

## Contexto del proyecto

La versión SQL original resolvió el problema en producción trabajando sobre un modelo heredado que no podía modificarse. La versión mejorada muestra el diseño correcto con libertad para definir el esquema desde cero. La aplicación Laravel expuso ese motor a los operarios sin que tuvieran que interactuar directamente con la base de datos.

Más detalles sobre las decisiones técnicas, reglas de negocio y comparativa entre versiones en [`docs/decisiones_tecnicas.md`](docs/decisiones_tecnicas.md).

---

## Autor

**Andrés Provira**
[GitHub](https://github.com/andresdatalyst)
