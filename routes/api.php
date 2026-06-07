<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
  Route::post('/addresses', [AddressController::class, 'store']);
  Route::post('/withdrawals', [WithdrawalController::class, 'store']);
  Route::get('/withdrawals', [WithdrawalController::class, 'index']);
  Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'show']);
  Route::get('/deposits', [DepositController::class, 'index']);
  Route::get('/deposits/{deposit}', [DepositController::class, 'show']);
});
