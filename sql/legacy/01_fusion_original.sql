-- ============================================================================
-- PROYECTO:  Motor de Fusiones FTTH
-- ARCHIVO:   01_fusion_original.sql  (versión original - legacy)
-- AUTOR:     Andrés Provira
-- CONTEXTO:  Empresa de telecomunicaciones, despliegue de fibra óptica FTTH
-- BASE DE DATOS: PostgreSQL (PL/pgSQL)
-- ============================================================================
--
-- DESCRIPCIÓN DEL PROBLEMA REAL:
-- --------------------------------
-- En redes de fibra óptica FTTH (Fiber To The Home), los cables deben
-- fusionarse entre sí en puntos físicos llamados Telecom Premises (TP).
-- Este proceso se realizaba de forma manual. El objetivo fue automatizarlo
-- completamente a partir de los datos existentes en la base de datos.
--
-- La red tiene esta forma:
--
--   [TP-A] ---Cable 1--- [TP-B] ---Cable 2--- [TP-C] ---Cable 3--- [TP-D]
--
-- Cada cable lleva varias fibras. Cada fibra del cable A se fusiona con
-- la fibra equivalente del cable B, en el mismo orden de posición.
--
-- LIMITACIÓN TÉCNICA IMPORTANTE:
-- --------------------------------
-- El modelo de datos ya estaba en producción y no era modificable.
-- Las fibras activas y de reserva de cada cable se almacenaban como
-- un campo de texto con el formato:
--
--   FIBRAS_ACT = '1-2,3-4,5-6'
--
-- Cada elemento separado por ',' representa un par de filamentos (A-B).
-- Cada par corresponde a la fusión con un TP del recorrido.
-- Esto obliga a usar parsing manual con string_to_array y split_part.
-- (Ver versión mejorada en /sql/improved/ para el diseño normalizado)
--
-- REGLA DE NEGOCIO CLAVE:
-- --------------------------------
-- El ÚLTIMO par de fibras de cada cable no se fusiona cable-cable,
-- sino que va al SPLITTER del TP final (marcado como div1 y div2).
-- El splitter divide la señal óptica hacia los usuarios finales.
--
-- TABLAS DEL SISTEMA:
-- --------------------------------
--   cable              → Tramos de fibra óptica entre dos TPs
--   telecom_premises   → Puntos de conexión (armarios, CTOs, splitters)
--   tabla_fusion       → Tabla resultado con todas las fusiones generadas
-- ============================================================================


-- ============================================================================
-- SECCIÓN 1: CONSULTA EXPLORATORIA
-- ============================================================================
-- Consulta de análisis usada durante el desarrollo para entender el formato
-- de los datos y validar el parsing antes de construir las funciones.
-- ============================================================================

SELECT
    c1.objectid                                        AS id_cable_origen,
    SPLIT_PART(SPLIT_PART(c1.FIBRAS_ACT, ',', 1), '-', 1) AS filamento_origen,
    SPLIT_PART(SPLIT_PART(c2.FIBRAS_ACT, ',', 1), '-', 1) AS filamento_destino,
    c2.objectid                                        AS id_cable_destino,
    c1.codigo                                          AS cod_tramo_origen,
    c2.codigo                                          AS cod_tramo_destino,
    ''                                                 AS splitter,
    t1.objectid_1                                      AS id_objeto_origen,
    t2.objectid_1                                      AS id_objeto_destino,
    t1.codigo                                          AS cod_objeto_origen,
    t2.codigo                                          AS cod_objeto_destino
FROM cable c1, cable c2, telecom_premises t1, telecom_premises t2
WHERE c1.objectid  = 763   -- Cable de origen del tramo
  AND c2.objectid  = 764   -- Cable de destino del tramo
  AND t1.objectid_1 = 110  -- TP de origen
  AND t2.objectid_1 = 61;  -- TP de destino


-- ============================================================================
-- SECCIÓN 2: FUNCIÓN fusion_cable_tp
-- ============================================================================
-- Genera las fusiones entre DOS CABLES CONSECUTIVOS de la red.
--
-- PARÁMETROS:
--   cable_origen  → objectid del cable de inicio del tramo
--   cable_destino → objectid del cable siguiente en el recorrido
--   tp_origen     → objectid_1 del TP donde empieza el tramo
--   tp_destino    → objectid_1 del TP donde termina el tramo
--
-- RETORNA:
--   Una fila por cada filamento fusionado (activos + reservas).
--   4 filas por posición del array: act_fil1, act_fil2, res_fil1, res_fil2.
--
-- LÓGICA INTERNA:
--   1. Convierte FIBRAS_ACT y FIBRAS_RES de texto a array con string_to_array
--   2. Recorre el array posición a posición con un FOR
--   3. En cada posición extrae los dos filamentos del par con split_part
--   4. Al llegar al último elemento aplica la regla del splitter (div1/div2)
-- ============================================================================

