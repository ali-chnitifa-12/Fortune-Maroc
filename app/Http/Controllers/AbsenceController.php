<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $query = Absence::with(['user', 'creator'])
            ->latest('absence_date')
            ->latest('id');

        if ($request->filled('employee')) {
            $query->where('employee_name', 'like', '%' . $request->employee . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('absence_date', $request->date);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        return view('absences.index', [
            'absences' => $query->paginate(20)->withQueryString(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['employee', 'date', 'shift', 'reason']),
            'reasons' => $this->reasons(),
            'shifts' => $this->shifts(),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('absences.create', [
            'absence' => new Absence(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'reasons' => $this->reasons(),
            'shifts' => $this->shifts(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateAbsence($request);

        $selectedUser = !empty($data['user_id'])
            ? User::find($data['user_id'])
            : null;

        Absence::create([
            'user_id' => $selectedUser?->id,
            'employee_name' => $selectedUser?->name ?: $data['employee_name'],
            'absence_date' => $data['absence_date'],
            'shift' => $data['shift'] ?? null,
            'reason' => $data['reason'],
            'hours' => $data['hours'],
            'comment' => $data['comment'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('absences.index')
            ->with('success', 'Absence created successfully.');
    }

    public function edit(Absence $absence)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        return view('absences.edit', [
            'absence' => $absence,
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'reasons' => $this->reasons(),
            'shifts' => $this->shifts(),
        ]);
    }

    public function update(Request $request, Absence $absence)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $data = $this->validateAbsence($request);

        $selectedUser = !empty($data['user_id'])
            ? User::find($data['user_id'])
            : null;

        $absence->update([
            'user_id' => $selectedUser?->id,
            'employee_name' => $selectedUser?->name ?: $data['employee_name'],
            'absence_date' => $data['absence_date'],
            'shift' => $data['shift'] ?? null,
            'reason' => $data['reason'],
            'hours' => $data['hours'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()->route('absences.index')
            ->with('success', 'Absence updated successfully.');
    }

    public function destroy(Absence $absence)
    {
        abort_unless(auth()->user()?->canManageAbsences(), 403);

        $absence->delete();

        return redirect()->route('absences.index')
            ->with('success', 'Absence deleted successfully.');
    }

    private function validateAbsence(Request $request): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'employee_name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'absence_date' => ['required', 'date'],
            'shift' => ['nullable', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:100'],
            'hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function reasons(): array
    {
        return [
            'Autorisation',
            'Congé',
            'Maladie',
            'Retard',
            'Absence injustifiée',
            'Formation',
            'Autre',
        ];
    }

    private function shifts(): array
    {
        return [
            'A',
            'B',
            'C',
            'Morning',
            'Afternoon',
            'Night',
        ];
    }
}