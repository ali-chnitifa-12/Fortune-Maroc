<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->time('hour_start')->nullable()->after('product_id');
            $table->time('hour_end')->nullable()->after('hour_start');

            $table->dropUnique('production_plan_unique');

            $table->unique([
                'plan_date',
                'shift_id',
                'machine_id',
                'hour_start',
                'hour_end',
            ], 'production_plan_hour_unique');
        });
    }

    public function down(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            $table->dropUnique('production_plan_hour_unique');

            $table->unique([
                'plan_date',
                'shift_id',
                'machine_id',
                'product_id',
            ], 'production_plan_unique');

            $table->dropColumn(['hour_start', 'hour_end']);
        });
    }
};