<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('production_entries', 'zone_id')) {
                $table->foreignId('zone_id')
                    ->nullable()
                    ->after('production_plan_id')
                    ->constrained('zones')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('production_entries', 'production_line_id')) {
                $table->foreignId('production_line_id')
                    ->nullable()
                    ->after('zone_id')
                    ->constrained('production_lines')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (Schema::hasColumn('production_entries', 'production_line_id')) {
                $table->dropConstrainedForeignId('production_line_id');
            }

            if (Schema::hasColumn('production_entries', 'zone_id')) {
                $table->dropConstrainedForeignId('zone_id');
            }
        });
    }
};