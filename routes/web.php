<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\DowntimeCategoryController;
use App\Http\Controllers\DowntimeReasonController;
use App\Http\Controllers\LineStatusController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineStatusController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionDowntimeController;
use App\Http\Controllers\ProductionEntryController;
use App\Http\Controllers\ProductionLineController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ThingsboardDeviceController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ZoneController;
use App\Models\ProductionEntry;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'fr'], true)) {
        Session::put('locale', $locale);
        App::setLocale($locale);
    }

    return back();
})->name('language.switch');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $latestEntries = ProductionEntry::with([
            'zone',
            'productionLine',
            'shift',
            'product',
            'approver',
        ])
            ->orderByDesc('production_date')
            ->orderByDesc('hour_start')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'latestEntries' => $latestEntries,
        ]);
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Production Planning
    |--------------------------------------------------------------------------
    */

    Route::get('production-plans/lines-by-zone/{zone}', [ProductionPlanController::class, 'linesByZone'])
        ->name('production-plans.lines-by-zone');

    Route::get('production-plans/products-by-line/{production_line}', [ProductionPlanController::class, 'productsByLine'])
        ->name('production-plans.products-by-line');

    Route::get('production-plans/machines-by-line/{production_line}', [ProductionPlanController::class, 'machinesByLine'])
        ->name('production-plans.machines-by-line');

    Route::get('production-plans', [ProductionPlanController::class, 'index'])
        ->name('production-plans.index');

    Route::get('production-plans/create', [ProductionPlanController::class, 'create'])
        ->name('production-plans.create');

    Route::post('production-plans', [ProductionPlanController::class, 'store'])
        ->name('production-plans.store');

    Route::get('production-plans/{production_plan}/edit', [ProductionPlanController::class, 'edit'])
        ->name('production-plans.edit');

    Route::put('production-plans/{production_plan}', [ProductionPlanController::class, 'update'])
        ->name('production-plans.update');

    Route::patch('production-plans/{production_plan}', [ProductionPlanController::class, 'update']);

    Route::delete('production-plans/{production_plan}', [ProductionPlanController::class, 'destroy'])
        ->name('production-plans.destroy');

    /*
    |--------------------------------------------------------------------------
    | Production Entries
    |--------------------------------------------------------------------------
    */

    Route::post('production-plans/{production_plan}/create-entry', [ProductionEntryController::class, 'createFromPlan'])
        ->name('production-entries.create-from-plan');

    Route::get('production-entries', [ProductionEntryController::class, 'index'])
        ->name('production-entries.index');

    Route::get('production-entries/create', [ProductionEntryController::class, 'create'])
        ->name('production-entries.create');

    Route::post('production-entries', [ProductionEntryController::class, 'store'])
        ->name('production-entries.store');

    Route::post('production-entries/{production_entry}/stop', [ProductionEntryController::class, 'stopMachine'])
        ->name('production-entries.stop');

    Route::post('production-entries/{production_entry}/fixed', [ProductionEntryController::class, 'fixedMachine'])
        ->name('production-entries.fixed');

    Route::post('production-entries/{production_entry}/finish', [ProductionEntryController::class, 'finishEntry'])
        ->name('production-entries.finish');

    Route::post('production-entries/{production_entry}/approve', [ProductionEntryController::class, 'approveEntry'])
        ->name('production-entries.approve');

    Route::get('production-entries/{production_entry}/edit', [ProductionEntryController::class, 'edit'])
        ->name('production-entries.edit');

    Route::put('production-entries/{production_entry}', [ProductionEntryController::class, 'update'])
        ->name('production-entries.update');

    Route::patch('production-entries/{production_entry}', [ProductionEntryController::class, 'update']);

    Route::delete('production-entries/{production_entry}', [ProductionEntryController::class, 'destroy'])
        ->name('production-entries.destroy');

    Route::put('production-downtimes/{production_downtime}', [ProductionDowntimeController::class, 'update'])
        ->name('production-downtimes.update');

    /*
    |--------------------------------------------------------------------------
    | KPI / Status Pages
    |--------------------------------------------------------------------------
    */

    Route::get('machine-status', [MachineStatusController::class, 'index'])
        ->name('machine-status.index');

    Route::get('line-status', [LineStatusController::class, 'index'])
        ->name('line-status.index');

    /*
    |--------------------------------------------------------------------------
    | Plant Structure
    |--------------------------------------------------------------------------
    */

    Route::resource('zones', ZoneController::class)->except(['show']);
    Route::resource('production-lines', ProductionLineController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

    Route::resource('machines', MachineController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('shifts', ShiftController::class)->except(['show']);
    Route::resource('downtime-categories', DowntimeCategoryController::class)->except(['show']);
    Route::resource('downtime-reasons', DowntimeReasonController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | ThingsBoard
    |--------------------------------------------------------------------------
    */

    Route::resource('thingsboard-devices', ThingsboardDeviceController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Human Resources
    |--------------------------------------------------------------------------
    */

    Route::get('absences/export', [AbsenceController::class, 'export'])
    ->name('absences.export');

    Route::resource('absences', AbsenceController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    Route::resource('users-management', UserManagementController::class)->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';