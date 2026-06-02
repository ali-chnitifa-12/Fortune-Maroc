<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('line_product')) {
            Schema::create('line_product', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('production_line_id');
                $table->unsignedBigInteger('product_id');

                $table->decimal('standard_qty_per_hour', 18, 2)->nullable();
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->unique(['production_line_id', 'product_id'], 'line_product_unique');
                $table->index('production_line_id', 'line_product_line_id_index');
                $table->index('product_id', 'line_product_product_id_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('line_product');
    }
};