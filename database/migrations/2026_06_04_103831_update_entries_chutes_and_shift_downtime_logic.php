<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('production_entries', 'chute_1_qty')) {
                $table->decimal('chute_1_qty', 15, 2)->default(0)->after('rejected_qty');
            }

            if (!Schema::hasColumn('production_entries', 'chute_2_qty')) {
                $table->decimal('chute_2_qty', 15, 2)->default(0)->after('chute_1_qty');
            }

            if (!Schema::hasColumn('production_entries', 'chute_3_qty')) {
                $table->decimal('chute_3_qty', 15, 2)->default(0)->after('chute_2_qty');
            }
        });

        if (Schema::hasColumn('production_entries', 'chute_qty')) {
            DB::table('production_entries')
                ->where(function ($query) {
                    $query->whereNull('chute_1_qty')
                        ->orWhere('chute_1_qty', 0);
                })
                ->update([
                    'chute_1_qty' => DB::raw('COALESCE(chute_qty, 0)'),
                    'chute_2_qty' => DB::raw('COALESCE(chute_2_qty, 0)'),
                    'chute_3_qty' => DB::raw('COALESCE(chute_3_qty, 0)'),
                ]);
        }

        Schema::table('production_downtimes', function (Blueprint $table) {
            if (!Schema::hasColumn('production_downtimes', 'production_plan_id')) {
                $table->foreignId('production_plan_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('production_plans')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('production_downtimes', 'production_entry_id')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE production_downtimes ALTER COLUMN production_entry_id DROP NOT NULL');
            } else {
                DB::statement('ALTER TABLE production_downtimes MODIFY production_entry_id BIGINT UNSIGNED NULL');
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE production_downtimes pd
                SET production_plan_id = pe.production_plan_id
                FROM production_entries pe
                WHERE pe.id = pd.production_entry_id
                    AND pd.production_plan_id IS NULL
            ");
        } else {
            DB::statement("
                UPDATE production_downtimes pd
                INNER JOIN production_entries pe ON pe.id = pd.production_entry_id
                SET pd.production_plan_id = pe.production_plan_id
                WHERE pd.production_plan_id IS NULL
            ");
        }

        Schema::table('production_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('production_plans', 'entries_generated_at')) {
                $table->timestamp('entries_generated_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            if (Schema::hasColumn('production_plans', 'entries_generated_at')) {
                $table->dropColumn('entries_generated_at');
            }
        });

        Schema::table('production_downtimes', function (Blueprint $table) {
            if (Schema::hasColumn('production_downtimes', 'production_plan_id')) {
                $table->dropConstrainedForeignId('production_plan_id');
            }
        });

        Schema::table('production_entries', function (Blueprint $table) {
            if (Schema::hasColumn('production_entries', 'chute_3_qty')) {
                $table->dropColumn('chute_3_qty');
            }

            if (Schema::hasColumn('production_entries', 'chute_2_qty')) {
                $table->dropColumn('chute_2_qty');
            }

            if (Schema::hasColumn('production_entries', 'chute_1_qty')) {
                $table->dropColumn('chute_1_qty');
            }
        });
    }
};
