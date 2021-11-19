<?php

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
    return 'API de Integração com Documentos Fiscais';
});

Route::get('nfe/imprimir/{idnfe}', 'NFe\NFeController@imprime');
Route::get('nfe/danfe/{pdfFile}', function ($pdfFile) {
    return response()->file(\storage_path('nfe' . DIRECTORY_SEPARATOR . 'DANFE' . DIRECTORY_SEPARATOR . $pdfFile));
});
