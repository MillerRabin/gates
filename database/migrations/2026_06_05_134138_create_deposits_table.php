<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('deposits', function (Blueprint $table) {
      $table->id();

      $table->foreignId('gate_id')
        ->constrained('gates');

      $table->foreignId('address_id')
        ->constrained('addresses');

      $table->string('tx_hash', 66);

      $table->unsignedInteger('log_index')
        ->default(0);

      $table->unsignedBigInteger('block_number');

      $table->string('block_hash', 66);

      $table->string('amount');

      $table->timestamps();

      $table->unique([
        'gate_id',
        'tx_hash',
        'log_index',
      ]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('deposits');
  }
};
