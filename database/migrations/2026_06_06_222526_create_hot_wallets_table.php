<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('hot_wallets', function (Blueprint $table) {
      $table->id();

      $table->foreignId('gate_id')
        ->constrained('gates')
        ->cascadeOnDelete();

      $table->unsignedInteger('account');
      $table->unsignedInteger('change');
      $table->unsignedInteger('address_index');

      $table->string('address', 42);

      $table->timestamps();

      $table->unique(['gate_id']);
      $table->unique([
        'gate_id',
        'account',
        'change',
        'address_index',
      ]);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('hot_wallets');
  }
};
