<?php
// Rotas referente a configuracao do módulo que será servida para o front-end
Route::middleware(['cors'])->group(function () {
    Route::get('nfe/config', 'NFe\NFeConfigController@index');
    Route::post('nfe/config', 'NFe\NFeConfigController@store');
    Route::put('nfe/config/{config}', 'NFe\NFeConfigController@update');
});
