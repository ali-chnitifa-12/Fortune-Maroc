<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('production_plans', 'machine_id')) {
            DB::statement('ALTER TABLE production_plans MODIFY machine_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasColumn('production_entries', 'machine_id')) {
            DB::statement('ALTER TABLE production_entries MODIFY machine_id BIGINT UNSIGNED NULL');
        }

        Schema::table('production_downtimes', function (Blueprint $table) {
            if (!Schema::hasColumn('production_downtimes', 'machine_id')) {
                $table->unsignedBigInteger('machine_id')->nullable()->after('production_entry_id');
                $table->index('machine_id', 'production_downtimes_machine_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_downtimes', function (Blueprint $table) {
            if (Schema::hasColumn('production_downtimes', 'machine_id')) {
                $table->dropIndex('production_downtimes_machine_id_index');
                $table->dropColumn('machine_id');
            }
        });
    }
};