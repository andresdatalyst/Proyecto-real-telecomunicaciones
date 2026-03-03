# Quick Start — Instalación y Ejecución

Guía paso a paso para tener el proyecto funcionando en tu máquina local.

---

## ⚙️ Requisitos Previos

- **PHP** 8.0.2 o superior
- **Composer** (gestor de paquetes PHP)
- **Node.js** 14+ (incluye npm)
- **MySQL** 5.7+ O **PostgreSQL** 12+ (elige uno)
- **Git** (opcional, para clonar)

### Verificar instalación

```bash
php --version
composer --version
node --version
npm --version
mysql --version
# o
psql --version
```

---

## 📥 Instalación

### 1. Navegar al directorio

```bash
cd web
```

### 2. Instalar dependencias PHP

```bash
composer install
```

Esto descargará Laravel y todas las librerías necesarias en `/vendor`.

**Tiempo estimado**: 2-3 minutos

### 3. Instalar dependencias Node.js

```bash
npm install
```

Descarga dependencias de assets (Vite, etc).

**Tiempo estimado**: 1-2 minutos

### 4. Crear archivo `.env`

```bash
cp .env.example .env
```

Esto copia la plantilla de configuración. Ahora debes editar `.env` con tus credenciales.

### 5. Generar clave de aplicación

```bash
php artisan key:generate
```

Genera `APP_KEY` en tu `.env`. Necesario para encriptación.

---

## 🗄️ Configurar Base de Datos

Abre `.env` con tu editor y configura:

### Opción A: MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fusion_ftth          ← Crea esta BD primero
DB_USERNAME=root                 ← Tu usuario MySQL
DB_PASSWORD=                      ← Tu contraseña (vacío si es local)
```

**Crear la base de datos** (en terminal MySQL):

```bash
mysql -u root
```

```sql
CREATE DATABASE fusion_ftth;
EXIT;
```

### Opción B: PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=fusion_ftth
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

**Crear la base de datos**:

```bash
psql -U postgres
```

```sql
CREATE DATABASE fusion_ftth;
\q
```

---

## ✨ Crear tablas (Migraciones)

Ejecuta las migraciones de Laravel para crear las 4 tablas:

```bash
php artisan migrate
```

Deberías ver:

```
✓ Database\Migrations\2024_01_01_000001_create_cable_table
✓ Database\Migrations\2024_01_01_000002_create_telecom_premises_table
✓ Database\Migrations\2024_01_01_000003_create_tabla_fusion_laravel_table
✓ Database\Migrations\2024_01_01_000004_create_tabla_fusion_alimentacion_table
```

Si hay error, verifica:
- Credenciales de BD en `.env`
- Base de datos creada
- Servidor de BD corriendo

---

## 🌱 Cargar Datos de Ejemplo

Para probar con datos reales, tienes dos opciones:

### Opción A: Importar SQL directamente

**MySQL**:
```bash
mysql -u root fusion_ftth < ../sql/sample_data/datos_ejemplo.sql
```

**PostgreSQL**:
```bash
psql -U postgres -d fusion_ftth -f ../sql/sample_data/datos_ejemplo.sql
```

### Opción B: Crear Seeder de Laravel (recomendado)

Edita `database/seeders/DatabaseSeeder.php`:

```php
public function run()
{
    // Cables
    \DB::table('cable')->insert([
        [
            'objectid' => 1,
            'codigo' => 'CBL-A',
            'origen' => 'CTO-001',
            'destino' => 'CTO-002',
            'fibras_act' => '1-2,3-4,5-6',
            'fibras_res' => '7-8,9-10,11-12',
            'ciudad' => 'CIUDAD EJEMPLO',
        ],
        [
            'objectid' => 2,
            'codigo' => 'CBL-B',
            'origen' => 'CTO-002',
            'destino' => 'CTO-003',
            'fibras_act' => '13-14,15-16,17-18',
            'fibras_res' => '19-20,21-22,23-24',
            'ciudad' => 'CIUDAD EJEMPLO',
        ],
        // ... más cables
    ]);

    // Telecom Premises
    \DB::table('telecom_premises')->insert([
        ['objectid_1' => 101, 'codigo' => 'CTO-001', 'ciudad' => 'CIUDAD EJEMPLO'],
        ['objectid_1' => 102, 'codigo' => 'CTO-002', 'ciudad' => 'CIUDAD EJEMPLO'],
        ['objectid_1' => 103, 'codigo' => 'CTO-003', 'ciudad' => 'CIUDAD EJEMPLO'],
        ['objectid_1' => 104, 'codigo' => 'CTO-004', 'ciudad' => 'CIUDAD EJEMPLO'],
        ['objectid_1' => 105, 'codigo' => 'CTO-005', 'ciudad' => 'CIUDAD EJEMPLO'],
    ]);
}
```

Luego:

```bash
php artisan db:seed
```

---

## 🚀 Ejecutar la Aplicación

### Terminal 1: Compilar Assets (en vivo)

```bash
npm run dev
```

Verás:

```
  VITE v3.x.x  ready in 123 ms

  ➜  Local: http://localhost:5173/
