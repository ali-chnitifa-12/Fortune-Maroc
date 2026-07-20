<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()?->canViewAbsences()) {
            abort(403);
        }

        $query = Absence::with(['user'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $absences = $query->paginate(20)->withQueryString();
        $users    = User::where('is_active', true)->orderBy('name')->get();

        return view('absences.index', [
            'absences' => $absences,
            'users'    => $users,
            'types'    => Absence::$types,
            'statuts'  => Absence::$statuts,
            'filters'  => $request->only(['user_id', 'type', 'statut', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        if (!auth()->user()?->canManageAbsences()) {
            abort(403);
        }

        return view('absences.create', [
            'users'   => User::where('is_active', true)->orderBy('name')->get(),
            'types'   => Absence::$types,
            'statuts' => Absence::$statuts,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()?->canManageAbsences()) {
            abort(403);
        }

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date'    => ['required', 'date'],
            'type'    => ['required', 'in:' . implode(',', array_keys(Absence::$types))],
            'motif'   => ['nullable', 'string', 'max:255'],
            'statut'  => ['required', 'in:' . implode(',', array_keys(Absence::$statuts))],
            'notes'   => ['nullable', 'string'],
        ]);

        $data['created_by'] = auth()->id();

        Absence::create($data);

        return redirect()->route('absences.index')
            ->with('success', 'Absence enregistrée avec succès.');
    }

    public function edit(Absence $absence)
    {
        if (!auth()->user()?->canManageAbsences()) {
            abort(403);
        }

        return view('absences.edit', [
            'absence' => $absence,
            'users'   => User::where('is_active', true)->orderBy('name')->get(),
            'types'   => Absence::$types,
            'statuts' => Absence::$statuts,
        ]);
    }

    public function update(Request $request, Absence $absence)
    {
        if (!auth()->user()?->canManageAbsences()) {
            abort(403);
        }

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date'    => ['required', 'date'],
            'type'    => ['required', 'in:' . implode(',', array_keys(Absence::$types))],
            'motif'   => ['nullable', 'string', 'max:255'],
            'statut'  => ['required', 'in:' . implode(',', array_keys(Absence::$statuts))],
            'notes'   => ['nullable', 'string'],
        ]);

        $absence->update($data);

        return redirect()->route('absences.index')
            ->with('success', 'Absence mise à jour avec succès.');
    }

    public function destroy(Absence $absence)
    {
        if (!auth()->user()?->canManageAbsences()) {
            abort(403);
        }

        $absence->delete();

        return redirect()->route('absences.index')
            ->with('success', 'Absence supprimée.');
    }
}
