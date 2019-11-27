<?php

use App\Services\UtilService as Utils;

Route::middleware('cors')->get('/empresa-local', function () {
    return Utils::getEmpresaLocal();
});