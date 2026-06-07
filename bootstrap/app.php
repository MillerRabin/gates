<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Exceptions\RpcException;
use App\Exceptions\InsufficientFundsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
      $exceptions->render(
        function (
          InsufficientFundsException $e,
          $request
        ) {
          return response()->json([
            'message' => $e->getMessage(),
            'error_code' => 'INSUFFICIENT_FUNDS',
          ], 422);
        }
      );

      $exceptions->render(
        function (
          RpcException $e,
          $request
        ) {
          return response()->json([
            'message' => $e->getMessage(),
            'error_code' => 'RPC_ERROR',
          ], 502);
        }
      );
    })
    ->create();
