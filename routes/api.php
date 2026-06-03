<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  Route::post('/addresses', [AddressController::class, 'store']);
  Route::post('/validateaddress', [AddressController::class, 'validateAddress']);
  Route::post('/withdrawals',[WithdrawalController::class, 'store']);
});
