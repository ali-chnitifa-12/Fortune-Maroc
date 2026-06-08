<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $query = $this->filteredAbsencesQuery($request)
            ->latest('absence_date')
            ->latest('id');

        return view('absences.index', [
            'absences' => $query->paginate(20)->withQueryString(),
            'users' => User::where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only([
                'employee',
                'date',
                'date_from',
                'date_to',
                'shift',
                'reason',
            ]),
            'reasons' => $this->reasons(),
            'shifts' => $this->shifts(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()?->canViewAbsences(), 403);

        $absences = $this->filteredAbsencesQuery($request)
            ->orderBy('absence_date')
            ->orderBy('employee_name')
            ->get();

        $dateFrom = $request->input('date_from') ?: $request->input('date') ?: 'all';
        $dateTo = $request->input('date_to') ?: $request->input('date') ?: 'all';

        $fileName = 'absences_' . $dateFrom . '_' . $dateTo . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($absences) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM pour Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Nom complet',
                'Date',
                'Shift',
                'Motif',
                'Heures',
                'Commentaire',
                'Créé par',
                'Créé le',
            ], ';');

            foreach ($absences as $absence) {
                fputcsv($handle, [
                    $absence->employee_name,
                    $absence->absence_date?->format('Y-m-d'),
                    $absence->shift ?: '',
                    $absence->reason,
                    $absence->hours,
                    $absence->comment ?: '',
                    $absence->creator?->name ?: '',
                    $absence->created_at?->format('Y-m-d H:i'),
                ], ';');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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

    private function filteredAbsencesQuery(Request $request)
    {
        $query = Absence::with(['user', 'creator']);

        if ($request->filled('employee')) {
            $query->where('employee_name', 'like', '%' . $request->employee . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('absence_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('absence_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('absence_date', '<=', $request->date_to);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        return $query;
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