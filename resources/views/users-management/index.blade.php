<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">User Management</h2>
                <div class="erp-page-subtitle">
                    Manage user roles and plant hierarchy assignments.
                </div>
            </div>

            <a href="{{ route('users-management.create') }}" class="erp-btn erp-btn-primary">
                Add User
            </a>
        </div>
    </x-slot>

    <div class="erp-page-wrap">
        @if(session('success'))
            <div class="fortune-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="fortune-error">
                <ul style="list-style:disc;margin-left:20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="erp-card">
            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Assignment</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="erp-pill erp-pill-info">
                                        {{ ucwords(str_replace('_', ' ', $user->roleValue())) }}
                                    </span>
                                </td>
                                <td>{{ $user->scopeLabel() }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('users-management.edit', $user) }}" class="erp-link">
                                        Edit
                                    </a>

                                    @if(auth()->id() !== $user->id)
                                        <form method="POST"
                                              action="{{ route('users-management.destroy', $user) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('components.erp-page-style')

    <style>
        .erp-pill-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .erp-pill-muted {
            background: #f1f5f9;
            color: #475569;
        }
    </style>
</x-app-layout>