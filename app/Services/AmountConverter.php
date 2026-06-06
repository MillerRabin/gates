<?php

namespace App\Services;

class AmountConverter
{
  public function toBaseUnits(
    string $amount,
    int $decimals
  ): string {
    return bcmul(
      $amount,
      bcpow('10', (string) $decimals),
      0
    );
  }
}
