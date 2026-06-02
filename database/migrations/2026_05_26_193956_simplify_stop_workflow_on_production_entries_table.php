<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('production_entries', 'entry_status')) {
                $table->string('entry_status')->default('draft')->after('machine_status');
            }

            if (!Schema::hasColumn('production_entries', 'stop_started_at')) {
                $table->timestamp('stop_started_at')->nullable()->after('entry_status');
            }

            if (!Schema::hasColumn('production_entries', 'stop_ended_at')) {
                $table->timestamp('stop_ended_at')->nullable()->after('stop_started_at');
            }

            if (!Schema::hasColumn('production_entries', 'current_stop_started_at')) {
                $table->timestamp('current_stop_started_at')->nullable()->after('stop_ended_at');
            }

            if (!Schema::hasColumn('production_entries', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('current_stop_started_at');
            }

            if (!Schema::hasColumn('production_entries', 'stops_count')) {
                $table->integer('stops_count')->default(0)->after('stop_duration_min');
            }
        });

        DB::table('production_entries')
            ->whereNull('entry_status')
            ->orWhereIn('entry_status', ['open_stop', 'completed'])
            ->update([
                'entry_status' => 'draft',
            ]);

        DB::table('production_entries')
            ->whereNull('machine_status')
            ->orWhereIn('machine_status', ['Running', 'Stopped', 'Cleaning', 'Changeover', 'Maintenance', 'No production planned', 'Quality hold'])
            ->update([
                'machine_status' => 'active',
            ]);

        DB::table('production_entries')
            ->whereNull('stops_count')
            ->update([
                'stops_count' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (Schema::hasColumn('production_entries', 'current_stop_started_at')) {
                $table->dropColumn('current_stop_started_at');
            }

            if (Schema::hasColumn('production_entries', 'stops_count')) {
                $table->dropColumn('stops_count');
            }
        });
    }
};