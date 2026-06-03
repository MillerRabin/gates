<?php

namespace App\DTOs;

use App\Http\Requests\CreateWithdrawalRequest;

class CreateWithdrawalDTO
{
  public function __construct(
    public string $assetGate,
    public string $toAddress,
    public string $amount,
  ) {}

  public static function fromRequest(
    CreateWithdrawalRequest $request
  ): self {
    return new self(
      assetGate: $request->asset_gate,
      toAddress: $request->to_address,
      amount: $request->amount,
    );
  }
}
