-- ============================================================================
-- PROYECTO:  Motor de Fusiones FTTH
-- ARCHIVO:   02_funciones.sql  (versión mejorada - funciones refactorizadas)
-- AUTOR:     Andrés Provira
-- ============================================================================
-- Ejecutar después de 01_schema.sql y sample_data/datos_ejemplo.sql
-- ============================================================================


-- ============================================================================
-- FUNCIÓN: fusionar_cables
-- ============================================================================
-- Genera las fusiones entre dos cables usando un JOIN directo por orden.
-- No hay arrays, no hay loops de parsing, no hay split_part.
--
-- La regla del splitter (último par → div1/div2) se aplica con un CASE
-- dentro de la propia query, sin necesidad de un IF en un bucle.
--
-- COMPARATIVA con la versión original:
--   ANTES: string_to_array → FOR loop → split_part → 4 RETURN QUERY
--   AHORA: JOIN por orden   → CASE     → INSERT con UNION ALL
-- ============================================================================

CREATE OR REPLACE FUNCTION fusionar_cables(
    p_cable_origen  INTEGER,
    p_cable_destino INTEGER,
    p_tp_origen     INTEGER,
    p_tp_destino    INTEGER
)
RETURNS VOID AS $$
DECLARE
    v_max_orden INTEGER;
BEGIN
    -- El último orden activa la regla del splitter
    SELECT MAX(orden) INTO v_max_orden
    FROM cable_fibra
    WHERE cable_id = p_cable_origen;

    INSERT INTO fusion_resultado (
        cable_origen_id, cable_destino_id,
        filamento_origen, filamento_destino,
        tipo, splitter,
        tp_origen_id, tp_destino_id,
        cod_cable_origen, cod_cable_destino,
        cod_tp_origen, cod_tp_destino
    )
    -- Primer filamento de cada par (filamento_a)
    SELECT
        f1.cable_id, f2.cable_id,
        f1.filamento_a, f2.filamento_a,
        f1.tipo,
        CASE
            WHEN f1.orden = v_max_orden AND f1.tipo = 'activa' THEN 'div1'
            ELSE NULL
        END,
        p_tp_origen, p_tp_destino,
        c1.codigo, c2.codigo,
        tp1.codigo, tp2.codigo
    FROM cable_fibra f1
    JOIN cable_fibra f2       ON f1.orden = f2.orden AND f1.tipo = f2.tipo
    JOIN cable c1             ON c1.id = f1.cable_id
    JOIN cable c2             ON c2.id = f2.cable_id
    JOIN telecom_premises tp1 ON tp1.id = p_tp_origen
    JOIN telecom_premises tp2 ON tp2.id = p_tp_destino
    WHERE f1.cable_id = p_cable_origen
      AND f2.cable_id = p_cable_destino

    UNION ALL

    -- Segundo filamento de cada par (filamento_b)
    SELECT
        f1.cable_id, f2.cable_id,
        f1.filamento_b, f2.filamento_b,
        f1.tipo,
        CASE
            WHEN f1.orden = v_max_orden AND f1.tipo = 'activa' THEN 'div2'
            ELSE NULL
        END,
        p_tp_origen, p_tp_destino,
        c1.codigo, c2.codigo,
        tp1.codigo, tp2.codigo
    FROM cable_fibra f1
    JOIN cable_fibra f2       ON f1.orden = f2.orden AND f1.tipo = f2.tipo
    JOIN cable c1             ON c1.id = f1.cable_id
    JOIN cable c2             ON c2.id = f2.cable_id
    JOIN telecom_premises tp1 ON tp1.id = p_tp_origen
    JOIN telecom_premises tp2 ON tp2.id = p_tp_destino
    WHERE f1.cable_id = p_cable_origen
      AND f2.cable_id = p_cable_destino;

END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- FUNCIÓN: fusionar_cable_tp_final
-- ============================================================================
-- Caso final del recorrido: el último cable llega a un TP sin continuación.
-- Todos los filamentos activos van al splitter (div1/div2).
-- ============================================================================

