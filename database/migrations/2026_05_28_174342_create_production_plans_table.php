<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();

            $table->date('plan_date');
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->decimal('planned_qty', 12, 2)->default(0);
            $table->decimal('target_oee', 5, 2)->nullable();

            $table->string('responsible')->nullable();

            $table->string('status')->default('planned');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'plan_date',
                'shift_id',
                'machine_id',
                'product_id',
            ], 'production_plan_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};