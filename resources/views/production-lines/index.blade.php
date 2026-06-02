<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">Production Lines</h2>
                <div class="erp-page-subtitle">Manage lines and assigned products.</div>
            </div>

            @if(auth()->user()->canManageMasterData())
                <a href="{{ route('production-lines.create') }}" class="erp-btn erp-btn-primary">Add Production Line</a>
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
            <div class="erp-result-title">Results: {{ $productionLines->total() }}</div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Zone</th>
                            <th>Machines</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($productionLines as $line)
                            <tr>
                                <td><strong>{{ $line->code }}</strong></td>
                                <td>{{ $line->name }}</td>
                                <td>{{ $line->zone?->code ?? '-' }}</td>
                                <td>{{ $line->machines_count }}</td>
                                <td>{{ $line->products_count }}</td>
                                <td>
                                    @if($line->is_active)
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(auth()->user()->canManageMasterData())
                                        <a href="{{ route('production-lines.edit', $line) }}" class="erp-link">Edit</a>

                                        <form method="POST"
                                              action="{{ route('production-lines.destroy', $line) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this production line?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-delete-link">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">No production lines found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">{{ $productionLines->links() }}</div>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>