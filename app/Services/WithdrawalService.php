<?php

namespace App\Services;

use App\DTOs\CreateWithdrawalDTO;
use App\Enums\WithdrawalStatus;
use App\Models\Gate;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawalService
{
  public function __construct(
    private WalletClient $walletClient,
    private RpcClient $rpcClient,
    private AmountConverter $amountConverter,
  ) {}

  public function createWithdrawal(
    CreateWithdrawalDTO $dto
  ): array {

    return DB::transaction(function () use ($dto) {

      $validation = $this->walletClient->validateAddress([
        'gate' => 'ethereum',
        'address' => $dto->toAddress,
      ]);

      if (!($validation['valid'] ?? false)) {
        throw new InvalidArgumentException(
          'Invalid destination address'
        );
      }

      $gate = Gate::query()
        ->where('name', $dto->assetGate)
        ->firstOrFail();

      $rpcUrl = $gate->parent_gate_id
        ? $gate->parentGate->rpc_url
        : $gate->rpc_url;

      $decimals = 18;

      $amountBaseUnits =
        $this->amountConverter->toBaseUnits(
          $dto->amount,
          $decimals
        );

      $withdrawal = Withdrawal::create([
        'gate_id' => $gate->id,
        'to_address' => $dto->toAddress,
        'amount' => $dto->amount,
        'amount_base_units' => $amountBaseUnits,
        'status' => WithdrawalStatus::CREATED,
      ]);

      $sender = $this->walletClient->createAddress([
        'gate' => 'ethereum',
        'account' => 0,
        'change' => 0,
        'address_index' => 0,
      ]);

      $senderAddress = $sender['address'];

      $nonce = $this->rpcClient->getTransactionCount(
        $rpcUrl,
        $senderAddress
      );

      $signed = $this->walletClient->createTransaction([
        'gate' => 'ethereum',
        'account' => 0,
        'change' => 0,
        'address_index' => 0,
        'tx_params' => [
          'to' => $dto->toAddress,
          'value_wei' => $amountBaseUnits,
          'data' => '0x',
          'nonce' => $nonce,
          'chain_id' => $gate->chain_id,
          'gas_limit' => 21000,
          'max_fee_per_gas_wei' => '30000000000',
          'max_priority_fee_per_gas_wei' => '1500000000',
        ],
      ]);

      $withdrawal->update([
        'status' => WithdrawalStatus::SIGNED,
        'signed_tx' => $signed['signed_tx'],
      ]);

      $broadcastTxHash =
        $this->rpcClient->sendRawTransaction(
          $rpcUrl,
          $signed['signed_tx']
        );

      $withdrawal->update([
        'status' => WithdrawalStatus::BROADCASTED,
        'tx_hash' => $broadcastTxHash,
      ]);

      return [
        'id' => $withdrawal->id,
        'asset_gate' => $gate->name,
        'amount' => $withdrawal->amount,
        'amount_base_units' => $withdrawal->amount_base_units,
        'status' => $withdrawal->status->value,
        'tx_hash' => $withdrawal->tx_hash,
      ];
    });
  }
}
