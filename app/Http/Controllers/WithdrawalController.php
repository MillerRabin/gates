<?php

namespace App\Http\Controllers;

use App\DTOs\CreateWithdrawalDTO;
use App\Http\Requests\CreateWithdrawalRequest;
use App\Services\WithdrawalService;

class WithdrawalController extends Controller
{
  public function store(
    CreateWithdrawalRequest $request,
    WithdrawalService $service
  ) {
    $withdrawal = $service->createWithdrawal(
      CreateWithdrawalDTO::fromRequest($request)
    );

    return response()->json($withdrawal, 201);
  }
}
