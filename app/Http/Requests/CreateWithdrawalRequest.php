<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWithdrawalRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'asset_gate' => ['required', 'string'],
      'to_address' => ['required', 'string'],
      'amount' => ['required', 'string'],
    ];
  }
}