```

Déjalo corriendo en background.

### Terminal 2: Servidor Laravel

```bash
php artisan serve
```

Verás:

```
   Laravel development server started: http://127.0.0.1:8000
```

---

## 🌐 Acceder a la aplicación

Abre en tu navegador:

```
http://localhost:8000
```

Deberías ver:

```
╔════════════════════════════════════════╗
║  Motor de Fusiones FTTH               ║
║                                        ║
║  01 / Distribución                    ║
║  Recorre automáticamente la red...    ║
║  [Iniciar →]                          ║
║                                        ║
║  02 / Alimentación                    ║
║  Recorre el tramo de cable...         ║
║  [Iniciar →]                          ║
╚════════════════════════════════════════╝
```

---

## ✅ Prueba el Flujo Completo

### Paso 1: Haz clic en "Distribución"

Te lleva a formulario de entrada.

### Paso 2: Ingresa datos de ejemplo

```
ID cable origen: 1
Ciudad: CIUDAD EJEMPLO
```

### Paso 3: Haz clic en "Ejecutar recorrido"

Debería mostrar tabla con fusiones generadas. Ejemplo:

```
┌──────────┬────────┬────────┬──────────┬──────────┬──────────┬─────────┐
│ Origen   │ Fil.O. │ Fil.D. │ Destino  │ TP Orig. │ TP Dest. │ Splitter│
├──────────┼────────┼────────┼──────────┼──────────┼──────────┼─────────┤
│ CBL-A    │ 1      │ 13     │ CBL-B    │ CTO-001  │ CTO-002  │ —       │
│ CBL-A    │ 2      │ 14     │ CBL-B    │ CTO-001  │ CTO-002  │ —       │
│ CBL-A    │ 5      │ 17     │ CBL-B    │ CTO-001  │ CTO-002  │ div1    │
│ CBL-A    │ 6      │ 18     │ CBL-B    │ CTO-001  │ CTO-002  │ div2    │
└──────────┴────────┴────────┴──────────┴──────────┴──────────┴─────────┘
```

✅ **¡Funciona!**

---

## 🛑 Parar la aplicación

```bash
# Terminal 1: Ctrl+C (detiene Vite)
# Terminal 2: Ctrl+C (detiene Laravel)
```

---

## 🔧 Troubleshooting

### Error: "Composer not found"

```bash
# Instala Composer: https://getcomposer.org
# O úsalo con PHP:
php composer.phar install
```

### Error: "npm not found"

```bash
# Instala Node.js: https://nodejs.org
# Incluye npm automáticamente
```

### Error: "SQLSTATE[HY000]: General error: 14 unable to open database file"

Significa que SQLite no puede acceder. En `.env`:

```env
# Cambia de SQLite a MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fusion_ftth
DB_USERNAME=root
DB_PASSWORD=
```

Luego: `php artisan migrate`

### Error: "No database selected"

```bash
# Verifica que la BD existe en MySQL:
mysql -u root
SHOW DATABASES;
# Si no está:
CREATE DATABASE fusion_ftth;
EXIT;
```

### Error: "No application key has been generated"

```bash
php artisan key:generate
```

### La página no carga con datos

Verifica:
1. ¿ Hiciste `php artisan migrate`?
2. ¿Cargaste datos de ejemplo?
3. ¿El servidor Laravel sigue corriendo (`php artisan serve`)?

---

## 📝 Comandos Útiles

```bash
# Ver estado de BD
php artisan model:show cable

# Ver migraciones ejecutadas
php artisan migrate:status

# Deshacer última migración
php artisan migrate:rollback

# Resetear BD y recargar
php artisan migrate:refresh --seed

# Limpiar caché
php artisan cache:clear

# Ver rutas
php artisan route:list

# Iniciar REPL (PHP shell)
php artisan tinker
```

---

## 🧪 Tests (Opcional)

Si quieres ejecutar tests:

```bash
php artisan test
```

Por ahora no hay tests, pero puedes agregarlos en `tests/`.

---

## ✨ Siguientes pasos

Una vez todo funciona:

1. Prueba con tus propios datos reales
2. Modifica estilos CSS en `resources/css/`
3. Agrega más funcionalidades en el controlador
4. Crea tests unitarios
5. Deploy a producción (servidor PHP)

---

## 📚 Documentación adicional

- [Arquitectura](./ARQUITECTURA.md) — Detalles técnicos
- [Decisiones técnicas](./decisiones_tecnicas.md) — Contexto del problema
- [Laravel Docs](https://laravel.com/docs/9.x) — Documentación oficial

---

## ❓ ¿Preguntas?

Si algo no funciona:
1. Revisa el archivo `.env` (credenciales de BD)
2. Verifica que los servidores de BD estén corriendo
3. Mira los logs en `storage/logs/laravel.log`
4. Revisa la [documentación de Laravel](https://laravel.com/docs)

¡A programar! 🚀
