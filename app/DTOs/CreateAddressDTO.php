<?php

namespace App\DTOs;

use App\Http\Requests\CreateAddressRequest;

class CreateAddressDTO
{
  public function __construct(
    public string $gate,
    public int $account,
    public int $change,
    public int $address_index,
  ) {}

  public static function fromRequest(CreateAddressRequest $request): self
  {
    return new self(
      gate: $request->gate,
      account: $request->account,
      change: $request->change,
      address_index: $request->address_index,
    );
  }
}
