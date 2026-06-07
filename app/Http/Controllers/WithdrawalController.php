<?php

namespace App\Http\Controllers;

use App\DTOs\CreateWithdrawalDTO;
use App\Http\Requests\CreateWithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

  public function index(Request $request, WithdrawalService $service): JsonResponse
  {
    return response()->json(
      $service->getWithdrawals(
        assetGate: $request->query('asset_gate'),
        perPage: $request->integer('per_page', 20),
      )
    );
  }

  public function show(int $withdrawal, WithdrawalService $service): JsonResponse
  {
    return response()->json(
      $service->getWithdrawal(
        $withdrawal
      )
    );
  }
}
