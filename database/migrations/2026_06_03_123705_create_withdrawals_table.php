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
    Schema::create('withdrawals', function (Blueprint $table) {
      $table->id();

      $table->foreignId('gate_id')
        ->constrained('gates');

      $table->string('to_address', 42);

      $table->string('amount');

      $table->string('amount_base_units');

      $table->longText('signed_tx')
        ->nullable();

      $table->string('tx_hash', 66)
        ->nullable();

      $table->enum('status', [
        'CREATED',
        'SIGNED',
        'BROADCASTED',
        'FAILED'
      ]);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('withdrawals');
  }
};
