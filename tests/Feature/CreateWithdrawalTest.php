<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Gate;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateWithdrawalTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_creates_and_broadcasts_eth_withdrawal(): void
  {
    $gate = Gate::create([
      'name' => 'eth_sepolia',
      'rpc_url' => 'https://ethereum-sepolia-rpc.publicnode.com',
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    Address::create([
      'gate_id' => $gate->id,
      'account' => 0,
      'change' => 0,
      'address_index' => 15,
      'address' => '0x1111111111111111111111111111111111111111',
    ]);

    Http::fake([
      '*' => function ($request) {

        $url = $request->url();

        if (str_contains($url, '/validateaddress')) {
          return Http::response([
            'valid' => true,
          ]);
        }

        if (str_contains($url, '/createaddress')) {
          return Http::response([
            'address' =>
            '0x1111111111111111111111111111111111111111',
          ]);
        }

        if (str_contains($url, '/tx')) {
          return Http::response([
            'tx_hash' => '0xTXHASH',
            'signed_tx' => '0xSIGNEDTX',
          ]);
        }

        return Http::response([
          'result' => '0xTXHASH',
        ]);
      },
    ]);

    $response = $this->postJson(
      '/api/v1/withdrawals',
      [
        'asset_gate' => 'eth_sepolia',
        'to_address' =>
        '0x2222222222222222222222222222222222222222',
        'amount' => '0.0004',
      ]
    );

    $response->assertCreated();

    $response->assertJsonFragment([
      'asset_gate' => 'eth_sepolia',
    ]);

    $this->assertDatabaseHas(
      'withdrawals',
      [
        'status' => 'BROADCASTED',
        'tx_hash' => '0xTXHASH',
      ]
    );
  }
}
