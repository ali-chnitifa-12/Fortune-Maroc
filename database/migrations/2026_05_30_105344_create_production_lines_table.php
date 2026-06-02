<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('production_lines')) {
            Schema::create('production_lines', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('zone_id')->nullable();

                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->index('zone_id', 'production_lines_zone_id_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_lines');
    }
};