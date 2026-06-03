<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WalletClient
{
  public function createAddress(array $payload): array
  {
    return Http::post(
      config('services.wallet.url') . '/api/v1/createaddress',
      $payload
    )->throw()->json();
  }

  public function validateAddress(array $payload): array
  {
    return Http::post(
      config('services.wallet.url') . '/api/v1/validateaddress',
      $payload
    )->throw()->json();
  }

  public function createTransaction(array $payload): array
  {
    return Http::post(
      config('services.wallet.url') . '/api/v1/tx',
      $payload
    )->throw()->json();
  }
}
