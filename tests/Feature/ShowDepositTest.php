<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Deposit;
use App\Models\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowDepositTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_shows_deposit(): void
  {
    $gate = Gate::create([
      'name' => 'eth_sepolia',
      'rpc_url' => 'https://rpc.example.com',
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    $address = Address::create([
      'gate_id' => $gate->id,
      'account' => 0,
      'change' => 0,
      'address_index' => 0,
      'address' =>
      '0x1111111111111111111111111111111111111111',
    ]);

    $deposit = Deposit::create([
      'gate_id' => $gate->id,
      'address_id' => $address->id,
      'tx_hash' => '0xDEPOSIT',
      'log_index' => 0,
      'block_number' => 123,
      'block_hash' => '0xBLOCK',
      'amount' => '1000000',
    ]);

    $response = $this->getJson(
      "/api/v1/deposits/{$deposit->id}"
    );

    $response->assertOk();

    $response->assertJsonFragment([
      'id' => $deposit->id,
      'tx_hash' => '0xDEPOSIT',
    ]);
  }
}
