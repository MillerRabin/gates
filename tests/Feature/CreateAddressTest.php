<?php

namespace Tests\Feature;

use App\Models\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateAddressTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_creates_address(): void
  {
    $gate = Gate::create([
      'name' => 'ethereum',
      'rpc_url' => null,
      'chain_id' => 11155111,
      'confirmations_required' => 12,
      'asset_type' => 'NATIVE',
    ]);

    Http::fake([
      '*' => Http::response([
        'address' => '0xABCDEF1234567890abcdef1234567890ABCDEF12'
      ], 200),
    ]);

    $response = $this->postJson('/api/v1/addresses', [
      'gate' => 'ethereum',
      'account' => 0,
      'change' => 0,
      'address_index' => 15,
    ]);

    $response->assertCreated();

    $response->assertJsonStructure([
      'id',
      'gate_id',
      'account',
      'change',
      'address_index',
      'address',
      'created_at',
      'updated_at',
    ]);
    
    $this->assertDatabaseHas('addresses', [
      'gate_id' => $gate->id,
      'account' => 0,
      'change' => 0,
      'address_index' => 15,
      'address' => '0xABCDEF1234567890abcdef1234567890ABCDEF12',
    ]);
  }
}
