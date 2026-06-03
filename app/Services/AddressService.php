<?php

namespace App\Services;

use App\DTOs\CreateAddressDTO;
use App\Models\Address;
use App\Models\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AddressService
{
  public function createAddress(CreateAddressDTO $dto): Address
  {
    $gate = Gate::where('name', $dto->gate)->firstOrFail();

    $response = Http::post(
      config('services.wallet.url') . '/api/v1/createaddress',
      [
        'gate' => $gate->name,
        'account' => $dto->account,
        'change' => $dto->change,
        'address_index' => $dto->address_index,
      ]
    );

    $response->throw();

    $walletData = $response->json();

    if (! isset($walletData['address'])) {
      throw new \RuntimeException(
        'Wallet service did not return address'
      );
    }

    return DB::transaction(function () use (
      $gate,
      $dto,
      $walletData
    ) {
      return Address::create([
        'gate_id' => $gate->id,
        'account' => $dto->account,
        'change' => $dto->change,
        'address_index' => $dto->address_index,
        'address' => $walletData['address'],
      ]);
    });
  }
}
