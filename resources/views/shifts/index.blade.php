<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shifts
            </h2>

            @can('manage-master-data')
                <a href="{{ route('shifts.create') }}"
                   style="background:#2563eb;color:white;padding:8px 14px;border-radius:6px;">
                    Add Shift
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div style="background:#dcfce7;color:#166534;padding:12px;margin-bottom:15px;border-radius:6px;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #ddd;">
                            <th style="text-align:left;padding:8px;">Code</th>
                            <th style="text-align:left;padding:8px;">Name</th>
                            <th style="text-align:left;padding:8px;">Start Time</th>
                            <th style="text-align:left;padding:8px;">End Time</th>
                            <th style="text-align:left;padding:8px;">Status</th>

                            @can('manage-master-data')
                                <th style="text-align:right;padding:8px;">Actions</th>
                            @endcan
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($shifts as $shift)
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:8px;">{{ $shift->code }}</td>
                                <td style="padding:8px;">{{ $shift->name }}</td>
                                <td style="padding:8px;">{{ $shift->start_time }}</td>
                                <td style="padding:8px;">{{ $shift->end_time }}</td>
                                <td style="padding:8px;">{{ $shift->is_active ? 'Active' : 'Inactive' }}</td>

                                @can('manage-master-data')
                                    <td style="padding:8px;text-align:right;">
                                        <a href="{{ route('shifts.edit', $shift) }}">Edit</a>

                                        <form action="{{ route('shifts.destroy', $shift) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this shift?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="color:red;margin-left:10px;">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding:12px;text-align:center;">
                                    No shifts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top:15px;">
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>