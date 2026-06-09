<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Employee;

class HrDashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('is_active', true)->count();
        $inactiveEmployees = Employee::where('is_active', false)->count();

        $totalAbsences = Absence::count();

        $todayAbsences = Absence::whereDate('absence_date', today())->count();

        $monthAbsences = Absence::whereYear('absence_date', now()->year)
            ->whereMonth('absence_date', now()->month)
            ->count();

        $latestAbsences = Absence::with(['employee', 'creator'])
            ->latest('absence_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('hr-dashboard.index', [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'totalAbsences' => $totalAbsences,
            'todayAbsences' => $todayAbsences,
            'monthAbsences' => $monthAbsences,
            'latestAbsences' => $latestAbsences,
        ]);
    }
}