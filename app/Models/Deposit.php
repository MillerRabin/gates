<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
  protected $fillable = [
    'gate_id',
    'address_id',
    'tx_hash',
    'log_index',
    'block_number',
    'block_hash',
    'amount',
  ];

  public function gate(): BelongsTo
  {
    return $this->belongsTo(
      Gate::class
    );
  }

  public function address(): BelongsTo
  {
    return $this->belongsTo(
      Address::class
    );
  }
}
