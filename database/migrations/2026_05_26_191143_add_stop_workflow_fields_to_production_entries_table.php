<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            $table->string('entry_status')->default('completed')->after('machine_status');
            $table->timestamp('stop_started_at')->nullable()->after('entry_status');
            $table->timestamp('stop_ended_at')->nullable()->after('stop_started_at');
            $table->timestamp('completed_at')->nullable()->after('stop_ended_at');
        });
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            $table->dropColumn([
                'entry_status',
                'stop_started_at',
                'stop_ended_at',
                'completed_at',
            ]);
        });
    }
};