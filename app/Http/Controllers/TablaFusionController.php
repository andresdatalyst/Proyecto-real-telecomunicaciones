<?php

namespace App\Http\Controllers;

use Exception;
use Hamcrest\Core\HasToString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\AssignOp\Concat;

class TablaFusionController extends Controller

{
    public $tabla_fusion;
    public $splitter;
    public $filamento_origen;
    public $filamento_destino;
    public $fibras_act1;
    public $fibras_act2;
    public $fibras_res1;
    public $fibras_res2;

    public function index()
    {

        return view('TablaFusion/index');
    }

    public function recorridoDisForm()
    {
        return view('TablaFusion/formFusionDistribucion');
    }

    //funcion fusiones 4 parámetros
    public function fusionCableTp($id_cable_origen, $id_cable_destino, $id_tp_origen, $id_tp_destino): void // Request $request
    {

        //Obtenemos los objetos 
        $cable_origen = DB::table('cable')->where('objectid', '=', $id_cable_origen)->first();
        $cable_destino = DB::table('cable')->where('objectid', '=', $id_cable_destino)->first();
        $tp_origen = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_origen)->first();
        $tp_destino = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_destino)->first();


        //con explode puedo convertir un string en un array
        $fibras_act1 = explode(',', $cable_origen->fibras_act);
        $fibras_res1 = explode(',', $cable_origen->fibras_res);
        $fibras_act2 = explode(',', $cable_destino->fibras_act);
        $fibras_res2 = explode(',', $cable_destino->fibras_res);


        //Recorremos un bucle hasta que no queden mas fibras por fusionar
        for ($i = 0; $i < count($fibras_act1); $i++) {

            $splitter = " ";

            //si estamos en la última posición del array significa que serán las fibras que se conecten a los divisores
            if ($i == count($fibras_act1) - 1) {
                $splitter = 'div1';
            }
            //Usamos un try catch cuando asignamos el filamento, ya que en algunas ocasiones devolverá null
            //y debemos tratarlo
            try {
                $filamento_origen = explode("-", $fibras_act1[$i])[0];
            } catch (Exception $e) {
                $filamento_origen = " ";
            }
            try {
                $filamento_destino = explode("-", $fibras_act2[$i])[0];
            } catch (Exception $e) {
                $filamento_destino = " ";
            }

            //Realizamos la primera fusion realizando un insert
            DB::table('tabla_fusion_laravel')
                ->insert([
                    'id_cable_origen' => $cable_origen->objectid,
                    'filamento_origen' => $filamento_origen,
                    'filamento_destino' => $filamento_destino,
                    'id_cable_destino' => $cable_destino->objectid,
                    'cod_tramo_origen' => $cable_origen->codigo,
                    'cod_tramo_destino' => $cable_destino->codigo,
                    'splitter' => $splitter,
                    'id_objeto_origen' => $tp_origen->objectid_1,
                    'id_objeto_destino' => $tp_destino->objectid_1,
                    'cod_objeto_origen' => $tp_origen->codigo,
                    'cod_objeto_destino' => $tp_destino->codigo,

                ]);

            try {
                $filamento_origen = explode("-", $fibras_act1[$i])[1];
            } catch (Exception $e) {
                $filamento_origen = " ";
            }
            try {
                $filamento_destino = explode("-", $fibras_act2[$i])[1];
            } catch (Exception $e) {
                $filamento_destino = " ";
            }

            if ($i == count($fibras_act1) - 1) {
                $splitter = 'div2';
            }

            DB::table('tabla_fusion_laravel')
                ->insert([
                    'id_cable_origen' => $cable_origen->objectid,
                    'filamento_origen' => $filamento_origen,
                    'filamento_destino' => $filamento_destino,
                    'id_cable_destino' => $cable_destino->objectid,
                    'cod_tramo_origen' => $cable_origen->codigo,
                    'cod_tramo_destino' => $cable_destino->codigo,
                    'splitter' => $splitter,
                    'id_objeto_origen' => $tp_origen->objectid_1,
                    'id_objeto_destino' => $tp_destino->objectid_1,
                    'cod_objeto_origen' => $tp_origen->codigo,
                    'cod_objeto_destino' => $tp_destino->codigo,

                ]);

            try {
                $filamento_origen = explode("-", $fibras_res1[$i])[0];
            } catch (Exception $e) {
                $filamento_origen = " ";
            }
            try {
                $filamento_destino = explode("-", $fibras_res2[$i])[0];
            } catch (Exception $e) {
                $filamento_destino = " ";
            }


            //reservas
            $splitter = " ";

            DB::table('tabla_fusion_laravel')
                ->insert([
                    'id_cable_origen' => $cable_origen->objectid,
                    'filamento_origen' => $filamento_origen,
                    'filamento_destino' => $filamento_destino,
                    'id_cable_destino' => $cable_destino->objectid,
                    'cod_tramo_origen' => $cable_origen->codigo,
                    'cod_tramo_destino' => $cable_destino->codigo,
                    'splitter' => $splitter,
                    'id_objeto_origen' => $tp_origen->objectid_1,
                    'id_objeto_destino' => $tp_destino->objectid_1,
                    'cod_objeto_origen' => $tp_origen->codigo,
                    'cod_objeto_destino' => $tp_destino->codigo,

                ]);

            try {
                $filamento_origen = explode("-", $fibras_res1[$i])[1];
            } catch (Exception $e) {
                $filamento_origen = " ";
            }
            try {
                $filamento_destino = explode("-", $fibras_res2[$i])[1];
            } catch (Exception $e) {
                $filamento_destino = " ";
            }

            DB::table('tabla_fusion_laravel')
                ->insert([
                    'id_cable_origen' => $cable_origen->objectid,
                    'filamento_origen' => $filamento_origen,
                    'filamento_destino' => $filamento_destino,
                    'id_cable_destino' => $cable_destino->objectid,
                    'cod_tramo_origen' => $cable_origen->codigo,
                    'cod_tramo_destino' => $cable_destino->codigo,
                    'splitter' => $splitter,
                    'id_objeto_origen' => $tp_origen->objectid_1,
                    'id_objeto_destino' => $tp_destino->objectid_1,
                    'cod_objeto_origen' => $tp_origen->codigo,
                    'cod_objeto_destino' => $tp_destino->codigo,

                ]);
        }

        
    }

    //Fusion que se realiza cuando es el último cable y termina en tp_destino
    public function fusionTpTp($id_cable_origen, $id_tp_origen, $id_tp_destino): void
    {

        //Obtenemos los objetos
        $cable_origen = DB::table('cable')->where('objectid', '=', $id_cable_origen)->first();
        $tp_origen = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_origen)->first();
        $tp_destino = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_destino)->first();

        //obtenemos el campo fibras_act y fibras_rec como un array
        $fibras_act1 = explode('-', $cable_origen->fibras_act);
        $fibras_res1 = explode('-', $cable_origen->fibras_res);

        $splitter = "div1";

        //Realizamos las fusiones como inserts
        DB::table('tabla_fusion_laravel')
            ->insert([
                'id_cable_origen' => $cable_origen->objectid,
                'filamento_origen' => $fibras_act1[0],
                'filamento_destino' => null,
                'id_cable_destino' => null,
                'cod_tramo_origen' => $cable_origen->codigo,
                'cod_tramo_destino' => null,
                'splitter' => $splitter,
                'id_objeto_origen' => $tp_origen->objectid_1,
                'id_objeto_destino' => $tp_destino->objectid_1,
                'cod_objeto_origen' => $tp_origen->codigo,
                'cod_objeto_destino' => $tp_destino->codigo,

            ]);

        $splitter = "div2";

        DB::table('tabla_fusion_laravel')
            ->insert([
                'id_cable_origen' => $cable_origen->objectid,
                'filamento_origen' => $fibras_act1[1],
                'filamento_destino' => null,
                'id_cable_destino' => null,
                'cod_tramo_origen' => $cable_origen->codigo,
                'cod_tramo_destino' => null,
                'splitter' => $splitter,
                'id_objeto_origen' => $tp_origen->objectid_1,
                'id_objeto_destino' => $tp_destino->objectid_1,
                'cod_objeto_origen' => $tp_origen->codigo,
                'cod_objeto_destino' => $tp_destino->codigo,

            ]);

        $splitter = " ";

        DB::table('tabla_fusion_laravel')
            ->insert([
                'id_cable_origen' => $cable_origen->objectid,
                'filamento_origen' => $fibras_res1[0],
                'filamento_destino' => null,
                'id_cable_destino' => null,
                'cod_tramo_origen' => $cable_origen->codigo,
                'cod_tramo_destino' => null,
                'splitter' => $splitter,
                'id_objeto_origen' => $tp_origen->objectid_1,
                'id_objeto_destino' => $tp_destino->objectid_1,
                'cod_objeto_origen' => $tp_origen->codigo,
                'cod_objeto_destino' => $tp_destino->codigo,

            ]);

        DB::table('tabla_fusion_laravel')
            ->insert([
                'id_cable_origen' => $cable_origen->objectid,
                'filamento_origen' => $fibras_res1[1],
                'filamento_destino' => null,
                'id_cable_destino' => null,
                'cod_tramo_origen' => $cable_origen->codigo,
                'cod_tramo_destino' => null,
                'splitter' => $splitter,
                'id_objeto_origen' => $tp_origen->objectid_1,
                'id_objeto_destino' => $tp_destino->objectid_1,
                'cod_objeto_origen' => $tp_origen->codigo,
                'cod_objeto_destino' => $tp_destino->codigo,

            ]);

        //        return view('TablaFusion/list');

    }


    //Funcion que recorre los cables de distribución, partiendo desde un formulario
    public function recorridoCableDistribucion(Request $request)
    {

        //obtenemos los valores del formulario
        $id_cable_origen = $request->input('id_cable_origen');
        $city = $request->input('city');
        //obtenemos los objetos
        $cable_origen = DB::table('cable')->where('objectid', '=', $id_cable_origen)->first();    

        $cable_destino = 0;//le asignamos un cable a cable_destino para que entre en el bucle

        //mientras no sea null
        while (!is_null($cable_destino)) {

            $tp_origen = DB::table('telecom_premises')
                ->where('codigo', '=', $cable_origen->origen)
                ->where('ciudad', '=', $city)->first();
            
            $cable_destino = DB::table('cable')
                ->where('origen', '=', $cable_origen->destino)
                ->where('ciudad', '=', $city)->first();
            $tp_destino = DB::table('telecom_premises')
                ->where('codigo', '=', $cable_origen->destino)
                ->where('ciudad', '=', $city)->first();
            
            //aqui llamamos a las funciones creadas previamente
            if (is_null($cable_destino)) {
                self::fusionTpTp($cable_origen->objectid, $tp_origen->objectid_1, $tp_destino->objectid_1);
                break; //como esta será la última funcion lo rompemos aquí
            } else {
                self::fusionCableTp($cable_origen->objectid, $cable_destino->objectid, $tp_origen->objectid_1, $tp_destino->objectid_1);
            }

            $cable_origen = DB::table('cable')->where('objectid', '=', $cable_destino->objectid)->first();
        }

        return view('TablaFusion/result');
    }

    //Get formulario fusionAlimentacion
    public function formFusionAlimentacion(){
        return view('TablaFusion/formFusionAlimentacion');
    }

    public function fusionAlimentacion($id_cable_origen,$id_cable_destino, $id_tp_origen, $id_tp_destino):void
    {
    
        $cable_origen = DB::table('cable')->where('objectid', '=', $id_cable_origen)->first();
        $cable_destino = DB::table('cable')->where('objectid', '=', $id_cable_destino)->first();

        $tp_origen = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_origen)->first();
        $tp_destino = DB::table('telecom_premises')->where('objectid_1', '=', $id_tp_destino)->first();
        
        $fibras_act1 = explode('-', $cable_origen->fibras_totales);
        $fibras_act2 = explode('-', $cable_origen->fi_x);
       
        $filamento_origen = $fibras_act1[0];
        $primer_filamento=(int)$fibras_act1[0];
        //ME DA ERROR AQUI NO ENTIENDO PORQUE
        $ultimo_filamento= (int)$fibras_act1[1];
        $filamento_destino = $fibras_act2[0];
       // dd($primer_filamento);
        for($i=$primer_filamento;$i<=$ultimo_filamento;$i++){
            
            DB::table('tabla_fusion_alimentacion')
                ->insert([
                    'id_cable_origen' => $cable_destino->objectid,
                    'filamento_origen' => $filamento_destino,
                    'filamento_destino' => $filamento_origen,
                    'id_cable_destino' => $cable_origen->objectid,
                    'cod_tramo_origen' => $cable_destino->codigo,
                    'cod_tramo_destino' => $cable_origen->codigo,
                    'splitter' => null,
                    'id_objeto_origen' => $tp_destino->objectid_1,
                    'id_objeto_destino' => $tp_origen->objectid_1,
                    'cod_objeto_origen' => $tp_destino->codigo,
                    'cod_objeto_destino' => $tp_origen->codigo,

                ]);
                $filamento_origen++;
                $filamento_destino++;
        }

        

    }

    public function recorridoAlimentacion(Request $request){

        //obtenemos los valores del formulario
        $id_cable_origen = $request->input('id_cable_origen');
        $city = $request->input('city');
        //obtenemos los objetos
        $cable_origen = DB::table('cable')->where('objectid', '=', $id_cable_origen)->first();    

        $cable_destino = 0;//le asignamos un cable a cable_destino para que entre en el bucle

        $cable_destino = DB::table('cable')
                ->where('origen', '=', $cable_origen->destino)
                ->where('ciudad', '=', $city)->first();

        //mientras no sea null
        while ($cable_destino->origen !='TT01') {

            $tp_origen = DB::table('telecom_premises')
                ->where('codigo', '=', $cable_origen->origen)
                ->where('ciudad', '=', $city)->first();
            
            $cable_destino = DB::table('cable')
                ->where('origen', '=', $cable_origen->destino)
                ->where('ciudad', '=', $city)->first();
            $tp_destino = DB::table('telecom_premises')
                ->where('codigo', '=', $cable_origen->destino)
                ->where('ciudad', '=', $city)->first();
            
            self::fusionAlimentacion($cable_origen->objectid, $cable_destino->objectid, $tp_origen->objectid_1, $tp_destino->objectid_1);

            $cable_origen = DB::table('cable')->where('objectid', '=', $cable_destino->objectid)->first();
        }

        return view('TablaFusion/result');
    }
}
