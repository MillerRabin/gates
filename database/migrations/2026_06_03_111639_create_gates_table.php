<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('gates', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('rpc_url');
      $table->unsignedBigInteger('chain_id');
      $table->unsignedInteger('confirmations_required');

      $table->foreignId('parent_gate_id')
        ->nullable()
        ->constrained('gates')
        ->nullOnDelete();

      $table->enum('asset_type', ['NATIVE', 'ERC20']);

      $table->string('token_contract')
        ->nullable();

      $table->timestamps();
      $table->index('chain_id');
      $table->index('asset_type');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('gates');
  }
};
