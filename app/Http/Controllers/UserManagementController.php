<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private array $roles = [
        'admin' => 'Admin',
        'responsable_production' => 'Responsable Production',
        'supervisor' => 'Supervisor',
        'operator' => 'Operator',
        'rh' => 'RH',
    ];

    public function index(Request $request)
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
        ];

        $query = User::query()
            ->with([
                'zone',
                'productionLine.zone',
                'assignedZones',
            ])
            ->orderBy('name');

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['role']) && array_key_exists($filters['role'], $this->roles)) {
            $query->where('role', $filters['role']);
        }

        if ($filters['status'] !== null && $filters['status'] !== '') {
            $query->where('is_active', (int) $filters['status'] === 1);
        }

        $users = $query
            ->paginate(20)
            ->withQueryString();

        return view('users-management.index', [
            'users' => $users,
            'roles' => $this->roles,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        return view('users-management.create', [
            'roles' => $this->roles,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::with('zone')->where('is_active', true)->orderBy('code')->get(),
            'selectedZones' => [],
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        $data = $this->validateUserData($request);

        DB::transaction(function () use ($data) {
            $accessPayload = $this->buildAccessPayload($data);

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'zone_id' => $accessPayload['zone_id'],
                'production_line_id' => $accessPayload['production_line_id'],
            ];

            if (Schema::hasColumn('users', 'is_active')) {
                $userData['is_active'] = $accessPayload['is_active'];
            }

            $user = User::create($userData);

            $this->syncSupervisorZones($user, $data);
        });

        return redirect()
            ->route('users-management.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($users_management)
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        $managedUser = User::query()
            ->with([
                'zone',
                'productionLine.zone',
                'assignedZones',
            ])
            ->findOrFail((int) $users_management);

        return view('users-management.edit', [
            'managedUser' => $managedUser,
            'user' => $managedUser,
            'roles' => $this->roles,
            'zones' => Zone::where('is_active', true)->orderBy('code')->get(),
            'productionLines' => ProductionLine::with('zone')->where('is_active', true)->orderBy('code')->get(),
            'selectedZones' => $this->selectedZoneIds($managedUser),
        ]);
    }

    public function update(Request $request, $users_management)
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        $managedUser = User::query()->findOrFail((int) $users_management);

        $data = $this->validateUserData($request, $managedUser);

        DB::transaction(function () use ($managedUser, $data) {
            $accessPayload = $this->buildAccessPayload($data);

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $data['role'],
                'zone_id' => $accessPayload['zone_id'],
                'production_line_id' => $accessPayload['production_line_id'],
            ];

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $userData['is_active'] = $accessPayload['is_active'];
            }

            $managedUser->update($userData);

            $this->syncSupervisorZones($managedUser, $data);
        });

        return redirect()
            ->route('users-management.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy($users_management)
    {
        if (!auth()->user()?->canManageUsers()) {
            abort(403);
        }

        $managedUser = User::query()->findOrFail((int) $users_management);

        if ((int) $managedUser->id === (int) auth()->id()) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        DB::transaction(function () use ($managedUser) {
            if (method_exists($managedUser, 'assignedZones')) {
                $managedUser->assignedZones()->sync([]);
            }

            $managedUser->delete();
        });

        return redirect()
            ->route('users-management.index')
            ->with('success', 'User deleted successfully.');
    }

    private function validateUserData(Request $request, ?User $managedUser = null): array
    {
        $isUpdate = $managedUser !== null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($managedUser?->id),
            ],
            'role' => ['required', Rule::in(array_keys($this->roles))],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'production_line_id' => ['nullable', 'integer', 'exists:production_lines,id'],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['integer', 'exists:zones,id'],
        ];

        if ($isUpdate) {
            $rules['password'] = ['nullable', 'string', 'min:6', 'confirmed'];
        } else {
            $rules['password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $data = $request->validate($rules);

        if (($data['role'] ?? null) === 'operator' && empty($data['production_line_id'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'production_line_id' => 'Production line is required for operator users.',
                ])
                ->throwResponse();
        }

        if (($data['role'] ?? null) === 'supervisor' && empty($data['zone_ids'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'zone_ids' => 'At least one zone is required for supervisor users.',
                ])
                ->throwResponse();
        }

        return $data;
    }

    private function buildAccessPayload(array $data): array
    {
        $role = $data['role'];
        $isActive = (string) ($data['is_active'] ?? '1') === '1';

        $payload = [
            'zone_id' => null,
            'production_line_id' => null,
            'is_active' => $isActive,
        ];

        if ($role === 'operator') {
            $line = ProductionLine::query()->findOrFail((int) $data['production_line_id']);

            $payload['production_line_id'] = (int) $line->id;
            $payload['zone_id'] = $line->zone_id ? (int) $line->zone_id : null;

            return $payload;
        }

        if ($role === 'supervisor') {
            $zoneIds = collect($data['zone_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

            $payload['zone_id'] = $zoneIds[0] ?? null;
            $payload['production_line_id'] = null;

            return $payload;
        }

        if (in_array($role, ['admin', 'responsable_production', 'rh'], true)) {
            $payload['zone_id'] = null;
            $payload['production_line_id'] = null;

            return $payload;
        }

        return $payload;
    }

    private function selectedZoneIds(User $user): array
    {
        if (method_exists($user, 'assignedZones')) {
            $zoneIds = $user->assignedZones()
                ->pluck('zones.id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            if (!empty($zoneIds)) {
                return array_values(array_unique($zoneIds));
            }
        }

        if ($user->zone_id) {
            return [(int) $user->zone_id];
        }

        return [];
    }

    private function syncSupervisorZones(User $user, array $data): void
    {
        if (!method_exists($user, 'assignedZones')) {
            return;
        }

        if (($data['role'] ?? null) !== 'supervisor') {
            $user->assignedZones()->sync([]);
            return;
        }

        $zoneIds = collect($data['zone_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        $user->assignedZones()->sync($zoneIds);
    }
}