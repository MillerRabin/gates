<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAddressRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'gate' => ['required', 'string'],
      'account' => ['required', 'integer', 'min:0'],
      'change' => ['required', 'integer', 'min:0'],
      'address_index' => ['required', 'integer', 'min:0'],
    ];
  }
}