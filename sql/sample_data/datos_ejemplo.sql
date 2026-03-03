-- ============================================================================
-- PROYECTO:  Motor de Fusiones FTTH
-- ARCHIVO:   datos_ejemplo.sql
-- ============================================================================
-- Dataset reproducible para probar las funciones sin datos de producción.
-- Simula una red FTTH con 4 cables y 5 TPs en una ciudad de ejemplo.
--
-- ORDEN DE EJECUCIÓN:
--   1. sql/improved/01_schema.sql
--   2. sql/sample_data/datos_ejemplo.sql   ← este archivo
--   3. sql/improved/02_funciones.sql
--   4. SELECT recorrido_red(1, 'CIUDAD EJEMPLO');
-- ============================================================================

-- Limpiar datos previos
TRUNCATE TABLE fusion_resultado RESTART IDENTITY CASCADE;
TRUNCATE TABLE cable_fibra      RESTART IDENTITY CASCADE;
TRUNCATE TABLE cable            RESTART IDENTITY CASCADE;
TRUNCATE TABLE telecom_premises RESTART IDENTITY CASCADE;

-- ============================================================================
-- TPs: puntos de conexión de la red
-- ============================================================================
INSERT INTO telecom_premises (codigo, ciudad) VALUES
    ('CTO-001', 'CIUDAD EJEMPLO'),   -- Inicio de la red
    ('CTO-002', 'CIUDAD EJEMPLO'),
    ('CTO-003', 'CIUDAD EJEMPLO'),
    ('CTO-004', 'CIUDAD EJEMPLO'),
    ('CTO-005', 'CIUDAD EJEMPLO');   -- Splitter final

-- ============================================================================
-- Cables: tramos entre TPs
-- ============================================================================
INSERT INTO cable (codigo, origen_tp, destino_tp, ciudad) VALUES
    ('CBL-A', 'CTO-001', 'CTO-002', 'CIUDAD EJEMPLO'),   -- id: 1
    ('CBL-B', 'CTO-002', 'CTO-003', 'CIUDAD EJEMPLO'),   -- id: 2
    ('CBL-C', 'CTO-003', 'CTO-004', 'CIUDAD EJEMPLO'),   -- id: 3
    ('CBL-D', 'CTO-004', 'CTO-005', 'CIUDAD EJEMPLO');   -- id: 4 (último)

-- ============================================================================
-- Fibras: una fila por par de filamentos
-- Cada cable tiene 3 pares activos + 3 de reserva
-- El par orden=3 es el último → irá al splitter (div1/div2)
-- ============================================================================

-- Cable A
INSERT INTO cable_fibra (cable_id, orden, filamento_a, filamento_b, tipo) VALUES
    (1, 1,  1,  2, 'activa'),
    (1, 2,  3,  4, 'activa'),
    (1, 3,  5,  6, 'activa'),   -- → splitter
    (1, 1,  7,  8, 'reserva'),
    (1, 2,  9, 10, 'reserva'),
    (1, 3, 11, 12, 'reserva');

-- Cable B
INSERT INTO cable_fibra (cable_id, orden, filamento_a, filamento_b, tipo) VALUES
    (2, 1, 13, 14, 'activa'),
    (2, 2, 15, 16, 'activa'),
    (2, 3, 17, 18, 'activa'),
    (2, 1, 19, 20, 'reserva'),
    (2, 2, 21, 22, 'reserva'),
    (2, 3, 23, 24, 'reserva');

-- Cable C
INSERT INTO cable_fibra (cable_id, orden, filamento_a, filamento_b, tipo) VALUES
    (3, 1, 25, 26, 'activa'),
    (3, 2, 27, 28, 'activa'),
    (3, 3, 29, 30, 'activa'),
    (3, 1, 31, 32, 'reserva'),
    (3, 2, 33, 34, 'reserva'),
    (3, 3, 35, 36, 'reserva');

-- Cable D (último tramo)
INSERT INTO cable_fibra (cable_id, orden, filamento_a, filamento_b, tipo) VALUES
    (4, 1, 37, 38, 'activa'),
    (4, 2, 39, 40, 'activa'),
    (4, 3, 41, 42, 'activa'),
    (4, 1, 43, 44, 'reserva'),
    (4, 2, 45, 46, 'reserva'),
    (4, 3, 47, 48, 'reserva');

-- Verificación
SELECT 'telecom_premises' AS tabla, COUNT(*) AS filas FROM telecom_premises
UNION ALL SELECT 'cable',       COUNT(*) FROM cable
UNION ALL SELECT 'cable_fibra', COUNT(*) FROM cable_fibra;
