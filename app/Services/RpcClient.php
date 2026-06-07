<?php

namespace App\Services;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\RpcException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

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

        try {

            $response = Http::post(
                $rpcUrl,
                [
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => $method,
                    'params' => $params,
                ]
            )->throw();

        } catch (RequestException $e) {

            throw new RpcException(
                'RPC request failed',
                previous: $e
            );
        }

        $json = $response->json();

        if (! empty($json['error'])) {

            $message =
                $json['error']['message']
                ?? 'Unknown RPC error';

            if (
                str_contains(
                    strtolower($message),
                    'insufficient funds'
                )
            ) {
                throw new InsufficientFundsException(
                    $message
                );
            }

            throw new RpcException(
                $message
            );
        }

        return $json['result'];
    }
}
