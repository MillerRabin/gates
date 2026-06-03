<?php

namespace App\Services;

use App\DTOs\CreateAddressDTO;
use App\DTOs\ValidateAddressDTO;
use App\Models\Address;
use App\Models\Gate;
use Illuminate\Support\Facades\DB;

class AddressService
{
  public function __construct(
    private WalletClient $walletClient
  ) {}

  public function createAddress(CreateAddressDTO $dto): Address
  {
    $gate = Gate::where('name', $dto->gate)->firstOrFail();

    $walletData = $this->walletClient->createAddress([
      'gate' => $gate->name,
      'account' => $dto->account,
      'change' => $dto->change,
      'address_index' => $dto->address_index,
    ]);

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

  public function validateAddress(
    ValidateAddressDTO $dto
  ): array {
    return $this->walletClient->validateAddress([
      'gate' => $dto->gate,
      'address' => $dto->address,
    ]);
  }
}