CREATE OR REPLACE FUNCTION fusion_cable_tp(
    cable_origen  NUMERIC,
    cable_destino NUMERIC,
    tp_origen     NUMERIC,
    tp_destino    NUMERIC
)
RETURNS TABLE (
    id_cable_origen    NUMERIC,
    filamento_origen   TEXT,
    filamento_destino  TEXT,
    id_cable_destino   NUMERIC,
    cod_tramo_origen   VARCHAR,
    cod_tramo_destino  VARCHAR,
    splitter           VARCHAR,
    id_objeto_origen   NUMERIC,
    id_objecto_destino NUMERIC,   -- Typo en nombre mantenido por compatibilidad con tabla destino
    cod_objeto_origen  VARCHAR,
    cod_objeto_destino VARCHAR
)
AS $$
DECLARE
    splitter      VARCHAR;
    fibras_act1   TEXT[];    -- Array de fibras activas del cable origen
    fibras_act2   TEXT[];    -- Array de fibras activas del cable destino
    fibras_res1   TEXT[];    -- Array de fibras de reserva del cable origen
    fibras_res2   TEXT[];    -- Array de fibras de reserva del cable destino
    cont          INTEGER;   -- Posición base para split_part (siempre 1 en este contexto)

BEGIN
    splitter := '';
    cont     := 1;

    -- -------------------------------------------------------------------------
    -- Convertir campos de texto en arrays para poder iterarlos
    -- Ejemplo: '1-2,3-4,5-6' → ARRAY['1-2', '3-4', '5-6']
    -- -------------------------------------------------------------------------
    SELECT string_to_array(c.FIBRAS_ACT, ',') INTO fibras_act1
    FROM cable c WHERE c.objectid = cable_origen;

    SELECT string_to_array(c.FIBRAS_ACT, ',') INTO fibras_act2
    FROM cable c WHERE c.objectid = cable_destino;

    SELECT string_to_array(c.FIBRAS_RES, ',') INTO fibras_res1
    FROM cable c WHERE c.objectid = cable_origen;

    SELECT string_to_array(c.FIBRAS_RES, ',') INTO fibras_res2
    FROM cable c WHERE c.objectid = cable_destino;

    -- -------------------------------------------------------------------------
    -- Bucle principal: una iteración por par de fibras
    -- El número de elementos del array = número de fusiones a realizar
    -- -------------------------------------------------------------------------
    FOR counter IN 1..array_length(fibras_act1, 1) BY 1
    LOOP

        -- ---------------------------------------------------------------------
        -- REGLA DE NEGOCIO: último elemento → fibras que van al splitter
        -- div1 = primer filamento del par, div2 = segundo filamento del par
        -- ---------------------------------------------------------------------
        IF counter = array_length(fibras_act1, 1) THEN
            splitter := 'div1';
        END IF;

        -- Fibras activas — primer filamento ('1-2' → extrae '1')
        RETURN QUERY
        SELECT
            c1.objectid, SPLIT_PART(fibras_act1[counter], '-', cont),
            SPLIT_PART(fibras_act2[counter], '-', cont),
            c2.objectid, c1.codigo, c2.codigo, splitter,
            t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
        FROM cable c1, cable c2, telecom_premises t1, telecom_premises t2
        WHERE c1.objectid  = cable_origen
          AND c2.objectid  = cable_destino
          AND t1.objectid_1 = tp_origen
          AND t2.objectid_1 = tp_destino;

        IF counter = array_length(fibras_act1, 1) THEN
            splitter := 'div2';
        END IF;

        -- Fibras activas — segundo filamento ('1-2' → extrae '2')
        RETURN QUERY
        SELECT
            c1.objectid, SPLIT_PART(fibras_act1[counter], '-', cont + 1),
            SPLIT_PART(fibras_act2[counter], '-', cont + 1),
            c2.objectid, c1.codigo, c2.codigo, splitter,
            t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
        FROM cable c1, cable c2, telecom_premises t1, telecom_premises t2
        WHERE c1.objectid  = cable_origen
          AND c2.objectid  = cable_destino
          AND t1.objectid_1 = tp_origen
          AND t2.objectid_1 = tp_destino;

        -- Las fibras de reserva nunca van al splitter (se dejan para uso futuro)
        splitter := '';

        -- Fibras de reserva — primer filamento
        RETURN QUERY
        SELECT
            c1.objectid, SPLIT_PART(fibras_res1[counter], '-', cont),
            SPLIT_PART(fibras_res2[counter], '-', cont),
            c2.objectid, c1.codigo, c2.codigo, splitter,
            t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
        FROM cable c1, cable c2, telecom_premises t1, telecom_premises t2
        WHERE c1.objectid  = cable_origen
          AND c2.objectid  = cable_destino
          AND t1.objectid_1 = tp_origen
          AND t2.objectid_1 = tp_destino;

        -- Fibras de reserva — segundo filamento
        RETURN QUERY
        SELECT
            c1.objectid, SPLIT_PART(fibras_res1[counter], '-', cont + 1),
            SPLIT_PART(fibras_res2[counter], '-', cont + 1),
            c2.objectid, c1.codigo, c2.codigo, splitter,
            t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
        FROM cable c1, cable c2, telecom_premises t1, telecom_premises t2
        WHERE c1.objectid  = cable_origen
          AND c2.objectid  = cable_destino
          AND t1.objectid_1 = tp_origen
          AND t2.objectid_1 = tp_destino;

    END LOOP;
