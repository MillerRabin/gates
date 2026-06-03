<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('gates')->insert([
            [
                'id' => 1,
                'name' => 'eth_sepolia',
                'rpc_url' => null,
                'chain_id' => 11155111,
                'confirmations_required' => 12,
                'parent_gate_id' => null,
                'asset_type' => 'NATIVE',
                'token_contract' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'usdc_sepolia',
                'rpc_url' => null,
                'chain_id' => 11155111,
                'confirmations_required' => 12,
                'parent_gate_id' => 1,
                'asset_type' => 'ERC20',
                'token_contract' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}