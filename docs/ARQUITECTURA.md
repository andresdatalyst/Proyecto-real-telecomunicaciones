# Arquitectura Técnica — Motor de Fusiones FTTH

---

## Visión General

```
┌─────────────────────────────────────────────────────────────────┐
│                      CAPAS DE ARQUITECTURA                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Presentación     →  Blade Templates (HTML/CSS)                │
│                      ├─ index.blade.php                        │
│                      ├─ formFusionDistribucion.blade.php       │
│                      └─ result.blade.php                       │
│                                                                 │
│  Lógica           →  TablaFusionController.php                 │
│                      ├─ recorridoCableDistribucion()           │
│                      ├─ fusionCableTp()                        │
│                      └─ fusionTpTp()                           │
│                                                                 │
│  Acceso a datos   →  Eloquent / Query Builder                 │
│                      └─ DB::table('cable'), etc.              │
│                                                                 │
│  Persistencia     →  Base de datos (MySQL / PostgreSQL)       │
│                      ├─ cable                                  │
│                      ├─ telecom_premises                       │
│                      ├─ tabla_fusion_laravel                   │
│                      └─ tabla_fusion_alimentacion              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 1️⃣ Capa de Presentación

### Flujo de navegación

```
Inicio
  │
  ├─→ GET /                    →  index.blade.php
  │       (Selector de tipo)
  │
  ├─→ GET /fusion/distribucion →  formFusionDistribucion.blade.php
  │       (Formulario entrada)
  │    POST /fusion/distribucion → Procesa
  │                                │
  │                                └─→ result.blade.php
  │
  └─→ GET /fusion/alimentacion →  formFusionAlimentacion.blade.php
         (Formulario entrada)
      POST /fusion/alimentacion  → Procesa
                                   │
                                   └─→ result.blade.php
```

### Vistas

#### `index.blade.php`
- **Propósito**: Página de inicio, selector de tipo de recorrido
- **Componentes**:
  - Header con logo
  - Dos tarjetas (cards):
    - "Distribución": recorre red cable-cable
    - "Alimentación": recorre cabecera-splitter
- **Estilos**: Dark theme (Cyan accent `#00d4aa`)

#### `formFusionDistribucion.blade.php` / `formFusionAlimentacion.blade.php`
- **Propósito**: Entrada de datos
- **Campos**:
  - `id_cable_origen`: ID numérico del cable inicial (objectid)
  - `city`: Nombre de la ciudad (string)
- **Validaciones** (lado servidor):
  - `id_cable_origen` requerido y numérico
  - `city` requerido y string

#### `result.blade.php`
- **Propósito**: Mostrar resultados del recorrido
- **Componentes**:
  - Alert de éxito con contador de registros
  - Tabla con columnas:
    - Cable origen | Fil. origen | Fil. destino | Cable destino | TP origen | TP destino | Splitter
  - Badges visuales para `div1` / `div2`
  - Link de retorno

---

## 2️⃣ Capa de Lógica

### TablaFusionController

**Namespace**: `App\Http\Controllers`

#### Métodos de vista

```php
public function index()
    → render('TablaFusion.index')

public function recorridoDisForm()
    → render('TablaFusion.formFusionDistribucion')

public function formFusionAlimentacion()
    → render('TablaFusion.formFusionAlimentacion')
```

#### Métodos de lógica — Distribución

```php
public function fusionCableTp(
    int $id_cable_origen,
    int $id_cable_destino,
    int $id_tp_origen,
    int $id_tp_destino
): void
```

**Proceso**:
1. Obtiene registros de cable por `objectid`
2. Obtiene registros de TP por `objectid_1`
3. Explode de fibras activas y de reserva en arrays
4. For loop por cantidad de pares (longitud del array)
5. Por cada iteración:
   - Parse de par: `'1-2'` → `[1, 2]`
   - Genera 4 INSERT (activa1, activa2, reserva1, reserva2)
   - Si es último (`$es_ultimo`): agrega `splitter: 'div1'` o `'div2'`
6. Inserta en tabla `tabla_fusion_laravel`

