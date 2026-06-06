<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
