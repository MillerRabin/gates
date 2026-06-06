<?php

namespace Tests\Feature;

use App\Models\Gate;
use App\Models\HotWallet;
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

    HotWallet::create([
      'gate_id' => $gate->id,
      'account' => 0,
      'change' => 0,
      'address_index' => 0,
      'address' =>
      '0x9858EfFD232B4033E47d90003D41EC34EcaEda94',
    ]);

    Http::fake([
      '*' => function ($request) {

        $url = $request->url();

        /*
                 * Wallet Service
                 */
        if (str_contains($url, '/validateaddress')) {
          return Http::response([
            'valid' => true,
          ]);
        }

        if (str_contains($url, '/tx')) {
          return Http::response([
            'tx_hash' => '0xSIGNEDHASH',
            'signed_tx' => '0xSIGNEDTX',
          ]);
        }

        /*
                 * JSON-RPC
                 */
        $payload = $request->data();

        if (
          ($payload['method'] ?? null)
          === 'eth_getTransactionCount'
        ) {
          return Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => '0x0',
          ]);
        }

        if (
          ($payload['method'] ?? null)
          === 'eth_sendRawTransaction'
        ) {
          return Http::response([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => '0xTXHASH',
          ]);
        }

        return Http::response([], 500);
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

    $response->assertJsonFragment([
      'status' => 'BROADCASTED',
    ]);

    $response->assertJsonFragment([
      'tx_hash' => '0xTXHASH',
    ]);

    $this->assertDatabaseHas(
      'withdrawals',
      [
        'gate_id' => $gate->id,
        'status' => 'BROADCASTED',
        'tx_hash' => '0xTXHASH',
        'signed_tx' => '0xSIGNEDTX',
      ]
    );

    Http::assertSent(function ($request) {
      $payload = $request->data();

      return ($payload['method'] ?? null)
        === 'eth_getTransactionCount';
    });

    Http::assertSent(function ($request) {
      $payload = $request->data();

      return ($payload['method'] ?? null)
        === 'eth_sendRawTransaction';
    });
  }
}
