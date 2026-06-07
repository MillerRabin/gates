<?php

namespace App\Http\Controllers;

use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepositController extends Controller
{
  public function __construct(
    private DepositService $depositService,
  ) {}

  public function index(
    Request $request
  ): JsonResponse {

    $deposits = $this->depositService
      ->getDeposits(
        assetGate: $request->query(
          'asset_gate'
        ),
        perPage: $request->integer(
          'per_page',
          20
        ),
      );

    return response()->json(
      $deposits
    );
  }

  public function show(
    int $deposit
  ): JsonResponse {

    return response()->json(
      $this->depositService->getDeposit(
        $deposit
      )
    );
  }
}
