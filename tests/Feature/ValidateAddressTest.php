<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ValidateAddressTest extends TestCase
{
  public function test_it_validates_address(): void
  {
    Http::fake([
      '*' => Http::response([
        'valid' => true,
      ], 200),
    ]);

    $response = $this->postJson('/api/v1/validateaddress', [
      'gate' => 'ethereum',
      'address' => '0xABCDEF1234567890abcdef1234567890ABCDEF12',
    ]);

    $response->assertOk();

    $response->assertJson([
      'valid' => true,
    ]);
  }
}
