<?php

namespace App\DTOs;

use App\Http\Requests\ValidateAddressRequest;

class ValidateAddressDTO
{
  public function __construct(
    public string $gate,
    public string $address,
  ) {}

  public static function fromRequest(
    ValidateAddressRequest $request
  ): self {
    return new self(
      gate: $request->gate,
      address: $request->address,
    );
  }
}