**Tabla resultante**:
```
+----+---+---+---+---+---+---+---+-------+---+
| id | ido| idu | ido| idu | fo| fd| split| ...
+----+---+---+---+---+---+---+---+-------+---+
| .. | 1 | 2 | 3 | 4 | 1 | 13| NULL | ... |  ← activa A
| .. | 1 | 2 | 3 | 4 | 2 | 14| NULL | ... |  ← activa B
| .. | 1 | 2 | 3 | 4 | 5 | 17| div1 | ... |  ← activa final→splitter
| .. | 1 | 2 | 3 | 4 | 7 | 19| NULL | ... |  ← reserva A
```

```php
public function fusionTpTp(
    int $id_cable_origen,
    int $id_tp_origen,
    int $id_tp_destino
): void
```

**Proceso** (caso final sin siguiente cable):
1. Obtiene último cable y TPs destino
2. Explode de fibras activas y de reserva
3. Split de primer y segundo filamento
4. Solo 4 INSERT pero TODO va a splitter:
   - Activa1 → `splitter: 'div1'`
   - Activa2 → `splitter: 'div2'`
   - Reserva1 → NULL
   - Reserva2 → NULL

```php
public function recorridoCableDistribucion(Request $request)
```

**Orquestador principal**:
1. Valida entrada (`id_cable_origen`, `city`)
2. Limpia tabla anterior: `DELETE FROM tabla_fusion_laravel`
3. **While loop** automático:
   ```php
   while (!is_null($cable_destino)) {
       // Buscar siguiente cable
       $cable_destino = DB::table('cable')
           ->where('origen', $cable_origen->destino)
           ->where('ciudad', $city)
           ->first();
           
       if (is_null($cable_destino)) {
           // Final: llamar a fusionTpTp()
           break;
       }
       
       // Hay siguiente: llamar a fusionCableTp()
       $this->fusionCableTp(...);
       
       // Avanzar
       $cable_origen = $cable_destino;
   }
   ```
4. Retorna a vista `result` con `$fusiones = DB::table(...)->get()`

#### Métodos de lógica — Alimentación

```php
public function fusionAlimentacion(...)
public function recorridoAlimentacion(Request $request)
```

Similar a distribución pero:
- Usa campos `fibras_totales` y `fi_x` (en lugar de `fibras_act` y `fibras_res`)
- For loop itera entre primer y último filamento (rango)
- Insertaen tabla `tabla_fusion_alimentacion`

---

## 3️⃣ Capa de Acceso a Datos

### Queries principales

```php
// Obtener cable por objectid
DB::table('cable')
    ->where('objectid', $id)
    ->first()

// Obtener TP por código
DB::table('telecom_premises')
    ->where('codigo', $codigo)
    ->where('ciudad', $ciudad)
    ->first()

// Buscar siguiente cable
DB::table('cable')
    ->where('origen', $destino_anterior)
    ->where('ciudad', $ciudad)
    ->first()

// Insertar fusión
DB::table('tabla_fusion_laravel')->insert([
    'id_cable_origen' => ...,
    'filamento_origen' => ...,
    // ... etc
])

// Listar resultados
DB::table('tabla_fusion_laravel')->get()

// Limpiar
DB::table('tabla_fusion_laravel')->delete()
```

### Índices recomendados

```sql
-- Cable
CREATE INDEX idx_cable_objectid ON cable(objectid);
CREATE INDEX idx_cable_origen_destino ON cable(origen, destino);
CREATE INDEX idx_cable_ciudad ON cable(ciudad);

-- Telecom Premises
CREATE INDEX idx_tp_objectid1 ON telecom_premises(objectid_1);
CREATE INDEX idx_tp_codigo_ciudad ON telecom_premises(codigo, ciudad);

-- Tabla Fusión
CREATE INDEX idx_tf_origen ON tabla_fusion_laravel(id_cable_origen);
CREATE INDEX idx_tf_destino ON tabla_fusion_laravel(id_cable_destino);
```

---

## 4️⃣ Capa de Persistencia

### Schema (migraciones)

