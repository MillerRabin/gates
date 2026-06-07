<?php

namespace Tests\Feature;

use App\Models\Gate;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListWithdrawalsTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_lists_withdrawals(): void
  {
    $gate = Gate::create([
      'name' => 'eth_sepolia',
      'rpc_url' => 'https://rpc.example.com',
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    Withdrawal::create([
      'gate_id' => $gate->id,
      'to_address' =>
      '0x2222222222222222222222222222222222222222',
      'amount' => '0.1',
      'amount_base_units' =>
      '100000000000000000',
      'status' => 'BROADCASTED',
      'tx_hash' => '0xWITHDRAWAL',
    ]);

    $response = $this->getJson(
      '/api/v1/withdrawals'
    );

    $response->assertOk();

    $response->assertJsonFragment([
      'tx_hash' => '0xWITHDRAWAL',
    ]);
  }
}