CREATE OR REPLACE FUNCTION fusionar_cable_tp_final(
    p_cable_origen INTEGER,
    p_tp_origen    INTEGER,
    p_tp_destino   INTEGER
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO fusion_resultado (
        cable_origen_id, cable_destino_id,
        filamento_origen, filamento_destino,
        tipo, splitter,
        tp_origen_id, tp_destino_id,
        cod_cable_origen,
        cod_tp_origen, cod_tp_destino
    )
    -- filamento_a
    SELECT
        f.cable_id, NULL,
        f.filamento_a, NULL,
        f.tipo,
        CASE WHEN f.tipo = 'activa' THEN 'div1' ELSE NULL END,
        p_tp_origen, p_tp_destino,
        c.codigo, tp1.codigo, tp2.codigo
    FROM cable_fibra f
    JOIN cable             c   ON c.id   = f.cable_id
    JOIN telecom_premises tp1  ON tp1.id = p_tp_origen
    JOIN telecom_premises tp2  ON tp2.id = p_tp_destino
    WHERE f.cable_id = p_cable_origen

    UNION ALL

    -- filamento_b
    SELECT
        f.cable_id, NULL,
        f.filamento_b, NULL,
        f.tipo,
        CASE WHEN f.tipo = 'activa' THEN 'div2' ELSE NULL END,
        p_tp_origen, p_tp_destino,
        c.codigo, tp1.codigo, tp2.codigo
    FROM cable_fibra f
    JOIN cable             c   ON c.id   = f.cable_id
    JOIN telecom_premises tp1  ON tp1.id = p_tp_origen
    JOIN telecom_premises tp2  ON tp2.id = p_tp_destino
    WHERE f.cable_id = p_cable_origen;

END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- FUNCIÓN: recorrido_red
-- ============================================================================
-- Motor de recorrido automático. Misma lógica que recorridoCable() original
-- pero con una única query por iteración en lugar de tres SELECT INTO separados.
-- ============================================================================

CREATE OR REPLACE FUNCTION recorrido_red(
    p_cable_inicio INTEGER,
    p_ciudad       TEXT
)
RETURNS VOID AS $$
DECLARE
    v_cable_actual    INTEGER := p_cable_inicio;
    v_cable_siguiente INTEGER;
    v_tp_origen       INTEGER;
    v_tp_destino      INTEGER;
BEGIN
    LOOP
        -- Obtener TP origen, TP destino y cable siguiente en una sola query
        SELECT
            tp_origen.id,
            tp_destino.id,
            c_siguiente.id
        INTO
            v_tp_origen,
            v_tp_destino,
            v_cable_siguiente
        FROM cable c
        JOIN telecom_premises tp_origen
            ON tp_origen.codigo  = c.origen_tp  AND tp_origen.ciudad  = p_ciudad
        JOIN telecom_premises tp_destino
            ON tp_destino.codigo = c.destino_tp AND tp_destino.ciudad = p_ciudad
        LEFT JOIN cable c_siguiente
            ON c_siguiente.origen_tp = c.destino_tp AND c_siguiente.ciudad = p_ciudad
        WHERE c.id = v_cable_actual
        LIMIT 1;

        IF v_cable_siguiente IS NULL THEN
            -- Final del recorrido
            PERFORM fusionar_cable_tp_final(v_cable_actual, v_tp_origen, v_tp_destino);
            EXIT;
        ELSE
            -- Tramo intermedio
            PERFORM fusionar_cables(v_cable_actual, v_cable_siguiente, v_tp_origen, v_tp_destino);
        END IF;

        v_cable_actual := v_cable_siguiente;
    END LOOP;
END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- EJEMPLOS DE USO
-- ============================================================================

-- Recorrido completo desde el primer cable
-- SELECT recorrido_red(1, 'CIUDAD EJEMPLO');

-- Ver resultados ordenados
-- SELECT
--     cod_cable_origen,
--     cod_cable_destino,
--     filamento_origen,
--     filamento_destino,
--     tipo,
--     splitter,
--     cod_tp_origen,
--     cod_tp_destino
-- FROM fusion_resultado
-- ORDER BY cable_origen_id, tipo, filamento_origen;

-- Limpiar para nueva ejecución
-- DELETE FROM fusion_resultado;
