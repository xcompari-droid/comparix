@extends('layouts.main')

@section('title', $product->name . ' - Comparix')

@section('content')
<div class="min-h-screen bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 py-6">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1 text-sm mb-6">
            <a href="/" class="text-neutral-600 hover:text-neutral-900">Acasă</a>
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="/categorii" class="text-neutral-600 hover:text-neutral-900">Categorii</a>
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <a href="{{ route('categories.show', $product->productType->category->slug) }}" class="text-neutral-600 hover:text-neutral-900">
                {{ $product->productType->category->name }}
            </a>
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-neutral-900 font-medium">{{ $product->name }}</span>
        </nav>

        {{-- Main Grid --}}
        <div class="grid lg:grid-cols-2 gap-6 mb-8">
            {{-- Product Image --}}
            <div>
                <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8">
                    @if($product->image_url)
                        <div class="w-full max-w-lg mx-auto">
                            <img src="{{ $product->image_url }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-auto object-contain">
                        </div>
                    @else
                        <div class="aspect-square flex items-center justify-center bg-neutral-100 rounded-2xl">
                            <svg class="w-32 h-32 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Product Info --}}
            <div class="space-y-6">
                {{-- Title & Category Badge --}}
                <div class="bg-white rounded-3xl shadow-sm p-6">
                    <div class="mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $product->productType->name }}
                        </span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-neutral-900 mb-4">
                        {{ $product->name }}
                    </h1>
                    
                    @if($product->offers->isNotEmpty())
                        @php
                            $bestOffer = $product->offers->sortBy('price')->first();
                        @endphp
                        <div class="flex items-baseline gap-3 mb-6">
                            <span class="text-3xl lg:text-4xl font-bold text-neutral-900">
                                {{ number_format($bestOffer->price, 2) }} RON
                            </span>
                            <span class="text-xs lg:text-sm text-neutral-500">
                                cel mai mic preț
                            </span>
                        </div>
                    @endif

                    {{-- Key Specifications Grid --}}
                    @if($product->specValues->isNotEmpty())
                        <div class="grid grid-cols-2 gap-4 pt-6 border-t border-neutral-200">
                            @php
                                $keySpecs = $product->specValues->take(4);
                            @endphp
                            @foreach($keySpecs as $specValue)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">
                                        {{ $specValue->specKey->name }}
                                    </dt>
                                    <dd class="text-sm font-semibold text-neutral-900">
                                        {{ $specValue->value }}
                                    </dd>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Compare Button --}}
                <a href="/compara?produse[]={{ $product->id }}" 
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-3 lg:py-4 px-6 rounded-2xl shadow-lg shadow-blue-600/20 hover:shadow-xl hover:shadow-blue-600/30 transition-all duration-200">
                    Compară cu alte produse
                </a>
            </div>
        </div>

        {{-- Offers Section --}}
        @if($product->offers->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8 mb-8">
                <h2 class="text-xl lg:text-2xl font-bold text-neutral-900 mb-6">
                    Oferte disponibile ({{ $product->offers->count() }})
                </h2>
                <div class="space-y-4">
                    @foreach($product->offers->sortBy('price') as $offer)
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between p-4 border border-neutral-200 rounded-2xl hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-200 group gap-4">
                            <div class="flex-1">
                                <div class="font-semibold text-neutral-900 mb-1">
                                    {{ $offer->merchant_name }}
                                </div>
                                @if($offer->merchant_url)
                                    <a href="{{ $offer->merchant_url }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="text-sm text-neutral-500 hover:text-blue-600 transition-colors">
                                        {{ parse_url($offer->merchant_url, PHP_URL_HOST) }}
                                    </a>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 lg:gap-6">
                                <div class="text-left lg:text-right">
                                    <div class="text-xl lg:text-2xl font-bold text-neutral-900">
                                        {{ number_format($offer->price, 2) }} RON
                                    </div>
                                    @if($offer->shipping_price > 0)
                                        <div class="text-xs text-neutral-500">
                                            + {{ number_format($offer->shipping_price, 2) }} RON livrare
                                        </div>
                                    @else
                                        <div class="text-xs text-green-600 font-medium">
                                            Livrare gratuită
                                        </div>
                                    @endif
                                </div>
                                @if($offer->offer_url)
                                    <a href="{{ $offer->offer_url }}" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 lg:px-6 py-2 lg:py-3 rounded-xl transition-colors group-hover:shadow-md text-sm lg:text-base whitespace-nowrap">
                                        Vezi oferta
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Full Specifications --}}
        @if($product->specValues->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8 mb-8">
                <h2 class="text-xl lg:text-2xl font-bold text-neutral-900 mb-6">
                    Specificații complete
                </h2>
                <div class="overflow-hidden rounded-2xl border border-neutral-200">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($product->specValues as $specValue)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-neutral-900 w-1/3 bg-neutral-50/50">
                                        {{ $specValue->specKey->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-neutral-700">
                                        {{ $specValue->value }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Similar Products --}}
        @if($similarProducts->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm p-6 lg:p-8">
                <h2 class="text-xl lg:text-2xl font-bold text-neutral-900 mb-6">
                    Produse similare
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
                    @foreach($similarProducts as $similar)
                        <a href="{{ route('products.show', $similar->id) }}" 
                           class="group block bg-white border border-neutral-200 rounded-2xl p-6 hover:border-blue-300 hover:shadow-lg transition-all duration-200">
                            @if($similar->image_url)
                                <div class="aspect-[4/3] mb-4 bg-neutral-50 rounded-xl overflow-hidden">
                                    <img src="{{ $similar->image_url }}" 
                                         alt="{{ $similar->name }}" 
                                         class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-200">
                                </div>
                            @else
                                <div class="aspect-[4/3] mb-4 bg-neutral-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-16 h-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <h3 class="font-semibold text-neutral-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                                {{ $similar->name }}
                            </h3>
                            
                            @if($similar->offers->isNotEmpty())
                                @php
                                    $bestPrice = $similar->offers->sortBy('price')->first();
                                @endphp
                                <p class="text-xl font-bold text-neutral-900">
                                    {{ number_format($bestPrice->price, 2) }} RON
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
