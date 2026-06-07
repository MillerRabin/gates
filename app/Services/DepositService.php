<?php

namespace App\Services;

use App\Models\Deposit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepositService
{
  public function getDeposits(
    ?string $assetGate = null,
    int $perPage = 20
  ): LengthAwarePaginator {

    return Deposit::query()
      ->with([
        'gate',
        'address',
      ])
      ->when(
        $assetGate,
        fn($query) => $query->whereHas(
          'gate',
          fn($gateQuery) => $gateQuery->where(
            'name',
            $assetGate
          )
        )
      )
      ->latest('id')
      ->paginate($perPage);
  }

  public function getDeposit(
    int $id
  ): Deposit {

    return Deposit::query()
      ->with([
        'gate',
        'address',
      ])
      ->findOrFail($id);
  }
}
