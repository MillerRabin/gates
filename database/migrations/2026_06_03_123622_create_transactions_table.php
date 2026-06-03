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
    Schema::create('transactions', function (Blueprint $table) {
      $table->id();

      $table->foreignId('gate_id')
        ->constrained('gates');

      $table->foreignId('address_id')
        ->nullable()
        ->constrained('addresses');

      $table->enum('type', [
        'DEPOSIT',
        'WITHDRAWAL'
      ]);

      $table->string('tx_hash', 66);

      $table->unsignedInteger('log_index')
        ->default(0);

      $table->unsignedBigInteger('block_number');

      $table->string('block_hash', 66);

      $table->string('from_address', 42);

      $table->string('to_address', 42);

      $table->string('amount');

      $table->unsignedInteger('confirmations')
        ->default(0);

      $table->enum('status', [
        'PENDING',
        'CONFIRMED',
        'REORGED'
      ]);

      $table->timestamps();

      $table->unique([
        'gate_id',
        'tx_hash',
        'log_index'
      ]);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
