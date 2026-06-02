<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DowntimeCategoryController;
use App\Http\Controllers\DowntimeReasonController;
use App\Http\Controllers\ProductionEntryController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\ProductionDowntimeController;
use App\Http\Controllers\ThingsboardDeviceController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MachineStatusController;
use App\Http\Controllers\LineStatusController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\ProductionLineController;
use App\Models\Machine;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'fr'], true)) {
        abort(404);
    }

    session(['locale' => $locale]);

    return back();
})->name('language.switch');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()?->isOperator()) {
            return redirect()->route('production-entries.index');
        }

        return redirect()->route('line-status.index');
    })->name('dashboard');

    Route::middleware('can:view-machine-status')->group(function () {
        Route::get('machine-status', [MachineStatusController::class, 'index'])
            ->name('machine-status.index');
    });

    Route::middleware('can:view-line-kpi-board')->group(function () {
        Route::get('line-status', [LineStatusController::class, 'index'])
            ->name('line-status.index');
    });

    Route::get('/zones/{zone}/production-lines', [ProductionPlanController::class, 'linesByZone'])
        ->name('zones.production-lines');

    Route::get('/production-lines/{production_line}/products', [ProductionPlanController::class, 'productsByLine'])
        ->name('production-lines.products');

    Route::get('/production-lines/{production_line}/machines', [ProductionPlanController::class, 'machinesByLine'])
        ->name('production-lines.machines');

    Route::get('/machines/{machine}/products', function (Machine $machine) {
        if (!$machine->productionLine) {
            return [];
        }

        return $machine->productionLine->activeProducts()
            ->orderBy('products.code')
            ->get([
                'products.id',
                'products.code',
                'products.name',
                'line_product.standard_qty_per_hour',
            ]);
    })->name('machines.products');

    Route::get('production-plans', [ProductionPlanController::class, 'index'])
        ->name('production-plans.index');

    Route::middleware('can:manage-production-plans')->group(function () {
        Route::get('production-plans/create', [ProductionPlanController::class, 'create'])
            ->name('production-plans.create');

        Route::post('production-plans', [ProductionPlanController::class, 'store'])
            ->name('production-plans.store');

        Route::get('production-plans/{production_plan}/edit', [ProductionPlanController::class, 'edit'])
            ->name('production-plans.edit');

        Route::put('production-plans/{production_plan}', [ProductionPlanController::class, 'update'])
            ->name('production-plans.update');

        Route::patch('production-plans/{production_plan}', [ProductionPlanController::class, 'update'])
            ->name('production-plans.update');

        Route::delete('production-plans/{production_plan}', [ProductionPlanController::class, 'destroy'])
            ->name('production-plans.destroy');
    });

    Route::get('production-plans/{production_plan}/create-entry', [ProductionEntryController::class, 'createFromPlan'])
        ->name('production-plans.create-entry');

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
        ->middleware('can:approve-production-entries')
        ->name('production-entries.approve');

    Route::get('production-entries/{production_entry}/edit', [ProductionEntryController::class, 'edit'])
        ->name('production-entries.edit');

    Route::put('production-entries/{production_entry}', [ProductionEntryController::class, 'update'])
        ->name('production-entries.update');

    Route::patch('production-entries/{production_entry}', [ProductionEntryController::class, 'update'])
        ->name('production-entries.update');

    Route::delete('production-entries/{production_entry}', [ProductionEntryController::class, 'destroy'])
        ->name('production-entries.destroy');

    Route::put('production-downtimes/{production_downtime}', [ProductionDowntimeController::class, 'update'])
        ->name('production-downtimes.update');

    Route::middleware('can:view-master-data')->group(function () {
        Route::resource('zones', ZoneController::class)->only(['index']);
        Route::resource('production-lines', ProductionLineController::class)->only(['index']);
        Route::resource('machines', MachineController::class)->only(['index']);
        Route::resource('products', ProductController::class)->only(['index']);
        Route::resource('shifts', ShiftController::class)->only(['index']);
        Route::resource('downtime-categories', DowntimeCategoryController::class)->only(['index']);
        Route::resource('downtime-reasons', DowntimeReasonController::class)->only(['index']);
        Route::resource('thingsboard-devices', ThingsboardDeviceController::class)->only(['index']);
    });

    Route::middleware('can:manage-master-data')->group(function () {
        Route::resource('zones', ZoneController::class)->except(['index', 'show']);
        Route::resource('production-lines', ProductionLineController::class)->except(['index', 'show']);
        Route::resource('machines', MachineController::class)->except(['index', 'show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('shifts', ShiftController::class)->except(['index', 'show']);
        Route::resource('downtime-categories', DowntimeCategoryController::class)->except(['index', 'show']);
        Route::resource('downtime-reasons', DowntimeReasonController::class)->except(['index', 'show']);
        Route::resource('thingsboard-devices', ThingsboardDeviceController::class)->except(['index', 'show']);
    });

    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users-management', UserManagementController::class)
            ->parameters(['users-management' => 'user']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';