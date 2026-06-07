<?php

namespace App\Http\Controllers;

use App\DTOs\CreateAddressDTO;
use App\DTOs\ValidateAddressDTO;
use App\Http\Requests\CreateAddressRequest;
use App\Http\Requests\ValidateAddressRequest;
use App\Services\AddressService;

class AddressController extends Controller
{
  public function store(
    CreateAddressRequest $request,
    AddressService $service
  ) {
    $address = $service->createAddress(
      CreateAddressDTO::fromRequest($request)
    );

    return response()->json($address, 201);
  }
}
