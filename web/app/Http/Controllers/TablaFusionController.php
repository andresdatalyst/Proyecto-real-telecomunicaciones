<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TablaFusionController
 *
 * Motor de fusiones FTTH portado a Laravel.
 * Gestiona el recorrido automático de cables y la generación
 * de la tabla de fusiones para redes de fibra óptica.
 *
 * FLUJO PRINCIPAL:
 *   1. El usuario introduce el ID del cable inicial y la ciudad
 *   2. recorridoCableDistribucion() navega la cadena de cables
 *   3. Por cada par de cables llama a fusionCableTp()
 *   4. Al llegar al final llama a fusionTpTp()
 *   5. Los resultados se insertan en tabla_fusion_laravel
 *   6. Se devuelven a la vista para mostrarlos en pantalla
 *
 * NOTA SOBRE EL MODELO DE DATOS:
 *   Las fibras están almacenadas como texto: '1-2,3-4,5-6'
 *   Se usa explode() como equivalente PHP de string_to_array() de PostgreSQL.
 *   Ver /sql/improved/ para el diseño normalizado alternativo.
 */
class TablaFusionController extends Controller
{
    // =========================================================================
    // VISTAS
    // =========================================================================

    public function index()
    {
        return view('TablaFusion.index');
    }

    public function recorridoDisForm()
    {
        return view('TablaFusion.formFusionDistribucion');
    }

    public function formFusionAlimentacion()
    {
        return view('TablaFusion.formFusionAlimentacion');
    }

    // =========================================================================
    // LÓGICA DE FUSIÓN — DISTRIBUCIÓN
    // =========================================================================

