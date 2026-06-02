<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Products
            </h2>

            @can('manage-master-data')
                <a href="{{ route('products.create') }}"
                   style="background:#2563eb;color:white;padding:8px 14px;border-radius:6px;">
                    Add Product
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
                            <th style="text-align:left;padding:8px;">Unit</th>
                            <th style="text-align:left;padding:8px;">Std Qty / Hour</th>
                            <th style="text-align:left;padding:8px;">Status</th>

                            @can('manage-master-data')
                                <th style="text-align:right;padding:8px;">Actions</th>
                            @endcan
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $product)
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:8px;">{{ $product->code }}</td>
                                <td style="padding:8px;">{{ $product->name }}</td>
                                <td style="padding:8px;">{{ $product->unit }}</td>
                                <td style="padding:8px;">{{ $product->standard_qty_per_hour }}</td>
                                <td style="padding:8px;">{{ $product->is_active ? 'Active' : 'Inactive' }}</td>

                                @can('manage-master-data')
                                    <td style="padding:8px;text-align:right;">
                                        <a href="{{ route('products.edit', $product) }}">Edit</a>

                                        <form action="{{ route('products.destroy', $product) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this product?')">
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
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top:15px;">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>