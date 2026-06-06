<?php

namespace Tests\Integration;

use App\Services\WalletClient;
use Tests\TestCase;

class WalletClientIntegrationTest extends TestCase
{
  private WalletClient $walletClient;

  protected function setUp(): void
  {
    parent::setUp();

    $this->walletClient = app(WalletClient::class);
  }

  public function test_it_creates_address_via_real_wallet_service(): void
  {
    $response = $this->walletClient->createAddress([
      'gate' => 'ethereum',
      'account' => 0,
      'change' => 0,
      'address_index' => 15,
    ]);

    $this->assertIsArray($response);

    $this->assertArrayHasKey('address', $response);

    $this->assertMatchesRegularExpression(
      '/^0x[a-fA-F0-9]{40}$/',
      $response['address']
    );
  }

  public function test_it_validates_valid_ethereum_address(): void
  {
    $response = $this->walletClient->validateAddress([
      'gate' => 'ethereum',
      'address' => '0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045',
    ]);

    $this->assertIsArray($response);

    $this->assertArrayHasKey('valid', $response);

    $this->assertTrue($response['valid']);
  }

  public function test_it_rejects_invalid_ethereum_address(): void
  {
    $response = $this->walletClient->validateAddress([
      'gate' => 'ethereum',
      'address' => 'invalid-address',
    ]);

    $this->assertIsArray($response);

    $this->assertArrayHasKey('valid', $response);

    $this->assertFalse($response['valid']);
  }
}
