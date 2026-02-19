@extends('admin.layout')

@section('title', 'Exhibitor Products')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Exhibitor Products</h1>
            <p class="text-gray-600 mt-1">Review and manage products from all exhibitors</p>
        </div>
        <a href="{{ route('admin.exhibitors.index') }}" 
           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            Back to Exhibitors
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-6">
    <div class="flex space-x-2 border-b border-gray-200">
        <a href="{{ route('admin.exhibitor-products.index', ['status' => 'all']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'all' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
            All Products
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $totalCount }}
            </span>
        </a>
        <a href="{{ route('admin.exhibitor-products.index', ['status' => 'active']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'active' ? 'text-green-600 border-b-2 border-green-600' : 'text-gray-600 hover:text-gray-900' }}">
            Active
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $activeCount }}
            </span>
        </a>
        <a href="{{ route('admin.exhibitor-products.index', ['status' => 'inactive']) }}" 
           class="px-4 py-2 font-medium text-sm transition {{ $status === 'inactive' ? 'text-gray-600 border-b-2 border-gray-600' : 'text-gray-600 hover:text-gray-900' }}">
            Inactive
            <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $status === 'inactive' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $inactiveCount }}
            </span>
        </a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Total Products</p>
        <p class="text-2xl font-bold text-gray-900">{{ $products->total() }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Active</p>
        <p class="text-2xl font-bold text-green-600">{{ $activeCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Inactive</p>
        <p class="text-2xl font-bold text-gray-600">{{ $inactiveCount }}</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-4">
        <p class="text-sm text-gray-600">Featured</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $products->where('is_featured', true)->count() }}</p>
    </div>
</div>

@if($products->isEmpty())
    <div class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No Products Yet</h3>
        <p class="text-gray-600">Products added by exhibitors will appear here for review.</p>
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exhibitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-12 h-12 object-cover rounded mr-3">
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                @if($product->is_featured)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                        Featured
                                    </span>
                                @endif
                                @if($product->is_new)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        New
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.exhibitors.show', $product->exhibitor) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-900">
                            {{ $product->exhibitor->company_name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $product->category ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->price_text)
                            <div class="text-sm text-gray-900">{{ $product->price_text }}</div>
                        @elseif($product->price)
                            <div class="text-sm text-gray-900">${{ number_format($product->price, 2) }}</div>
                        @else
                            <div class="text-sm text-gray-400">-</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-xs text-gray-600">
                            <div>👁 {{ $product->views_count }} views</div>
                            <div>💬 {{ $product->inquiries_count }} inquiries</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.exhibitor-products.toggle-active', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="focus:outline-none">
                                @if($product->is_active)
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition">
                                        Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200 transition">
                                        Inactive
                                    </span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('admin.exhibitors.show', $product->exhibitor) }}" 
                               class="text-indigo-600 hover:text-indigo-900 transition" 
                               title="View Exhibitor">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('admin.exhibitor-products.destroy', $product) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900 transition" 
                                        title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->appends(['status' => $status])->links() }}
    </div>
@endif
@endsection
