<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            $table->foreignId('production_plan_id')
                ->nullable()
                ->after('id')
                ->constrained('production_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('production_plan_id');
        });
    }
};