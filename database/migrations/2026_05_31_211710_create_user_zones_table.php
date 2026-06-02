<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_zones')) {
            Schema::create('user_zones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'zone_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_zones');
    }
};