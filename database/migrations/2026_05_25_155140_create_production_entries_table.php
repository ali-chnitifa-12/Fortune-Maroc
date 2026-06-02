<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_entries', function (Blueprint $table) {
            $table->id();

            $table->date('production_date');
            $table->foreignId('shift_id')->constrained()->restrictOnDelete();
            $table->foreignId('machine_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->time('hour_start');
            $table->time('hour_end');

            $table->decimal('planned_qty', 12, 2);
            $table->decimal('actual_qty', 12, 2);
            $table->decimal('rejected_qty', 12, 2)->default(0);
            $table->decimal('good_qty', 12, 2)->default(0);

            $table->string('machine_status')->default('Running');
            $table->integer('stop_duration_min')->default(0);

            $table->foreignId('downtime_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('downtime_reason_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('availability', 8, 2)->default(0);
            $table->decimal('performance', 8, 2)->default(0);
            $table->decimal('quality', 8, 2)->default(0);
            $table->decimal('oee', 8, 2)->default(0);

            $table->text('comment')->nullable();

            $table->boolean('sent_to_thingsboard')->default(false);
            $table->text('thingsboard_response')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['production_date', 'machine_id', 'hour_start', 'hour_end'],
                'unique_machine_hour_entry'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_entries');
    }
};
