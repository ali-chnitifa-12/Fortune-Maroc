<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with([
                'zone',
                'productionLine',
                'assignedZones',
            ])
            ->orderBy('name')
            ->get();

        return view('users-management.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('users-management.create', [
            'user' => new User(),
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::with('zone')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
            'selectedZones' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateUser($request);

        $role = strtolower(trim($data['role']));

        if ($role === 'operator' && empty($data['production_line_id'])) {
            return back()->withInput()->withErrors([
                'production_line_id' => 'Production line is mandatory for operator.',
            ]);
        }

        if ($role === 'supervisor' && empty($data['zone_ids'])) {
            return back()->withInput()->withErrors([
                'zone_ids' => 'At least one zone is mandatory for supervisor.',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'zone_id' => null,
            'production_line_id' => $role === 'operator' ? $data['production_line_id'] : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($role === 'supervisor') {
            $user->assignedZones()->sync($data['zone_ids'] ?? []);
            $firstZoneId = collect($data['zone_ids'] ?? [])->first();

            $user->update([
                'zone_id' => $firstZoneId ?: null,
                'production_line_id' => null,
            ]);
        }

        if (in_array($role, ['responsable_production', 'admin'], true)) {
            $user->assignedZones()->sync([]);
            $user->update([
                'zone_id' => null,
                'production_line_id' => null,
            ]);
        }

        return redirect()->route('users-management.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users-management.edit', [
            'user' => $user->load(['assignedZones', 'productionLine']),
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::with('zone')
                ->where('is_active', true)
                ->orderBy('code')
                ->get(),
            'selectedZones' => $user->assignedZones()->pluck('zones.id')->toArray(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validateUser($request, $user);

        $role = strtolower(trim($data['role']));

        if ($role === 'operator' && empty($data['production_line_id'])) {
            return back()->withInput()->withErrors([
                'production_line_id' => 'Production line is mandatory for operator.',
            ]);
        }

        if ($role === 'supervisor' && empty($data['zone_ids'])) {
            return back()->withInput()->withErrors([
                'zone_ids' => 'At least one zone is mandatory for supervisor.',
            ]);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($role === 'operator') {
            $payload['production_line_id'] = $data['production_line_id'];
            $payload['zone_id'] = null;
        }

        if ($role === 'supervisor') {
            $payload['production_line_id'] = null;
            $payload['zone_id'] = collect($data['zone_ids'] ?? [])->first() ?: null;
        }

        if (in_array($role, ['responsable_production', 'admin'], true)) {
            $payload['production_line_id'] = null;
            $payload['zone_id'] = null;
        }

        $user->update($payload);

        if ($role === 'supervisor') {
            $user->assignedZones()->sync($data['zone_ids'] ?? []);
        } else {
            $user->assignedZones()->sync([]);
        }

        return redirect()->route('users-management.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own user.',
            ]);
        }

        $user->assignedZones()->detach();
        $user->delete();

        return redirect()->route('users-management.index')
            ->with('success', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId = $user?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'min:6',
            ],
            'role' => [
                'required',
                Rule::in([
                    'operator',
                    'responsable_production',
                    'supervisor',
                    'admin',
                ]),
            ],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['nullable', 'exists:zones,id'],
            'production_line_id' => ['nullable', 'exists:production_lines,id'],
            'is_active' => ['nullable'],
        ]);
    }
}