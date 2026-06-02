<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thingsboard_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('thingsboard_devices', 'mapping_type')) {
                $table->string('mapping_type')->default('line')->after('id');
            }

            if (!Schema::hasColumn('thingsboard_devices', 'zone_id')) {
                $table->unsignedBigInteger('zone_id')->nullable()->after('mapping_type');
                $table->index('zone_id', 'thingsboard_devices_zone_id_index');
            }

            if (!Schema::hasColumn('thingsboard_devices', 'production_line_id')) {
                $table->unsignedBigInteger('production_line_id')->nullable()->after('zone_id');
                $table->index('production_line_id', 'thingsboard_devices_line_id_index');
            }

            if (!Schema::hasColumn('thingsboard_devices', 'machine_id')) {
                $table->unsignedBigInteger('machine_id')->nullable()->after('production_line_id');
                $table->index('machine_id', 'thingsboard_devices_machine_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('thingsboard_devices', function (Blueprint $table) {
            if (Schema::hasColumn('thingsboard_devices', 'machine_id')) {
                $table->dropIndex('thingsboard_devices_machine_id_index');
                $table->dropColumn('machine_id');
            }

            if (Schema::hasColumn('thingsboard_devices', 'production_line_id')) {
                $table->dropIndex('thingsboard_devices_line_id_index');
                $table->dropColumn('production_line_id');
            }

            if (Schema::hasColumn('thingsboard_devices', 'zone_id')) {
                $table->dropIndex('thingsboard_devices_zone_id_index');
                $table->dropColumn('zone_id');
            }

            if (Schema::hasColumn('thingsboard_devices', 'mapping_type')) {
                $table->dropColumn('mapping_type');
            }
        });
    }
};