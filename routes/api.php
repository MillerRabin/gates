<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  Route::post('/addresses', [AddressController::class, 'store']);
});
