<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('production_entries', 'chute_qty')) {
                $table->decimal('chute_qty', 15, 2)->default(0)->after('rejected_qty');
            }
        });

        DB::table('production_entries')
            ->whereNull('chute_qty')
            ->update([
                'chute_qty' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (Schema::hasColumn('production_entries', 'chute_qty')) {
                $table->dropColumn('chute_qty');
            }
        });
    }
};