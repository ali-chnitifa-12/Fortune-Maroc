<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'zone_id')) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'production_line_id')) {
                $table->unsignedBigInteger('production_line_id')->nullable()->after('zone_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'production_line_id')) {
                $table->dropColumn('production_line_id');
            }

            if (Schema::hasColumn('users', 'zone_id')) {
                $table->dropColumn('zone_id');
            }
        });
    }
};