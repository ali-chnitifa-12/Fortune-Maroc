<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            if (!Schema::hasColumn('machines', 'production_line_id')) {
                $table->unsignedBigInteger('production_line_id')
                    ->nullable()
                    ->after('id');

                $table->index('production_line_id', 'machines_production_line_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            if (Schema::hasColumn('machines', 'production_line_id')) {
                $table->dropIndex('machines_production_line_id_index');
                $table->dropColumn('production_line_id');
            }
        });
    }
};