<?php

namespace App\Console\Commands;

use App\Models\Gate;
use App\Services\BlockchainIndexer;
use Illuminate\Console\Command;

class BlockchainIndexCommand extends Command
{
  protected $signature =
  'blockchain:index {--base_gate=}';

  protected $description =
  'Index blockchain deposits';

  public function handle(
    BlockchainIndexer $indexer
  ): int {

    $gate = Gate::query()
      ->where(
        'name',
        $this->option('base_gate')
      )
      ->firstOrFail();

    $indexer->index($gate);

    $this->info('Done');

    return self::SUCCESS;
  }
}
