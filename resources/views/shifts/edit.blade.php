<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Shift
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('shifts.update', $shift) }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <label>Code</label>
                        <input name="code" value="{{ old('code', $shift->code) }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('code') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>Name</label>
                        <input name="name" value="{{ old('name', $shift->name) }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('name') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>Start Time</label>
                        <input type="time" name="start_time" value="{{ old('start_time', substr($shift->start_time, 0, 5)) }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('start_time') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>End Time</label>
                        <input type="time" name="end_time" value="{{ old('end_time', substr($shift->end_time, 0, 5)) }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('end_time') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>
                            <input type="checkbox" name="is_active" value="1"
                                   {{ $shift->is_active ? 'checked' : '' }}>
                            Active
                        </label>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit"
                                style="background:#2563eb;color:white;padding:8px 14px;border-radius:6px;">
                            Update
                        </button>

                        <a href="{{ route('shifts.index') }}" style="margin-left:10px;">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
