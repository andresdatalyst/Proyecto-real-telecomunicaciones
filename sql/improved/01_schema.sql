-- ============================================================================
-- PROYECTO:  Motor de Fusiones FTTH
-- ARCHIVO:   01_schema.sql  (versión mejorada - esquema normalizado)
-- AUTOR:     Andrés Provira
-- ============================================================================
--
-- POR QUÉ EXISTE ESTA VERSIÓN:
-- La versión original resolvió el problema en producción, pero trabajaba
-- sobre un modelo heredado donde las fibras se guardaban como texto.
-- Este archivo propone el diseño que se habría hecho desde cero:
--   ✓ Una fila por fibra (modelo normalizado)
--   ✓ Sin parsing de strings
--   ✓ JOINs en lugar de loops + split_part
--   ✓ Mejor rendimiento y mantenibilidad
-- ============================================================================


-- ============================================================================
-- TABLAS
-- ============================================================================

-- Puntos de conexión física de la red (CTOs, armarios, splitters)
CREATE TABLE telecom_premises (
    id      SERIAL       PRIMARY KEY,
    codigo  VARCHAR(50)  NOT NULL,
    ciudad  VARCHAR(100) NOT NULL,
    UNIQUE (codigo, ciudad)
);

-- Tramos de cable entre dos TPs consecutivos
CREATE TABLE cable (
    id         SERIAL       PRIMARY KEY,
    codigo     VARCHAR(50)  NOT NULL,
    origen_tp  VARCHAR(50)  NOT NULL,   -- Código del TP de origen
    destino_tp VARCHAR(50)  NOT NULL,   -- Código del TP de destino
    ciudad     VARCHAR(100) NOT NULL
);

-- Fibras individuales de cada cable
-- CAMBIO CLAVE respecto al modelo original:
-- En vez de '1-2,3-4,5-6' en un campo de texto,
-- cada par de filamentos ocupa una fila propia.
CREATE TABLE cable_fibra (
    id          SERIAL      PRIMARY KEY,
    cable_id    INTEGER     NOT NULL REFERENCES cable(id) ON DELETE CASCADE,
    orden       INTEGER     NOT NULL,                              -- Posición en el recorrido
    filamento_a INTEGER     NOT NULL,                             -- Primer número del par
    filamento_b INTEGER     NOT NULL,                             -- Segundo número del par
    tipo        VARCHAR(10) NOT NULL CHECK (tipo IN ('activa', 'reserva')),
    UNIQUE (cable_id, orden, tipo)
);

-- Tabla de resultados de fusiones generadas
CREATE TABLE fusion_resultado (
    id                 SERIAL       PRIMARY KEY,
    cable_origen_id    INTEGER,
    cable_destino_id   INTEGER,
    filamento_origen   INTEGER,
    filamento_destino  INTEGER,
    tipo               VARCHAR(10),
    splitter           VARCHAR(10),
    tp_origen_id       INTEGER,
    tp_destino_id      INTEGER,
    cod_cable_origen   VARCHAR(50),
    cod_cable_destino  VARCHAR(50),
    cod_tp_origen      VARCHAR(50),
    cod_tp_destino     VARCHAR(50),
    fecha_generacion   TIMESTAMP DEFAULT NOW()
);