END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- SECCIÓN 3: FUNCIÓN fusion_tp_tp
-- ============================================================================
-- Caso especial para el FINAL del recorrido.
-- Cuando no existe cable siguiente, el último cable llega directamente
-- al TP final (splitter de distribución). No hay cable destino.
--
-- DIFERENCIA con fusion_cable_tp:
--   - Solo recibe 3 parámetros (no hay cable_destino)
--   - Las fibras no están en array de pares sino directamente en el campo
--   - Todos los filamentos activos van al splitter (div1 y div2)
--   - id_cable_destino se devuelve como 0 (sin cable destino)
-- ============================================================================

CREATE OR REPLACE FUNCTION fusion_tp_tp(
    cable_origen NUMERIC,
    tp_origen    NUMERIC,
    tp_destino   NUMERIC
)
RETURNS TABLE (
    id_cable_origen    NUMERIC,
    filamento_origen   TEXT,
    filamento_destino  TEXT,
    id_cable_destino   INTEGER,
    cod_tramo_origen   VARCHAR,
    cod_tramo_destino  VARCHAR,
    splitter           VARCHAR,
    id_objeto_origen   NUMERIC,
    id_objecto_destino NUMERIC,
    cod_objeto_origen  VARCHAR,
    cod_objeto_destino VARCHAR
)
AS $$
DECLARE
    splitter          VARCHAR;
    filamento_destino TEXT    := '';
    cod_tramo_destino VARCHAR := '';
BEGIN

    -- Activas → primer filamento al splitter (div1)
    splitter := 'div1';
    RETURN QUERY
    SELECT c1.objectid, SPLIT_PART(c1.fibras_act, '-', 1),
           filamento_destino, 0, c1.codigo, cod_tramo_destino, splitter,
           t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
    FROM cable c1, telecom_premises t1, telecom_premises t2
    WHERE c1.objectid   = cable_origen
      AND t1.objectid_1 = tp_origen
      AND t2.objectid_1 = tp_destino;

    -- Activas → segundo filamento al splitter (div2)
    splitter := 'div2';
    RETURN QUERY
    SELECT c1.objectid, SPLIT_PART(c1.fibras_act, '-', 2),
           filamento_destino, 0, c1.codigo, cod_tramo_destino, splitter,
           t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
    FROM cable c1, telecom_premises t1, telecom_premises t2
    WHERE c1.objectid   = cable_origen
      AND t1.objectid_1 = tp_origen
      AND t2.objectid_1 = tp_destino;

    -- Reservas → sin splitter
    splitter := '';
    RETURN QUERY
    SELECT c1.objectid, SPLIT_PART(c1.fibras_res, '-', 1),
           filamento_destino, 0, c1.codigo, cod_tramo_destino, splitter,
           t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
    FROM cable c1, telecom_premises t1, telecom_premises t2
    WHERE c1.objectid   = cable_origen
      AND t1.objectid_1 = tp_origen
      AND t2.objectid_1 = tp_destino;

    RETURN QUERY
    SELECT c1.objectid, SPLIT_PART(c1.fibras_res, '-', 2),
           filamento_destino, 0, c1.codigo, cod_tramo_destino, splitter,
           t1.objectid_1, t2.objectid_1, t1.codigo, t2.codigo
    FROM cable c1, telecom_premises t1, telecom_premises t2
    WHERE c1.objectid   = cable_origen
      AND t1.objectid_1 = tp_origen
      AND t2.objectid_1 = tp_destino;

