<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Zones</h2>
                <div class="erp-page-subtitle">Manage production zones.</div>
            </div>

            @if(auth()->user()->canManageMasterData())
                <a href="{{ route('zones.create') }}" class="erp-btn erp-btn-primary">Add Zone</a>
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
            <div class="erp-result-title">Results: {{ $zones->total() }}</div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Lines</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($zones as $zone)
                            <tr>
                                <td><strong>{{ $zone->code }}</strong></td>
                                <td>{{ $zone->name }}</td>
                                <td>{{ $zone->description }}</td>
                                <td>{{ $zone->production_lines_count }}</td>
                                <td>
                                    @if($zone->is_active)
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(auth()->user()->canManageMasterData())
                                        <a href="{{ route('zones.edit', $zone) }}" class="erp-link">Edit</a>

                                        <form method="POST"
                                              action="{{ route('zones.destroy', $zone) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this zone?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">No zones found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">{{ $zones->links() }}</div>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>