    /**
     * Genera las fusiones entre dos cables consecutivos.
     *
     * Recorre el array de fibras activas y de reserva posición a posición.
     * La última posición corresponde a las fibras que van al splitter del TP
     * (marcadas como div1 y div2).
     *
     * @param int $id_cable_origen   objectid del cable de origen
     * @param int $id_cable_destino  objectid del cable de destino
     * @param int $id_tp_origen      objectid_1 del TP de origen
     * @param int $id_tp_destino     objectid_1 del TP de destino
     */
    public function fusionCableTp(
        int $id_cable_origen,
        int $id_cable_destino,
        int $id_tp_origen,
        int $id_tp_destino
    ): void {
        $cable_origen  = DB::table('cable')->where('objectid', $id_cable_origen)->first();
        $cable_destino = DB::table('cable')->where('objectid', $id_cable_destino)->first();
        $tp_origen     = DB::table('telecom_premises')->where('objectid_1', $id_tp_origen)->first();
        $tp_destino    = DB::table('telecom_premises')->where('objectid_1', $id_tp_destino)->first();

        // Convertir texto en arrays: '1-2,3-4,5-6' → ['1-2', '3-4', '5-6']
        $fibras_act1 = explode(',', $cable_origen->fibras_act);
        $fibras_res1 = explode(',', $cable_origen->fibras_res);
        $fibras_act2 = explode(',', $cable_destino->fibras_act);
        $fibras_res2 = explode(',', $cable_destino->fibras_res);

        $total = count($fibras_act1);

        for ($i = 0; $i < $total; $i++) {

            $es_ultimo = ($i === $total - 1);

            $par_act1 = explode('-', $fibras_act1[$i]);
            $par_act2 = explode('-', $fibras_act2[$i]);
            $par_res1 = explode('-', $fibras_res1[$i]);
            $par_res2 = explode('-', $fibras_res2[$i]);

            $base = [
                'id_cable_origen'   => $cable_origen->objectid,
                'id_cable_destino'  => $cable_destino->objectid,
                'cod_tramo_origen'  => $cable_origen->codigo,
                'cod_tramo_destino' => $cable_destino->codigo,
                'id_objeto_origen'  => $tp_origen->objectid_1,
                'id_objeto_destino' => $tp_destino->objectid_1,
                'cod_objeto_origen' => $tp_origen->codigo,
                'cod_objeto_destino'=> $tp_destino->codigo,
            ];

            // Activas: primer filamento
            DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
                'filamento_origen'  => $par_act1[0] ?? null,
                'filamento_destino' => $par_act2[0] ?? null,
                'splitter'          => $es_ultimo ? 'div1' : null,
            ]));

            // Activas: segundo filamento
            DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
                'filamento_origen'  => $par_act1[1] ?? null,
                'filamento_destino' => $par_act2[1] ?? null,
                'splitter'          => $es_ultimo ? 'div2' : null,
            ]));

            // Reservas: primer filamento
            DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
                'filamento_origen'  => $par_res1[0] ?? null,
                'filamento_destino' => $par_res2[0] ?? null,
                'splitter'          => null,
            ]));

            // Reservas: segundo filamento
            DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
                'filamento_origen'  => $par_res1[1] ?? null,
                'filamento_destino' => $par_res2[1] ?? null,
                'splitter'          => null,
            ]));
        }
    }

    /**
     * Caso final del recorrido: último cable llega a un TP sin continuación.
     * Todos los filamentos activos van al splitter (div1 / div2).
     */
    public function fusionTpTp(
        int $id_cable_origen,
        int $id_tp_origen,
        int $id_tp_destino
    ): void {
        $cable_origen = DB::table('cable')->where('objectid', $id_cable_origen)->first();
        $tp_origen    = DB::table('telecom_premises')->where('objectid_1', $id_tp_origen)->first();
        $tp_destino   = DB::table('telecom_premises')->where('objectid_1', $id_tp_destino)->first();

        $fibras_act = explode('-', $cable_origen->fibras_act);
        $fibras_res = explode('-', $cable_origen->fibras_res);

        $base = [
            'id_cable_origen'    => $cable_origen->objectid,
            'id_cable_destino'   => null,
            'cod_tramo_origen'   => $cable_origen->codigo,
            'cod_tramo_destino'  => null,
            'filamento_destino'  => null,
            'id_objeto_origen'   => $tp_origen->objectid_1,
            'id_objeto_destino'  => $tp_destino->objectid_1,
            'cod_objeto_origen'  => $tp_origen->codigo,
            'cod_objeto_destino' => $tp_destino->codigo,
        ];

        DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
            'filamento_origen' => $fibras_act[0] ?? null,
            'splitter'         => 'div1',
        ]));

        DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
            'filamento_origen' => $fibras_act[1] ?? null,
            'splitter'         => 'div2',
        ]));

        DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
            'filamento_origen' => $fibras_res[0] ?? null,
            'splitter'         => null,
        ]));

        DB::table('tabla_fusion_laravel')->insert(array_merge($base, [
            'filamento_origen' => $fibras_res[1] ?? null,
            'splitter'         => null,
        ]));
    }

    /**
     * Motor de recorrido automático de red de distribución.
     * Navega desde el primer cable hasta el final generando todas las fusiones.
     */
    public function recorridoCableDistribucion(Request $request)
    {
        $request->validate([
            'id_cable_origen' => 'required|integer',
            'city'            => 'required|string',
        ]);

        $id_cable_origen = (int) $request->input('id_cable_origen');
        $city            = $request->input('city');

        // Limpiar fusiones anteriores del mismo recorrido
        DB::table('tabla_fusion_laravel')->delete();

        $cable_origen  = DB::table('cable')->where('objectid', $id_cable_origen)->first();
        $cable_destino = true;

        while (!is_null($cable_destino)) {

            $tp_origen = DB::table('telecom_premises')
                ->where('codigo', $cable_origen->origen)
                ->where('ciudad', $city)
                ->first();

            $cable_destino = DB::table('cable')
                ->where('origen', $cable_origen->destino)
                ->where('ciudad', $city)
                ->first();

            $tp_destino = DB::table('telecom_premises')
                ->where('codigo', $cable_origen->destino)
                ->where('ciudad', $city)
                ->first();

            if (is_null($cable_destino)) {
                $this->fusionTpTp(
                    $cable_origen->objectid,
                    $tp_origen->objectid_1,
                    $tp_destino->objectid_1
                );
                break;
            }

            $this->fusionCableTp(
                $cable_origen->objectid,
                $cable_destino->objectid,
                $tp_origen->objectid_1,
                $tp_destino->objectid_1
            );

            $cable_origen = DB::table('cable')
                ->where('objectid', $cable_destino->objectid)
                ->first();
        }

        // Pasar los resultados a la vista
        $fusiones = DB::table('tabla_fusion_laravel')->get();

        return view('TablaFusion.result', [
            'fusiones' => $fusiones,
            'total'    => $fusiones->count(),
        ]);
    }

    // =========================================================================
    // LÓGICA DE FUSIÓN — ALIMENTACIÓN
    // =========================================================================

    public function fusionAlimentacion(
        int $id_cable_origen,
        int $id_cable_destino,
        int $id_tp_origen,
        int $id_tp_destino
    ): void {
        $cable_origen  = DB::table('cable')->where('objectid', $id_cable_origen)->first();
        $cable_destino = DB::table('cable')->where('objectid', $id_cable_destino)->first();
        $tp_origen     = DB::table('telecom_premises')->where('objectid_1', $id_tp_origen)->first();
        $tp_destino    = DB::table('telecom_premises')->where('objectid_1', $id_tp_destino)->first();

        $fibras_totales = explode('-', $cable_origen->fibras_totales);
        $fi_x           = explode('-', $cable_origen->fi_x);

        $primer_filamento  = (int) ($fibras_totales[0] ?? 0);
        $ultimo_filamento  = (int) ($fibras_totales[1] ?? 0);
        $filamento_destino = (int) ($fi_x[0] ?? 0);
        $filamento_origen  = $primer_filamento;

        for ($i = $primer_filamento; $i <= $ultimo_filamento; $i++) {
            DB::table('tabla_fusion_alimentacion')->insert([
                'id_cable_origen'    => $cable_destino->objectid,
                'filamento_origen'   => $filamento_destino,
                'filamento_destino'  => $filamento_origen,
                'id_cable_destino'   => $cable_origen->objectid,
                'cod_tramo_origen'   => $cable_destino->codigo,
                'cod_tramo_destino'  => $cable_origen->codigo,
                'splitter'           => null,
                'id_objeto_origen'   => $tp_destino->objectid_1,
                'id_objeto_destino'  => $tp_origen->objectid_1,
                'cod_objeto_origen'  => $tp_destino->codigo,
                'cod_objeto_destino' => $tp_origen->codigo,
            ]);
            $filamento_origen++;
            $filamento_destino++;
        }
    }

    public function recorridoAlimentacion(Request $request)
    {
        $request->validate([
            'id_cable_origen' => 'required|integer',
            'city'            => 'required|string',
        ]);

        $id_cable_origen = (int) $request->input('id_cable_origen');
        $city            = $request->input('city');

        DB::table('tabla_fusion_alimentacion')->delete();

        $cable_origen  = DB::table('cable')->where('objectid', $id_cable_origen)->first();
        $cable_destino = DB::table('cable')
            ->where('origen', $cable_origen->destino)
            ->where('ciudad', $city)
            ->first();

        while (!is_null($cable_destino) && $cable_destino->origen !== 'TT01') {

            $tp_origen = DB::table('telecom_premises')
                ->where('codigo', $cable_origen->origen)
                ->where('ciudad', $city)
                ->first();

            $cable_destino = DB::table('cable')
                ->where('origen', $cable_origen->destino)
                ->where('ciudad', $city)
                ->first();

            $tp_destino = DB::table('telecom_premises')
                ->where('codigo', $cable_origen->destino)
                ->where('ciudad', $city)
                ->first();

            $this->fusionAlimentacion(
                $cable_origen->objectid,
                $cable_destino->objectid,
                $tp_origen->objectid_1,
                $tp_destino->objectid_1
            );

            $cable_origen = DB::table('cable')
                ->where('objectid', $cable_destino->objectid)
                ->first();
        }

        $fusiones = DB::table('tabla_fusion_alimentacion')->get();

        return view('TablaFusion.result', [
            'fusiones' => $fusiones,
            'total'    => $fusiones->count(),
        ]);
    }
}
