<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndexedBlock extends Model
{
  protected $fillable = [
    'gate_id',
    'block_number',
    'block_hash',
    'parent_hash',
  ];
}
