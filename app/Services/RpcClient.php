<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RpcClient
{
  public function blockNumber(string $rpcUrl): int
  {
    $result = $this->request(
      $rpcUrl,
      'eth_blockNumber'
    );

    return hexdec($result);
  }

  public function getBlockByNumber(
    string $rpcUrl,
    int $blockNumber
  ): array {
    return $this->request(
      $rpcUrl,
      'eth_getBlockByNumber',
      [
        '0x' . dechex($blockNumber),
        true,
      ]
    );
  }

  public function getLogs(
    string $rpcUrl,
    array $filter
  ): array {
    return $this->request(
      $rpcUrl,
      'eth_getLogs',
      [$filter]
    );
  }

  public function sendRawTransaction(
    string $rpcUrl,
    string $signedTx
  ): string {
    return $this->request(
      $rpcUrl,
      'eth_sendRawTransaction',
      [$signedTx]
    );
  }

  public function getTransactionCount(
    string $rpcUrl,
    string $address
  ): int {
    $result = $this->request(
      $rpcUrl,
      'eth_getTransactionCount',
      [
        $address,
        'pending',
      ]
    );

    return hexdec($result);
  }

  private function request(
    string $rpcUrl,
    string $method,
    array $params = []
  ): mixed {
    $response = Http::post($rpcUrl, [
      'jsonrpc' => '2.0',
      'id' => 1,
      'method' => $method,
      'params' => $params,
    ])->throw();

    $json = $response->json();

    if (isset($json['error'])) {
      throw new RuntimeException(
        $json['error']['message']
      );
    }

    return $json['result'];
  }
}
