<?php

namespace App\Services;

use App\DTOs\CreateWithdrawalDTO;

class WithdrawalService
{
  public function __construct(
    private AddressService $addressService,
    private WalletClient $walletClient,
  ) {}

  public function createWithdrawal(
    CreateWithdrawalDTO $dto
  ): array {
    // TODO:
    // validateAddress
    // convert amount
    // create withdrawal
    // sign tx
    // broadcast

    return [];
  }
}
