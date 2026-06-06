<?php

namespace Tests\Integration;

use App\DTOs\CreateWithdrawalDTO;
use App\Models\Gate;
use App\Models\HotWallet;
use App\Services\RpcClient;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalServiceIntegrationTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_creates_and_broadcasts_withdrawal(): void
  {
    $gate = Gate::create([
      'name' => 'eth_sepolia',
      'rpc_url' => 'https://ethereum-sepolia-rpc.publicnode.com',
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    HotWallet::create([
      'gate_id' => $gate->id,
      'account' => 0,
      'change' => 0,
      'address_index' => 0,
      'address' =>
      '0x9858EfFD232B4033E47d90003D41EC34EcaEda94',
    ]);

    $rpcClient = $this->mock(
      RpcClient::class
    );

    $rpcClient
      ->shouldReceive('getTransactionCount')
      ->once()
      ->andReturn(0);

    $rpcClient
      ->shouldReceive('sendRawTransaction')
      ->once()
      ->andReturn('0xTESTHASH');

    $dto = new CreateWithdrawalDTO(
      assetGate: 'eth_sepolia',
      toAddress: '0x9858EfFD232B4033E47d90003D41EC34EcaEda94',
      amount: '0.0004',
    );

    $result = app(
      WithdrawalService::class
    )->createWithdrawal($dto);

    $this->assertEquals(
      'eth_sepolia',
      $result['asset_gate']
    );

    $this->assertEquals(
      'BROADCASTED',
      $result['status']
    );

    $this->assertEquals(
      '0xTESTHASH',
      $result['tx_hash']
    );

    $this->assertDatabaseHas(
      'withdrawals',
      [
        'gate_id' => $gate->id,
        'status' => 'BROADCASTED',
        'tx_hash' => '0xTESTHASH',
      ]
    );
  }

  public function test_it_throws_exception_for_invalid_address(): void
  {
    Gate::create([
      'name' => 'eth_sepolia',
      'rpc_url' => 'https://ethereum-sepolia-rpc.publicnode.com',
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    $dto = new CreateWithdrawalDTO(
      assetGate: 'eth_sepolia',
      toAddress: 'invalid-address',
      amount: '0.0004',
    );

    $this->expectException(
      \InvalidArgumentException::class
    );

    $this->expectExceptionMessage(
      'Invalid destination address'
    );

    app(
      WithdrawalService::class
    )->createWithdrawal($dto);
  }
}
