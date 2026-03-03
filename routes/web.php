<?php

use App\Http\Controllers\MapController;
use App\Http\Controllers\TablaFusionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/TablaFusion/index',[TablaFusionController::class,'index']);
Route::get('/TablaFusion/distribucionForm',[TablaFusionController::class,'recorridoDisForm']);
Route::get('/TablaFusion/alimentacionForm',[TablaFusionController::class,'formFusionAlimentacion']);

Route::post('/fusionDistribucion)',[TablaFusionController::class,'recorridoCableDistribucion'])
->name('fusionDistribucion');
Route::post('/fusionAlimentacion',[TablaFusionController::class,'recorridoAlimentacion'])
->name('fusionAlimentacion');


