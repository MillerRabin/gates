<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gate extends Model
{
  public function hotWallet()
  {
    return $this->hasOne(HotWallet::class);
  }
  
  protected $fillable = [
    'name',
    'rpc_url',
    'chain_id',
    'confirmations_required',
    'parent_gate_id',
    'asset_type',
    'token_contract',
  ];
}
