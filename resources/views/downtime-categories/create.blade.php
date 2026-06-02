<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Downtime Category
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('downtime-categories.store') }}">
                    @csrf

                    <div>
                        <label>Name</label>
                        <input name="name" value="{{ old('name') }}" required
                               placeholder="Mechanical"
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('name') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>
                            <input type="checkbox" name="is_active" value="1" checked>
                            Active
                        </label>
                    </div>

                    <div style="margin-top:20px;">
                        <button type="submit"
                                style="background:#2563eb;color:white;padding:8px 14px;border-radius:6px;">
                            Save
                        </button>

                        <a href="{{ route('downtime-categories.index') }}" style="margin-left:10px;">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
