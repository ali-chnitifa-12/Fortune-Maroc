<x-app-layout>
    <x-slot name="header">
        <div class="erp-page-head">
            <div>
                <h2 class="erp-page-title">ThingsBoard Mappings</h2>
                <div class="erp-page-subtitle">
                    Map production lines and machines to ThingsBoard devices.
                </div>
            </div>

            @if(auth()->user()->canManageMasterData())
                <a href="{{ route('thingsboard-devices.create') }}" class="erp-btn erp-btn-primary">
                    Add Mapping
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
            <div class="erp-result-title">Results: {{ $devices->total() }}</div>

            <div class="erp-responsive-table">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Zone</th>
                            <th>Line</th>
                            <th>Machine</th>
                            <th>ThingsBoard Device</th>
                            <th>Access Token</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td>
                                    @if($device->mapping_type === 'line')
                                        <span class="erp-pill erp-pill-success">Line</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Machine</span>
                                    @endif
                                </td>

                                <td>{{ $device->zone?->code ?? '-' }}</td>
                                <td>{{ $device->productionLine?->code ?? '-' }}</td>
                                <td>{{ $device->machine?->code ?? '-' }}</td>
                                <td><strong>{{ $device->device_name }}</strong></td>

                                <td>
                                    {{ substr($device->access_token, 0, 6) }}********
                                </td>

                                <td>
                                    @if($device->is_active)
                                        <span class="erp-pill erp-pill-success">Active</span>
                                    @else
                                        <span class="erp-pill erp-pill-muted">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    @if(auth()->user()->canManageMasterData())
                                        <a href="{{ route('thingsboard-devices.edit', $device) }}" class="erp-link">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('thingsboard-devices.destroy', $device) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this ThingsBoard mapping?')">
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
                                <td colspan="8" class="erp-empty">
                                    No ThingsBoard mappings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="erp-pagination">
                {{ $devices->links() }}
            </div>
        </div>
    </div>

    @include('components.erp-page-style')
</x-app-layout>