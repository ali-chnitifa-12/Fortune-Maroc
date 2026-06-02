<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Machines</h2>
                <div class="erp-page-subtitle">
                    Manage machines and link each machine to a production line.
                </div>
            </div>

            @if(auth()->user()->canManageMasterData())
                <a href="{{ route('machines.create') }}" class="erp-btn erp-btn-primary">
                    Add Machine
                </a>
            @endif
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
            <div class="erp-result-title">
                Results: {{ $machines->total() }}
            </div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Zone</th>
                            <th>Production Line</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($machines as $machine)
                            <tr>
                                <td><strong>{{ $machine->code }}</strong></td>
                                <td>{{ $machine->name }}</td>
                                <td>{{ $machine->productionLine?->zone?->code ?? '-' }}</td>
                                <td>{{ $machine->productionLine?->code ?? '-' }}</td>
                                <td>{{ $machine->description }}</td>
                                <td>
                                    @if($machine->is_active)
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(auth()->user()->canManageMasterData())
                                        <a href="{{ route('machines.edit', $machine) }}" class="erp-link">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('machines.destroy', $machine) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('If this machine is used in production history, it will be deactivated instead of deleted. Continue?')">
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
                                    No machines found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">
                {{ $machines->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>