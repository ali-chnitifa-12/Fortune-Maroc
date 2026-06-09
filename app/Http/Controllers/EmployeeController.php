<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $query = Employee::query()
            ->latest('is_active')
            ->orderBy('full_name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                    ->orWhere('matricule', 'like', '%' . $request->search . '%')
                    ->orWhere('department', 'like', '%' . $request->search . '%')
                    ->orWhere('position', 'like', '%' . $request->search . '%')
                    ->orWhere('departure_reason', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        return view('employees.index', [
            'employees' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('employees.create', [
            'employee' => new Employee(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateEmployee($request);

        Employee::create([
            'full_name' => $data['full_name'],
            'matricule' => $data['matricule'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'departure_date' => $data['departure_date'] ?? null,
            'departure_reason' => $data['departure_reason'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('employees.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateEmployee($request, $employee);

        $employee->update([
            'full_name' => $data['full_name'],
            'matricule' => $data['matricule'] ?? null,
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'departure_date' => $data['departure_date'] ?? null,
            'departure_reason' => $data['departure_reason'] ?? null,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'matricule' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('employees', 'matricule')->ignore($employee?->id),
            ],
            'department' => ['nullable', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable'],
            'departure_date' => ['nullable', 'date'],
            'departure_reason' => ['nullable', 'string', 'max:255'],
        ]);
    }
}