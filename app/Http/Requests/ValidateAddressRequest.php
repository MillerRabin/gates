<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateAddressRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'gate' => ['required', 'string'],
      'address' => ['required', 'regex:/^0x[a-fA-F0-9]{40}$/']
    ];
  }
}
