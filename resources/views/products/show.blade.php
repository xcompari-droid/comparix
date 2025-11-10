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
                                {{ format_number($bestOffer->price) }} RON
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
                                        @if(is_numeric($specValue->value))
                                            {{ format_number($specValue->value) }}
                                        @else
                                            {{ $specValue->value }}
                                        @endif
                                        @if($specValue->specKey->unit)
                                            <span class="text-xs text-neutral-500 font-normal">{{ $specValue->specKey->unit }}</span>
                                        @endif
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

                @auth
                {{-- Favorite & Price Alert Buttons --}}
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="toggleFavorite({{ $product->id }})" 
                            id="favorite-btn"
                            class="flex items-center justify-center gap-2 bg-white hover:bg-neutral-50 border-2 border-neutral-200 hover:border-red-300 text-neutral-700 hover:text-red-600 font-semibold py-3 px-4 rounded-2xl transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="heart-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span id="favorite-text">Favorite</span>
                    </button>
                    
                    <button onclick="showPriceAlert()" 
                            class="flex items-center justify-center gap-2 bg-white hover:bg-neutral-50 border-2 border-neutral-200 hover:border-blue-300 text-neutral-700 hover:text-blue-600 font-semibold py-3 px-4 rounded-2xl transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span>Alertă preț</span>
                    </button>
                </div>
                @endauth
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
                                        {{ format_number($offer->price) }} RON
                                    </div>
                                    @if($offer->shipping_price > 0)
                                        <div class="text-xs text-neutral-500">
                                            + {{ format_number($offer->shipping_price) }} RON livrare
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
                                        @if(is_numeric($specValue->value))
                                            {{ format_number($specValue->value) }}
                                        @else
                                            {{ $specValue->value }}
                                        @endif
                                        @if($specValue->specKey->unit)
                                            <span class="text-neutral-500">{{ $specValue->specKey->unit }}</span>
                                        @endif
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
                                    {{ format_number($bestPrice->price) }} RON
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@auth
{{-- Price Alert Modal --}}
<div id="price-alert-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-neutral-900">Alertă de preț</h3>
            <button onclick="hidePriceAlert()" class="text-neutral-400 hover:text-neutral-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <p class="text-neutral-600 mb-6">
            Vei primi o notificare prin email când prețul produsului scade sub suma dorită.
        </p>
        
        <form action="{{ route('price-alerts.store', $product->id) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="target_price" class="block text-sm font-medium text-neutral-700 mb-2">
                    Preț dorit (RON)
                </label>
                <input type="number" 
                       name="target_price" 
                       id="target_price" 
                       step="0.01" 
                       min="0"
                       @if($product->offers->isNotEmpty())
                       value="{{ $product->offers->sortBy('price')->first()->price }}"
                       @endif
                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required>
            </div>
            
            <div class="flex gap-3">
                <button type="button" 
                        onclick="hidePriceAlert()"
                        class="flex-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold py-3 px-4 rounded-xl transition-colors">
                    Anulează
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-colors">
                    Creează alertă
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFavorite(productId) {
    fetch(`/produse/${productId}/favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const btn = document.getElementById('favorite-btn');
        const text = document.getElementById('favorite-text');
        const icon = document.getElementById('heart-icon');
        
        if (data.favorited) {
            btn.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
            btn.classList.remove('border-neutral-200');
            icon.setAttribute('fill', 'currentColor');
            text.textContent = 'Favorit';
        } else {
            btn.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
            btn.classList.add('border-neutral-200');
            icon.setAttribute('fill', 'none');
            text.textContent = 'Favorite';
        }
    })
    .catch(error => console.error('Error:', error));
}

function showPriceAlert() {
    document.getElementById('price-alert-modal').classList.remove('hidden');
}

function hidePriceAlert() {
    document.getElementById('price-alert-modal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('price-alert-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hidePriceAlert();
    }
});
</script>
@endauth

@endsection
