@extends('layouts.main')

@section('title', 'Favorite - Comparix')

@section('content')
<div class="min-h-screen bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-neutral-900 mb-2">Produsele mele favorite</h1>
            <p class="text-neutral-600">{{ $favorites->count() }} {{ $favorites->count() === 1 ? 'produs' : 'produse' }} salvat{{ $favorites->count() === 1 ? '' : 'e' }}</p>
        </div>

        @if($favorites->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-3xl shadow-sm p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <h2 class="text-2xl font-bold text-neutral-900 mb-2">Niciun produs favorit</h2>
                <p class="text-neutral-600 mb-6">Salvează produsele tale preferate pentru a le accesa rapid mai târziu.</p>
                <a href="/categorii" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors">
                    Explorează produse
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @else
            {{-- Products Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($favorites as $favorite)
                    @php
                        $product = $favorite->product;
                        $bestOffer = $product->offers->sortBy('price')->first();
                    @endphp
                    
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group">
                        <a href="{{ route('products.show', $product->id) }}" class="block">
                            {{-- Image --}}
                            @if($product->image_url)
                                <div class="aspect-square bg-neutral-50 p-6">
                                    <img src="{{ $product->image_url }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                                </div>
                            @else
                                <div class="aspect-square bg-neutral-100 flex items-center justify-center">
                                    <svg class="w-20 h-20 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </a>

                        <div class="p-6">
                            {{-- Product Name --}}
                            <a href="{{ route('products.show', $product->id) }}">
                                <h3 class="font-semibold text-neutral-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2 min-h-[3rem]">
                                    {{ $product->name }}
                                </h3>
                            </a>

                            {{-- Price --}}
                            @if($bestOffer)
                                <div class="mb-4">
                                    <div class="text-2xl font-bold text-neutral-900">
                                        {{ format_number($bestOffer->price) }} RON
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        {{ $product->offers->count() }} {{ $product->offers->count() === 1 ? 'ofertă' : 'oferte' }}
                                    </div>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="flex gap-2">
                                <a href="{{ route('products.show', $product->id) }}" 
                                   class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-semibold py-2 px-4 rounded-xl transition-colors">
                                    Vezi detalii
                                </a>
                                <button onclick="removeFavorite({{ $product->id }})"
                                        class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-xl transition-colors"
                                        title="Elimină din favorite">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function removeFavorite(productId) {
    if (!confirm('Sigur vrei să elimini acest produs din favorite?')) {
        return;
    }

    fetch(`/produse/${productId}/favorite`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
