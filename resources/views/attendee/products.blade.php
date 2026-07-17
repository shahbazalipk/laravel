@extends('attendee.layout')

@section('title', 'Products & Services')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Products & Services</h1>
    <p class="text-gray-600 mt-2">Discover products and services from our exhibitors</p>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('attendee.products') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}"
                   placeholder="Product name..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filters</label>
            <div class="flex gap-2">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="featured" 
                           value="1" 
                           {{ request('featured') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700">Featured</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="new" 
                           value="1" 
                           {{ request('new') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2 text-sm text-gray-700">New</span>
                </label>
            </div>
        </div>
        
        <div class="flex items-end gap-2 md:col-span-2">
            <button type="submit" 
                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('attendee.products') }}" 
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Results Count -->
<div class="mb-4">
    <p class="text-sm text-gray-600">
        Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
    </p>
</div>

<!-- Products Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($products as $product)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
            @if($product->image)
                <img src="{{ storage_public_url($product->image) }}" 
                     alt="{{ $product->name }}"
                     class="w-full h-56 object-cover">
            @else
                <div class="w-full h-56 bg-gray-100 flex items-center justify-center">
                    <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            @endif
            
            <div class="p-5">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-lg font-bold text-gray-900 flex-1">{{ $product->name }}</h3>
                    <div class="flex flex-col gap-1 ml-2">
                        @if($product->is_featured)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded whitespace-nowrap">Featured</span>
                        @endif
                        @if($product->is_new)
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded whitespace-nowrap">New</span>
                        @endif
                    </div>
                </div>
                
                <a href="{{ route('attendee.exhibitors.show', $product->exhibitor) }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 mb-3">
                    {{ $product->exhibitor->company_name }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                
                @if($product->category)
                    <p class="text-xs text-gray-500 mb-2">{{ $product->category }}</p>
                @endif
                
                @if($product->price_text)
                    <p class="text-lg font-bold text-indigo-600 mb-3">{{ $product->price_text }}</p>
                @elseif($product->price)
                    <p class="text-lg font-bold text-indigo-600 mb-3">${{ number_format($product->price, 2) }}</p>
                @endif
                
                <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $product->description }}</p>
                
                @if($product->features)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-700 mb-1">Key Features:</p>
                        <p class="text-xs text-gray-600 line-clamp-2">{{ $product->features }}</p>
                    </div>
                @endif
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex gap-3 text-xs text-gray-500">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            {{ $product->views_count }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            {{ $product->inquiries_count }}
                        </span>
                    </div>
                    
                    @if($product->exhibitor->contact_email)
                        <a href="mailto:{{ $product->exhibitor->contact_email }}?subject=Inquiry about {{ $product->name }}" 
                           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition">
                            Inquire
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Products Found</h3>
            <p class="text-gray-600">Try adjusting your filters or check back later for new products.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($products->hasPages())
    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endif
@endsection
