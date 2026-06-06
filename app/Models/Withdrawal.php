<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
  protected $fillable = [
    'gate_id',
    'to_address',
    'amount',
    'amount_base_units',
    'signed_tx',
    'tx_hash',
    'status',
  ];

  protected $casts = [
    'status' => WithdrawalStatus::class,
  ];

  public function gate()
  {
    return $this->belongsTo(Gate::class);
  }
}
