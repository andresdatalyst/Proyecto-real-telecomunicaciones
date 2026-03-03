# Decisiones técnicas y contexto del proyecto

## El problema de las fibras en texto

El sistema GIS que usaba la empresa almacenaba los atributos de red en campos de texto para mantener flexibilidad. El campo `FIBRAS_ACT` guardaba todos los pares de fibras activas separados por comas:

```
FIBRAS_ACT = '1-2,3-4,5-6'
```

Cada elemento `A-B` es un par de filamentos. La posición dentro del string indica a qué TP del recorrido corresponde esa fusión: el elemento en posición 1 fusiona con el primer TP intermedio, el de posición 2 con el segundo, y así sucesivamente.

El modelo no era modificable porque estaba en producción y otros sistemas dependían de él.

---

## La regla del splitter

En redes FTTH, el splitter divide la señal óptica hacia los usuarios finales. El **último par de fibras** de cada cable es el que llega al splitter de la CTO (Central Terminal Óptica), y recibe un tratamiento especial:

- `div1` → primer filamento del par final
- `div2` → segundo filamento del par final

El resto de pares son fusiones directas cable-cable sin destino a splitter.

En la versión original esto se implementa con un `IF` dentro del loop:

```sql
IF counter = array_length(fibras_act1, 1) THEN
    splitter := 'div1';
END IF;
```

En la versión mejorada pasa a ser un `CASE` en la propia query:

```sql
CASE WHEN f1.orden = v_max_orden AND f1.tipo = 'activa' THEN 'div1' ELSE NULL END
```

---

## Por qué hay dos funciones de fusión

El recorrido tiene dos situaciones distintas:

**Tramo intermedio** — `fusion_cable_tp` / `fusionar_cables`: hay dos cables. Las fibras del cable A se fusionan con las fibras del cable B en el mismo orden de posición.

**Tramo final** — `fusion_tp_tp` / `fusionar_cable_tp_final`: el último cable llega a un TP sin continuación. Las fibras van al splitter sin cable destino.

---

## Cómo funciona el recorrido automático

```
[CTO-001] --CBL-A-- [CTO-002] --CBL-B-- [CTO-003] --CBL-C-- [CTO-004] --CBL-D-- [CTO-005]
```

La función de recorrido navega esa cadena buscando siempre el cable cuyo `origen_tp` coincide con el `destino_tp` del cable actual, dentro de la misma ciudad. Cuando no encuentra siguiente cable, sabe que está al final y ejecuta la función de cierre.

El campo `ciudad` es necesario porque distintos proyectos de red en ciudades diferentes podían compartir los mismos códigos de origen/destino.

---

## Comparativa técnica entre versiones

| Aspecto | Versión original | Versión mejorada |
|---|---|---|
| Almacenamiento de fibras | Campo texto `'1-2,3-4'` | Una fila por par (normalizado) |
| Parsing | `string_to_array` + `split_part` | JOIN por columna `orden` |
| Iteración | `FOR counter IN 1..array_length` | Set-based con `UNION ALL` |
| Regla del splitter | `IF` dentro del loop | `CASE` en la query |
| Queries por iteración | 4 `RETURN QUERY` separadas | 1 INSERT con `UNION ALL` |
| Navegación de red | 3 `SELECT INTO` por iteración | 1 query con múltiples JOINs |
| FROM syntax | `FROM tabla1, tabla2` (implicit join) | `JOIN ... ON` explícito |
| Datos de prueba | IDs hardcodeados de producción | Dataset reproducible incluido |

---

## Limitaciones conocidas de la versión original

**Rendimiento en redes grandes**: hay múltiples `SELECT INTO` secuenciales dentro del loop. En redes con cientos de cables esto escala mal. La versión mejorada consolida esas consultas.

**Acoplamiento al formato de texto**: si una fibra necesita un atributo nuevo, hay que modificar el formato del string y todo el código de parsing.

**`RETURNS VOID`**: `recorridoCable` inserta directamente en tabla y no retorna resultado. Para ver qué generó hay que consultar `tabla_fusion` aparte.

Estas limitaciones eran aceptables dado el contexto: el modelo ya estaba cerrado, la red tenía un tamaño acotado, y el objetivo era automatizar un proceso que antes era 100% manual.