#### `cable`
```php
$table->increments('objectid');          // PK
$table->string('codigo', 50);            // ej: 'CBL-001'
$table->string('origen', 50);            // TP código origen
$table->string('destino', 50);           // TP código destino
$table->text('fibras_act');              // ej: '1-2,3-4,5-6'
$table->text('fibras_res');              // ej: '7-8,9-10,11-12'
$table->text('fibras_totales');          // ej: '1-48' (rango)
$table->text('fi_x');                    // ej: '101' (punto acceso)
$table->string('ciudad', 100);           // Filtro de proyecto
$table->timestamps();
```

#### `telecom_premises`
```php
$table->increments('id');                // PK local
$table->integer('objectid_1')->unique(); // Identificador real
$table->string('codigo', 50);            // ej: 'CTO-001'
$table->string('ciudad', 100);           // Filtro de proyecto
$table->timestamps();
```

#### `tabla_fusion_laravel`
```php
$table->id();                            // PK
$table->integer('id_cable_origen');      // FK
$table->string('filamento_origen', 20);  // ej: '1'
$table->string('filamento_destino', 20); // ej: '13'
$table->integer('id_cable_destino');     // FK
$table->string('cod_tramo_origen', 50);  // Código cable origin
$table->string('cod_tramo_destino', 50); // Código cable destino
$table->string('splitter', 10);          // 'div1', 'div2', null
$table->integer('id_objeto_origen');     // FK TP
$table->integer('id_objeto_destino');    // FK TP
$table->string('cod_objeto_origen', 50);
$table->string('cod_objeto_destino', 50);
$table->timestamps();
```

#### `tabla_fusion_alimentacion`
Schema idéntico a `tabla_fusion_laravel`.

---

## 🔀 Flujo Completo Paso a Paso

### Ejemplo: User ej 764 en "VEGAS"

```
1. Usuario entra a GET /fusion/distribucion
   → Ve formulario

2. Usuario submite:
   ├─ id_cable_origen: 764
   └─ city: VEGAS

3. POST /fusion/distribucion
   → recorridoCableDistribucion() inicia

4. Validación OK → limpiar tabla

5. $cable_origen = DB::table('cable')->where('objectid', 764)->first()
   Resultado: {objectid: 764, codigo: 'CBL-050', origen: 'CTO-A', 
               destino: 'CTO-B', fibras_act: '1-2,3-4,5-6', ...}

6. ITERACIÓN 1 — While
   $cable_destino = db→where('origen', 'CTO-B')->first()
   Resultado: {objectid: 765, codigo: 'CBL-051', ...}
   
   Encontrado → fusionCableTp(764, 765, ...)
   ├─ explode fibras → ['1-2', '3-4', '5-6']
   ├─ for i=0, 1, 2:
   │  ├─ i=0: insert(filamento_origen=1, destino=13, splitter=null)
   │  ├─ i=0: insert(filamento_origen=2, destino=14, splitter=null)
   │  ├─ i=0: insert(filamento_origen=7, destino=19, splitter=null)
   │  ├─ i=0: insert(filamento_origen=8, destino=20, splitter=null)
   │  ├─ i=1: (mismo proceso con pares siguiente)
   │  └─ i=2: (último) → splitter='div1', 'div2', ...
   │
   ├─ Total: 4 × 3 = 12 INSERT en tabla_fusion_laravel

7. ITERACIÓN 2 — While
   $cable_origen = 765
   $cable_destino = db→where('origen', 'CTO-C')->first()
   Resultado: {objectid: 766, ...}
   
   fusionCableTp(765, 766, ...)
   ├─ Repeat proceso
   └─ Otros 12 INSERT

8. ITERACIÓN N — While
   $cable_destino = db→where('origen', 'FINAL')->first()
   Resultado: null (no hay siguiente)
   
   fusionTpTp() ← Llamada final
   └─ Todo a splitter

9. EXIT while

10. $fusiones = DB::table('tabla_fusion_laravel')->get()
    Resultado: Collection con ~48 registros (12 × 4)

11. return view('result', ['fusiones' => $fusiones, 'total' => 48])

12. result.blade.php renderiza tabla HTML
    ├─ 48 filas
    ├─ badges para splitter
    └─ links de acción
```

---

