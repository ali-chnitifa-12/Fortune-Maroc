<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('zones')->updateOrInsert(
            ['code' => 'ZP'],
            [
                'name' => 'Zone Production',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $zoneId = DB::table('zones')->where('code', 'ZP')->value('id');

        foreach ([
            ['code' => 'L1', 'name' => 'Ligne 1'],
            ['code' => 'L2', 'name' => 'Ligne 2'],
            ['code' => 'L3', 'name' => 'Ligne 3'],
        ] as $line) {
            DB::table('production_lines')->updateOrInsert(
                ['code' => $line['code']],
                [
                    'zone_id' => $zoneId,
                    'name' => $line['name'],
                    'description' => $line['name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('production_lines')
            ->whereIn('code', ['L1', 'L2', 'L3'])
            ->delete();
    }
};