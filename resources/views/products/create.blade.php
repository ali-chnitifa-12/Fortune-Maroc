<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Product
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div>
                        <label>Code</label>
                        <input name="code" value="{{ old('code') }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('code') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>Name</label>
                        <input name="name" value="{{ old('name') }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('name') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>Unit</label>
                        <input name="unit" value="{{ old('unit', 'KG') }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('unit') <div style="color:red;">{{ $message }}</div> @enderror
                    </div>

                    <div style="margin-top:15px;">
                        <label>Standard Quantity Per Hour</label>
                        <input type="number" step="0.01" name="standard_qty_per_hour"
                               value="{{ old('standard_qty_per_hour', 0) }}" required
                               style="width:100%;border:1px solid #ccc;border-radius:6px;padding:8px;">
                        @error('standard_qty_per_hour') <div style="color:red;">{{ $message }}</div> @enderror
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

                        <a href="{{ route('products.index') }}" style="margin-left:10px;">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
