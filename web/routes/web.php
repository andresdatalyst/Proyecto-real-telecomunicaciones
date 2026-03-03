<?php

use App\Http\Controllers\TablaFusionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Motor de Fusiones FTTH
|--------------------------------------------------------------------------
*/

// Página de inicio
Route::get('/', [TablaFusionController::class, 'index'])
    ->name('home');

// Formularios
Route::get('/fusion/distribucion', [TablaFusionController::class, 'recorridoDisForm'])
    ->name('fusion.distribucion.form');

Route::get('/fusion/alimentacion', [TablaFusionController::class, 'formFusionAlimentacion'])
    ->name('fusion.alimentacion.form');

// Procesamiento — FIX: ruta anterior tenía ')' dentro de la URL (bug)
Route::post('/fusion/distribucion', [TablaFusionController::class, 'recorridoCableDistribucion'])
    ->name('fusionDistribucion');

Route::post('/fusion/alimentacion', [TablaFusionController::class, 'recorridoAlimentacion'])
    ->name('fusionAlimentacion');
