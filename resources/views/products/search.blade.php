@extends('layouts.main')

@section('title', 'Căutare: ' . $query . ' - compariX.ro')

@section('content')
<div class="bg-gradient-to-b from-gray-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Search Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-gray-900">
                    Rezultate pentru "<span class="text-cyan-600">{{ $query }}</span>"
                </h1>
                <span class="text-sm text-gray-500">
                    {{ $products->total() }} {{ $products->total() == 1 ? 'produs' : 'produse' }}
                </span>
            </div>
            
            <!-- Search Bar -->
            <form action="/cautare" method="GET" class="max-w-2xl">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Caută produse..." 
                        class="w-full px-4 py-3 pr-24 rounded-lg border-2 border-gray-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 outline-none transition-all"
                        value="{{ $query }}"
                    >
                    <button 
                        type="submit"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-6 py-2 bg-gradient-to-r from-cyan-500 to-emerald-400 text-white font-semibold rounded-md hover:from-cyan-600 hover:to-emerald-500 transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        @if($products->isEmpty())
            <!-- No Results -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">Nu am găsit rezultate</h2>
                <p class="text-gray-500 mb-6">Încercă să cauți ceva diferit sau explorează categoriile</p>
                <a href="/categorii" class="inline-flex items-center px-6 py-3 bg-cyan-500 text-white rounded-lg hover:bg-cyan-600 transition-colors">
                    Vezi toate categoriile
                </a>
            </div>
        @else
            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
                        <a href="/produse/{{ $product->id }}" class="block">
                            <!-- Image -->
                            <div class="relative bg-gray-50 aspect-square flex items-center justify-center overflow-hidden">
                                <img 
                                    src="{{ $product->image_url }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy"
                                >
                                @if($product->score >= 80)
                                    <div class="absolute top-3 right-3 bg-emerald-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        Top {{ number_format($product->score, 0) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Content -->
                            <div class="p-4">
                                <!-- Brand -->
                                <div class="text-xs font-semibold text-cyan-600 uppercase tracking-wide mb-1">
                                    {{ $product->brand }}
                                </div>
                                
                                <!-- Name -->
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 min-h-[2.5rem]">
                                    {{ $product->name }}
                                </h3>
                                
                                <!-- Category -->
                                @if($product->productType?->category)
                                    <div class="text-xs text-gray-500 mb-3">
                                        {{ $product->productType->category->name }}
                                    </div>
                                @endif
                                
                                <!-- Price -->
                                @php
                                    $minOffer = $product->offers->first();
                                @endphp
                                @if($minOffer)
                                    <div class="flex items-baseline justify-between">
                                        <div>
                                            <span class="text-2xl font-bold text-gray-900">
                                                {{ format_number($minOffer->price) }}
                                            </span>
                                            <span class="text-sm text-gray-600">lei</span>
                                        </div>
                                        <div class="text-xs text-emerald-600 font-medium">
                                            {{ $product->offers->count() }} {{ $product->offers->count() == 1 ? 'ofertă' : 'oferte' }}
                                        </div>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">
                                        Preț indisponibil
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="mt-12">
                    {{ $products->appends(['q' => $query])->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
