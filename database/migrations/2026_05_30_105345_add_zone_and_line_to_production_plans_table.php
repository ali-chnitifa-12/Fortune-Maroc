<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('production_plans', 'zone_id')) {
                $table->foreignId('zone_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('zones')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('production_plans', 'production_line_id')) {
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
        Schema::table('production_plans', function (Blueprint $table) {
            if (Schema::hasColumn('production_plans', 'production_line_id')) {
                $table->dropConstrainedForeignId('production_line_id');
            }

            if (Schema::hasColumn('production_plans', 'zone_id')) {
                $table->dropConstrainedForeignId('zone_id');
            }
        });
    }
};