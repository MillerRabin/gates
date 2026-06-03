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
    Schema::create('indexed_blocks', function (Blueprint $table) {
      $table->id();

      $table->foreignId('gate_id')
        ->constrained('gates');

      $table->unsignedBigInteger('block_number');
      $table->string('block_hash', 66);
      $table->string('parent_hash', 66);
      $table->timestamps();
      $table->unique(['gate_id', 'block_number']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('indexed_blocks');
  }
};
