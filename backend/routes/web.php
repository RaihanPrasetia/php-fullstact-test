<?php

use App\Http\Controllers\MyClientController;
use Illuminate\Support\Facades\Route;

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::prefix('api')->group(function () {
    Route::resource('client', MyClientController::class);
});
