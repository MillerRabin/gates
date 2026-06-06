<?php

namespace App\Console\Commands;

use App\Models\Gate;
use App\Models\HotWallet;
use App\Services\WalletClient;
use Illuminate\Console\Command;

class HotWalletSyncCommand extends Command
{
  protected $signature = 'hotwallet:sync';

  protected $description = 'Sync hot wallets for all base gates';

  public function __construct(
    private WalletClient $walletClient
  ) {
    parent::__construct();
  }

  public function handle(): int
  {
    $baseGates = Gate::query()
      ->whereNull('parent_gate_id')
      ->get();

    foreach ($baseGates as $gate) {

      $response = $this->walletClient->createAddress([
        'gate' => 'ethereum',
        'account' => 0,
        'change' => 0,
        'address_index' => 0,
      ]);

      HotWallet::updateOrCreate(
        [
          'gate_id' => $gate->id,
        ],
        [
          'account' => 0,
          'change' => 0,
          'address_index' => 0,
          'address' => $response['address'],
        ]
      );

      $this->info(
        sprintf(
          '%s => %s',
          $gate->name,
          $response['address']
        )
      );
    }

    return self::SUCCESS;
  }
}
