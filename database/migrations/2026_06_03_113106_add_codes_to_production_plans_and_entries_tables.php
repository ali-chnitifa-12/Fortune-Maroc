<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('production_plans', 'plan_code')) {
                $table->string('plan_code', 20)->nullable()->after('id');
            }
        });

        Schema::table('production_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('production_entries', 'entry_code')) {
                $table->string('entry_code', 20)->nullable()->after('id');
            }
        });

        $plans = DB::table('production_plans')
            ->whereNull('plan_code')
            ->orderBy('id')
            ->get();

        foreach ($plans as $plan) {
            DB::table('production_plans')
                ->where('id', $plan->id)
                ->update([
                    'plan_code' => 'P' . str_pad((string) $plan->id, 9, '0', STR_PAD_LEFT),
                ]);
        }

        $entries = DB::table('production_entries')
            ->whereNull('entry_code')
            ->orderBy('id')
            ->get();

        foreach ($entries as $entry) {
            DB::table('production_entries')
                ->where('id', $entry->id)
                ->update([
                    'entry_code' => 'E' . str_pad((string) $entry->id, 9, '0', STR_PAD_LEFT),
                ]);
        }

        Schema::table('production_plans', function (Blueprint $table) {
            if (!$this->indexExists('production_plans', 'production_plans_plan_code_unique')) {
                $table->unique('plan_code', 'production_plans_plan_code_unique');
            }
        });

        Schema::table('production_entries', function (Blueprint $table) {
            if (!$this->indexExists('production_entries', 'production_entries_entry_code_unique')) {
                $table->unique('entry_code', 'production_entries_entry_code_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('production_entries', function (Blueprint $table) {
            if ($this->indexExists('production_entries', 'production_entries_entry_code_unique')) {
                $table->dropUnique('production_entries_entry_code_unique');
            }

            if (Schema::hasColumn('production_entries', 'entry_code')) {
                $table->dropColumn('entry_code');
            }
        });

        Schema::table('production_plans', function (Blueprint $table) {
            if ($this->indexExists('production_plans', 'production_plans_plan_code_unique')) {
                $table->dropUnique('production_plans_plan_code_unique');
            }

            if (Schema::hasColumn('production_plans', 'plan_code')) {
                $table->dropColumn('plan_code');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};