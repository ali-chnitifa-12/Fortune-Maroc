<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_downtimes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('production_entry_id')
                ->constrained('production_entries')
                ->cascadeOnDelete();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();

            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();

            $table->integer('duration_min')->default(0);

            $table->foreignId('downtime_category_id')
                ->nullable()
                ->constrained('downtime_categories')
                ->nullOnDelete();

            $table->foreignId('downtime_reason_id')
                ->nullable()
                ->constrained('downtime_reasons')
                ->nullOnDelete();

            $table->text('comment')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_downtimes');
    }
};