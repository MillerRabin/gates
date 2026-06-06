<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Deposit;
use App\Models\Gate;
use App\Models\IndexedBlock;
use App\Services\RpcClient;
use RuntimeException;


class BlockchainIndexer
{
  private const INITIAL_SYNC_DEPTH = 100;

  private const TRANSFER_TOPIC = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';


  public function __construct(
    private RpcClient $rpcClient
  ) {}

  public function index(Gate $baseGate): void
  {
    if (empty($baseGate->rpc_url)) {
      throw new RuntimeException(
        "RPC URL is not configured for gate {$baseGate->name}"
      );
    }

    $lastIndexed = IndexedBlock::query()
      ->where('gate_id', $baseGate->id)
      ->latest('block_number')
      ->first();

    $latestBlock = $this->rpcClient->blockNumber(
      $baseGate->rpc_url
    );

    $startBlock = $lastIndexed ?
      $lastIndexed->block_number + 1 :
      max(0, $latestBlock - self::INITIAL_SYNC_DEPTH);

    logger()->info('Blockchain indexing started', [
      'gate' => $baseGate->name,
      'start_block' => $startBlock,
      'latest_block' => $latestBlock,
    ]);

    if ($startBlock > $latestBlock) {
      logger()->info('No new blocks to index', [
        'gate' => $baseGate->name,
        'latest_block' => $latestBlock,
      ]);

      return;
    }

    for (
      $blockNumber = $startBlock;
      $blockNumber <= $latestBlock;
      $blockNumber++
    ) {
      $block = $this->rpcClient->getBlockByNumber(
        $baseGate->rpc_url,
        $blockNumber
      );

      $this->processBlock(
        $baseGate,
        $block
      );

      $this->processErc20Transfers(
        $baseGate,
        $blockNumber
      );
    }

    logger()->info('Blockchain indexing finished', [
      'gate' => $baseGate->name,
      'latest_block' => $latestBlock,
    ]);
  }

  private function processBlock(
    Gate $baseGate,
    array $block
  ): void {

    $this->validateParentHash(
      $baseGate,
      $block
    );

    IndexedBlock::create([
      'gate_id' => $baseGate->id,
      'block_number' => hexdec(
        $block['number']
      ),
      'block_hash' => $block['hash'],
      'parent_hash' => $block['parentHash'],
    ]);

    logger()->debug('Indexed block saved', [
      'gate' => $baseGate->name,
      'block_number' => hexdec($block['number']),
      'block_hash' => $block['hash'],
    ]);

    foreach ($block['transactions'] as $tx) {

      $address = Address::query()
        ->whereRaw(
          'LOWER(address) = ?',
          [strtolower($tx['to'] ?? '')]
        )
        ->first();

      if (!$address) {
        continue;
      }

      Deposit::firstOrCreate(
        [
          'gate_id' => $baseGate->id,
          'tx_hash' => $tx['hash'],
          'log_index' => 0,
        ],
        [
          'address_id' => $address->id,
          'block_number' => hexdec(
            $tx['blockNumber']
          ),
          'block_hash' => $tx['blockHash'],
          'amount' => hexdec(
            $tx['value']
          ),
        ]
      );
    }
  }

  private function validateParentHash(
    Gate $baseGate,
    array $block
  ): void {
    $blockNumber = hexdec($block['number']);

    if ($blockNumber === 0) {
      return;
    }

    $previousBlock = IndexedBlock::query()
      ->where('gate_id', $baseGate->id)
      ->where('block_number', $blockNumber - 1)
      ->first();

    if (!$previousBlock) {
      return;
    }

    if (
      strtolower($previousBlock->block_hash) !== strtolower($block['parentHash'])
    ) {
      logger()->error(
        'Blockchain reorg detected',
        [
          'gate' => $baseGate->name,
          'block_number' => $blockNumber,
          'expected_parent_hash' => $previousBlock->block_hash,
          'actual_parent_hash' => $block['parentHash'],
        ]
      );

      throw new RuntimeException(
        sprintf(
          'Blockchain reorg detected at block %s',
          $blockNumber
        )
      );
    }
  }

  private function processErc20Transfers(
    Gate $baseGate,
    int $blockNumber
  ): void {

    $erc20Gates = Gate::query()
      ->where('parent_gate_id', $baseGate->id)
      ->where('asset_type', 'ERC20')
      ->get();

    foreach ($erc20Gates as $assetGate) {

      if (!$assetGate->token_contract) {
        continue;
      }

      $logs = $this->rpcClient->getLogs(
        $baseGate->rpc_url,
        [
          'fromBlock' => '0x' . dechex($blockNumber),
          'toBlock' => '0x' . dechex($blockNumber),
          'address' => $assetGate->token_contract,
          'topics' => [
            self::TRANSFER_TOPIC,
          ],
        ]
      );

      foreach ($logs as $log) {
        $this->processTransferLog(
          $assetGate,
          $log
        );
      }
    }
  }

  private function processTransferLog(
    Gate $assetGate,
    array $log
  ): void {

    if (!isset($log['topics'][2])) {
      return;
    }

    $toAddress =
      '0x' .
      substr(
        $log['topics'][2],
        -40
      );

    $address = Address::query()
      ->whereRaw(
        'LOWER(address) = ?',
        [strtolower($toAddress)]
      )
      ->first();

    if (!$address) {
      return;
    }

    $amount = $this->hexToDecimal(
      $log['data']
    );

    Deposit::firstOrCreate(
      [
        'gate_id' => $assetGate->id,
        'tx_hash' => $log['transactionHash'],
        'log_index' => hexdec(
          $log['logIndex']
        ),
      ],
      [
        'address_id' => $address->id,
        'block_number' => hexdec(
          $log['blockNumber']
        ),
        'block_hash' => $log['blockHash'],
        'amount' => $amount,
      ]
    );

    logger()->info(
      'ERC20 deposit detected',
      [
        'gate' => $assetGate->name,
        'address' => $address->address,
        'tx_hash' => $log['transactionHash'],
        'amount' => $amount,
      ]
    );
  }

  private function hexToDecimal(
    string $hex
  ): string {

    $hex = str_replace(
      '0x',
      '',
      $hex
    );

    $decimal = '0';

    foreach (str_split($hex) as $char) {

      $decimal = bcmul(
        $decimal,
        '16'
      );

      $decimal = bcadd(
        $decimal,
        (string) hexdec($char)
      );
    }

    return $decimal;
  }
}
