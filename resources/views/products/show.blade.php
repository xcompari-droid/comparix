@extends('layouts.main')

@section('title', $product->name . ' - Comparix')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex text-sm text-gray-500">
            <a href="/" class="hover:text-blue-600">Acasă</a>
            <span class="mx-2">/</span>
            <a href="{{ route('categories.index') }}" class="hover:text-blue-600">Categorii</a>
            <span class="mx-2">/</span>
            <a href="{{ route('categories.show', $product->productType->category->slug) }}" class="hover:text-blue-600">
                {{ $product->productType->category->name }}
            </a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $product->name }}</span>
        </nav>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Product Info - Left Side (2 cols) -->
        <div class="lg:col-span-2">
            <!-- Product Image -->
            @if($product->image_url)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <img src="{{ $product->image_url }}" 
                         alt="{{ $product->name }}" 
                         class="w-full max-w-md mx-auto h-auto object-contain">
                </div>
            @endif

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>
            
            @if($product->description)
                <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-3">Descriere</h2>
                    <p class="text-gray-600">{{ $product->description }}</p>
                </div>
            @endif

            <!-- Specifications -->
            @if($product->specValues->isNotEmpty())
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Specificații</h2>
                    <div class="space-y-3">
                        @foreach($product->specValues as $specValue)
                            <div class="flex border-b border-gray-200 pb-3">
                                <dt class="text-gray-600 font-medium w-1/3">
                                    {{ $specValue->specKey->name }}
                                </dt>
                                <dd class="text-gray-900 w-2/3">
                                    {{ $specValue->value }}
                                </dd>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Offers - Right Side -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                <h2 class="text-xl font-semibold mb-4">
                    Oferte disponibile
                    <span class="text-sm font-normal text-gray-500">({{ $product->offers->count() }})</span>
                </h2>

                @if($product->offers->isEmpty())
                    <p class="text-gray-500 text-center py-8">Nu există oferte disponibile momentan.</p>
                @else
                    <div class="space-y-4">
                        @foreach($product->offers as $offer)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $offer->merchant }}</h3>
                                        <div class="text-2xl font-bold text-blue-600 mt-1">
                                            {{ number_format($offer->price, 2) }} RON
                                        </div>
                                    </div>
                                    @if($loop->first)
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                            Cel mai mic preț
                                        </span>
                                    @endif
                                </div>

                                @if($offer->shipping_cost > 0)
                                    <p class="text-sm text-gray-500 mb-2">
                                        + {{ number_format($offer->shipping_cost, 2) }} RON transport
                                    </p>
                                @else
                                    <p class="text-sm text-green-600 mb-2">
                                        ✓ Transport gratuit
                                    </p>
                                @endif

                                <a href="{{ route('offer.redirect', $offer->id) }}" 
                                   target="_blank"
                                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 px-4 rounded transition">
                                    Vezi oferta →
                                </a>

                                @if($offer->in_stock)
                                    <p class="text-xs text-green-600 text-center mt-2">✓ În stoc</p>
                                @else
                                    <p class="text-xs text-gray-500 text-center mt-2">Disponibilitate limitată</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Price Statistics -->
                    @if($product->offers->count() > 1)
                        @php
                            $minPrice = $product->offers->min('price');
                            $maxPrice = $product->offers->max('price');
                            $avgPrice = $product->offers->avg('price');
                            $savings = $maxPrice - $minPrice;
                        @endphp
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="text-sm space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Preț minim:</span>
                                    <span class="font-semibold">{{ number_format($minPrice, 2) }} RON</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Preț mediu:</span>
                                    <span class="font-semibold">{{ number_format($avgPrice, 2) }} RON</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Preț maxim:</span>
                                    <span class="font-semibold">{{ number_format($maxPrice, 2) }} RON</span>
                                </div>
                                @if($savings > 0)
                                    <div class="flex justify-between text-green-600 pt-2 border-t">
                                        <span class="font-semibold">Economisești până la:</span>
                                        <span class="font-bold">{{ number_format($savings, 2) }} RON</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Similar Products -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Produse similare</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @php
                $similarProducts = $product->productType->products()
                    ->where('id', '!=', $product->id)
                    ->with(['offers' => function($query) {
                        $query->where('in_stock', true)->orderBy('price', 'asc');
                    }])
                    ->limit(4)
                    ->get();
            @endphp

            @foreach($similarProducts as $similar)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-4">
                    <h3 class="font-semibold mb-2">
                        <a href="{{ route('products.show', $similar->id) }}" class="hover:text-blue-600">
                            {{ $similar->name }}
                        </a>
                    </h3>
                    @if($similar->offers->isNotEmpty())
                        <div class="text-xl font-bold text-blue-600 mb-2">
                            de la {{ number_format($similar->offers->first()->price, 2) }} RON
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $similar->offers->count() }} {{ $similar->offers->count() === 1 ? 'ofertă' : 'oferte' }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