END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- SECCIÓN 4: FUNCIÓN recorridoCable
-- ============================================================================
-- Motor principal de recorrido automático de red.
--
-- LÓGICA DEL BUCLE:
--   1. Dado el primer cable y la ciudad, busca su TP origen y destino
--   2. Busca si existe un cable cuyo origen = destino del cable actual
--   3. Si existe  → llama a fusion_cable_tp (tramo intermedio)
--   4. Si NO existe → llama a fusion_tp_tp (final del recorrido) y sale
--   5. El cable destino pasa a ser el nuevo cable origen → siguiente iteración
--
-- POR QUÉ SE FILTRA POR ciudad:
--   Distintos proyectos de red en ciudades diferentes podían tener cables
--   con los mismos códigos de origen/destino. El campo ciudad evita
--   que el recorrido salte entre redes distintas.
--
-- NOTA: Devuelve VOID e inserta directamente en tabla_fusion.
--   Para ver el resultado: SELECT * FROM tabla_fusion;
-- ============================================================================

CREATE OR REPLACE FUNCTION recorridoCable(
    cable_origen NUMERIC,
    city         TEXT
)
RETURNS VOID AS $$
DECLARE
    tp_origen     NUMERIC;
    tp_destino    NUMERIC;
    cable_destino NUMERIC;
BEGIN
    cable_destino := 0;  -- Valor inicial para entrar en el bucle

    WHILE cable_destino IS NOT NULL LOOP

        -- Buscar el TP origen del cable actual
        SELECT objectid_1 INTO tp_origen
        FROM telecom_premises
        WHERE codigo = (SELECT origen FROM cable WHERE objectid = cable_origen)
          AND ciudad = city;

        -- Buscar el siguiente cable (aquel cuyo origen = destino del actual)
        SELECT objectid INTO cable_destino
        FROM cable
        WHERE origen = (SELECT destino FROM cable WHERE objectid = cable_origen)
          AND ciudad = city
        LIMIT 1;

        -- Buscar el TP destino del cable actual
        SELECT objectid_1 INTO tp_destino
        FROM telecom_premises
        WHERE codigo = (SELECT destino FROM cable WHERE objectid = cable_origen)
          AND ciudad = city;

        IF cable_destino IS NULL THEN
            -- Final del recorrido: última fusión hacia TP con splitter
            INSERT INTO tabla_fusion
            SELECT * FROM fusion_tp_tp(cable_origen, tp_origen, tp_destino);
        ELSE
            -- Tramo intermedio: fusión cable → cable
            INSERT INTO tabla_fusion
            SELECT * FROM fusion_cable_tp(cable_origen, cable_destino, tp_origen, tp_destino);
        END IF;

        -- Avanzar al siguiente tramo
        cable_origen := cable_destino;

    END LOOP;
END;
$$ LANGUAGE plpgsql;


-- ============================================================================
-- SECCIÓN 5: EJEMPLOS DE USO
-- ============================================================================

-- Fusión manual tramo a tramo (útil para depuración o recorridos parciales)
INSERT INTO tabla_fusion SELECT * FROM fusion_cable_tp(763, 764, 110, 61);
SELECT * FROM fusion_cable_tp(764, 765, 61, 59);
SELECT * FROM fusion_cable_tp(765, 768, 59, 69);
SELECT * FROM fusion_tp_tp(768, 69, 111);    -- Último tramo

-- Recorrido inverso (verificación)
SELECT * FROM fusion_cable_tp(768, 767, 111, 69);

-- Recorrido automático completo desde el primer cable
SELECT recorridoCable(764, 'VEGAS DE ALMENARA');
SELECT recorridoCable(777, 'VEGAS DE ALMENARA');

-- Ver resultados
SELECT * FROM tabla_fusion;

-- Limpiar para nueva ejecución
DELETE FROM tabla_fusion;


-- ============================================================================
-- SECCIÓN 6: CONSULTAS DE NAVEGACIÓN
-- ============================================================================
-- Consultas auxiliares usadas durante el desarrollo para navegar la red
-- y construir la lógica de recorridoCable.
-- ============================================================================

-- Ver datos de un cable concreto
SELECT * FROM cable WHERE objectid = 763;

-- Ver datos de un TP concreto
SELECT * FROM telecom_premises WHERE objectid_1 = 111;

-- Buscar el TP destino de un cable dado
SELECT * FROM telecom_premises
WHERE codigo = (SELECT destino FROM cable WHERE objectid = 764)
  AND ciudad = 'VEGAS DE ALMENARA';

-- Buscar el cable siguiente en el recorrido
SELECT * FROM cable
WHERE origen = (SELECT destino FROM cable WHERE objectid = 764)
  AND ciudad = 'VEGAS DE ALMENARA'
LIMIT 1;

-- Buscar el TP origen de un cable dado
SELECT * FROM telecom_premises
WHERE codigo = (SELECT origen FROM cable WHERE objectid = 764)
  AND ciudad = 'VEGAS DE ALMENARA';

-- Comprobar si existe cable siguiente al final del tramo
SELECT objectid FROM cable
WHERE origen = (SELECT destino FROM cable WHERE objectid = 768)
  AND ciudad = 'VEGAS DE ALMENARA'
LIMIT 1;
