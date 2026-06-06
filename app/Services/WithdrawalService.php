<?php

namespace App\Services;

use App\DTOs\CreateWithdrawalDTO;
use App\Enums\WithdrawalStatus;
use App\Models\Gate;
use App\Models\HotWallet;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

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

      $gate = Gate::query()
        ->where('name', $dto->assetGate)
        ->firstOrFail();

      $baseGate = $gate->parent_gate_id
        ? $gate->parentGate
        : $gate;

      $validation =
        $this->walletClient->validateAddress([
          'gate' => 'ethereum',
          'address' => $dto->toAddress,
        ]);

      if (! ($validation['valid'] ?? false)) {
        throw new InvalidArgumentException(
          'Invalid destination address'
        );
      }

      $hotWallet = HotWallet::query()
        ->where('gate_id', $baseGate->id)
        ->first();

      if (!$hotWallet) {
        throw new RuntimeException(
          "Hot wallet is not configured for gate {$baseGate->name}"
        );
      }

      $decimals = $gate->asset_type === 'ERC20'
        ? 6 // USDC Sepolia
        : 18;

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

      $nonce = $this->rpcClient->getTransactionCount(
        $baseGate->rpc_url,
        $hotWallet->address
      );

      $txParams = $this->buildTransactionParams(
        gate: $gate,
        toAddress: $dto->toAddress,
        amountBaseUnits: $amountBaseUnits,
        nonce: $nonce
      );

      $signed = $this->walletClient->createTransaction([
        'gate' => 'ethereum',
        'account' => $hotWallet->account,
        'change' => $hotWallet->change,
        'address_index' => $hotWallet->address_index,
        'tx_params' => $txParams,
      ]);

      $withdrawal->update([
        'status' => WithdrawalStatus::SIGNED,
        'signed_tx' => $signed['signed_tx'],
      ]);

      $broadcastTxHash =
        $this->rpcClient->sendRawTransaction(
          $baseGate->rpc_url,
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
        'amount_base_units'
        => $withdrawal->amount_base_units,
        'status'
        => $withdrawal->status->value,
        'tx_hash'
        => $withdrawal->tx_hash,
      ];
    });
  }

  private function buildTransactionParams(
    Gate $gate,
    string $toAddress,
    string $amountBaseUnits,
    int $nonce
  ): array {

    if ($gate->asset_type === 'ERC20') {

      return [
        'to' => $gate->token_contract,
        'value_wei' => '0',
        'data' => $this->buildErc20TransferData(
          $toAddress,
          $amountBaseUnits
        ),
        'nonce' => $nonce,
        'chain_id' => $gate->chain_id,
        'gas_limit' => 90000,
        'max_fee_per_gas_wei'
        => '30000000000',
        'max_priority_fee_per_gas_wei'
        => '1500000000',
      ];
    }

    return [
      'to' => $toAddress,
      'value_wei' => $amountBaseUnits,
      'data' => '0x',
      'nonce' => $nonce,
      'chain_id' => $gate->chain_id,
      'gas_limit' => 21000,
      'max_fee_per_gas_wei'
      => '30000000000',
      'max_priority_fee_per_gas_wei'
      => '1500000000',
    ];
  }

  private function buildErc20TransferData(
    string $recipient,
    string $amount
  ): string {

    $methodId = 'a9059cbb';

    $recipient =
      strtolower(
        ltrim($recipient, '0x')
      );

    $recipient =
      str_pad(
        $recipient,
        64,
        '0',
        STR_PAD_LEFT
      );

    $amountHex =
      gmp_strval(
        gmp_init($amount, 10),
        16
      );

    $amountHex =
      str_pad(
        $amountHex,
        64,
        '0',
        STR_PAD_LEFT
      );

    return '0x'
      . $methodId
      . $recipient
      . $amountHex;
  }
}