## ⚠️ Consideraciones de Rendimiento

### O(n) complexidad

```
Cables = 10
Pares por cable = 3 (activa1, activa2, reserva1, reserva2) = 4 inserts
Total = 10 * 3 * 4 = 120 registros
```

Para redes grandes (100+ cables):
- Considerar batch inserts: `insertBatch()`
- Considerar index en `(id_cable_origen, splitter)`
- Considerar particionamiento horizontal de `tabla_fusion_laravel`

### Queries en todo

Por cada iteración se ejecutan:
```
1. SELECT cable (origen)
2. SELECT cable (destino)
3. SELECT telecom_premises (origen) × 1-2 veces
4. SELECT telecom_premises (destino) × 1-2 veces
5. INSERT × 4
```

Total: ~11 queries × N cables = **11N queries**.

**Mejora**: Cargar todos los datos en memoria al inicio y hacer join en PHP.

---

## 🔗 Relaciones y Constraints

```
cable.origen → telecom_premises.codigo (loose FK)
cable.destino → telecom_premises.codigo (loose FK)
tabla_fusion_laravel.id_cable_origen → cable.objectid (conceptual)
tabla_fusion_laravel.id_objeto_origen → telecom_premises.objectid_1 (conceptual)
```

Nota: Las FK no están definidas en BD porque el modelo era heredado.

---

## 📊 Diagrama de Secuencia

```
┌──────────────────────────────────────────────┐
│ Usuario                                      │
└────────────────────┬─────────────────────────┘
                     │
                     │ POST /fusion/distribucion
                     │ (id_cable, city)
                     ▼
┌──────────────────────────────────────────────────────────┐
│ TablaFusionController::recorridoCableDistribucion()      │
├──────────────────────────────────────────────────────────┤
│  1. Validar entrada                                      │
│  2. Limpiar tabla_fusion_laravel                         │
│  3. Cargar cable inicial                                 │
│  4. WHILE (hay siguiente cable)                          │
│     ├─ Buscar siguiente cable                            │
│     ├─ Si existe:                                        │
│     │   └─ fusionCableTp() → 4 inserts                   │
│     └─ Si no existe:                                     │
│         └─ fusionTpTp() → 4 inserts al splitter          │
│  5. Cargar resultados                                    │
│  6. Render result.blade.php                              │
└────────┬───────────────────────────────────┬─────────────┘
         │                                   │
         │  SELECT cable                     │
         │  SELECT telecom_premises          │
         ▼                                   ▼
    ┌─────────────────────────────────────────────┐
    │         Base de Datos                       │
    ├─────────────────────────────────────────────┤
    │ cable, telecom_premises,                    │
    │ tabla_fusion_laravel                        │
    └────────────────────┬────────────────────────┘
                         │
                         │ Collection<Fusion>
                         ▼
                    ┌─────────────────┐
                    │  result.blade   │
                    │  (HTML tabla)   │
                    └────────┬────────┘
                             │
                             │ HTTP Response
                             ▼
                         ┌──────────────┐
                         │ Navegador    │
                         │ (HTML render)│
                         └──────────────┘
```

---

## 🚀 Deployment

### Requisitos mínimos

```
- PHP 8.0.2+
- Laravel 9.19+
- Composer
- Node.js 14+ (para assets)
- MySQL 5.7+ o PostgreSQL 12+
```

### Environment

```bash
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stack
DB_CONNECTION=mysql
DB_HOST=servidor.com
DB_PORT=3306
DB_DATABASE=produccion
DB_USERNAME=usuario_bd
DB_PASSWORD=***
```

### Checks pre-deploy

```bash
php artisan config:cache
php artisan route:cache
npm run build
php artisan migrate --force
```

---

## 📝 Notas finales

- El modelo heredado (`fibras_act = '1-2,3-4'`) es una limitación aceptada dado que el sistema estaba en producción
- La versión mejorada en `/sql/improved/` muestra cómo se hubiera diseñado con libertad
- El controlador PHP mantiene la misma lógica que el SQL para facilitar mantenimiento
- No hay validaciones de integridad de datos (confía en datos limpios del GIS)
