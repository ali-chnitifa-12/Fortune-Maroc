<?php
use App\Models\User;
use App\Models\Zone;
use App\Models\ProductionLine;
use App\Models\Machine;
use App\Models\Product;
use App\Models\Shift;
use App\Models\ProductionPlan;
use App\Models\ProductionEntry;
use App\Models\ProductionDowntime;
use App\Models\DowntimeCategory;
use App\Models\DowntimeReason;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// 1. Create Admin Account
$admin = User::updateOrCreate(
    ['email' => 'admin@example.com'],
    [
        'name' => 'MES Administrator',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'is_active' => true
    ]
);

// 2. Create Supervisor Account
$supervisor = User::updateOrCreate(
    ['email' => 'supervisor@example.com'],
    [
        'name' => 'MES Supervisor',
        'password' => Hash::make('password'),
        'role' => 'supervisor',
        'is_active' => true
    ]
);

// 3. Create Operator Account (User normal)
$operator = User::updateOrCreate(
    ['email' => 'operator@example.com'],
    [
        'name' => 'MES Operator',
        'password' => Hash::make('password'),
        'role' => 'operator',
        'is_active' => true
    ]
);

// Also keep the old test@example.com user as admin for compatibility
User::updateOrCreate(
    ['email' => 'test@example.com'],
    [
        'name' => 'Test Admin',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'is_active' => true
    ]
);

echo "Accounts created successfully:\n";
echo "Admin: admin@example.com / password\n";
echo "Supervisor: supervisor@example.com / password\n";
echo "Operator: operator@example.com / password\n";
