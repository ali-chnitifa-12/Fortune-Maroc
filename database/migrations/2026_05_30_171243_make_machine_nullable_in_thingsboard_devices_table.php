<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('thingsboard_devices', 'machine_id')) {
            DB::statement('ALTER TABLE thingsboard_devices MODIFY machine_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Do not revert to NOT NULL because line mappings need machine_id nullable.
    }
};