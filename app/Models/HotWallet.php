<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotWallet extends Model
{
  protected $fillable = [
    'gate_id',
    'account',
    'change',
    'address_index',
    'address',
  ];

  public function gate()
  {
    return $this->belongsTo(Gate::class);
  }
}
