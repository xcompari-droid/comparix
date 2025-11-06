@extends('layouts.main')

@section('title', $category->name . ' - compariX.ro')

@section('content')
<div class="bg-gradient-to-b from-cyan-50 to-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm mb-4">
            <a href="/" class="text-gray-500 hover:text-cyan-600">Acasă</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="/categorii" class="text-gray-500 hover:text-cyan-600">Categorii</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-900">{{ $category->name }}</span>
        </nav>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-lg text-gray-600">{{ $category->description }}</p>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($products->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nu există produse în această categorie</h3>
            <p class="mt-1 text-sm text-gray-500">Adaugă produse din panoul de admin</p>
        </div>
    @else
        <!-- Selected products for comparison -->
        <div id="comparison-bar" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-cyan-500 shadow-lg p-4 z-50">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="font-semibold">Produse selectate: <span id="selected-count">0</span>/3</span>
                </div>
                <button onclick="compareProducts()" class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors font-medium">
                    Compară produsele
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 overflow-hidden">
                    <!-- Checkbox for comparison -->
                    <div class="p-4 border-b bg-gray-50">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="comparison-checkbox w-5 h-5 text-cyan-600 rounded focus:ring-cyan-500" 
                                   value="{{ $product->id }}"
                                   onchange="updateComparison()">
                            <span class="ml-2 text-sm text-gray-700">Adaugă la comparație</span>
                        </label>
                    </div>

                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ $product->name }}
                        </h3>
                        
                        @if($product->brand)
                            <p class="text-sm text-gray-500 mb-3">{{ $product->brand }}</p>
                        @endif

                        @if($product->description)
                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($product->description, 100) }}</p>
                        @endif

                        <!-- Offers -->
                        @if($product->offers->isNotEmpty())
                            <div class="space-y-2 mb-4">
                                @foreach($product->offers->take(3) as $offer)
                                    <a href="/oferta/{{ $offer->id }}" 
                                       target="_blank"
                                       class="block p-3 bg-gradient-to-r from-cyan-50 to-emerald-50 rounded-lg hover:from-cyan-100 hover:to-emerald-100 transition-colors">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">{{ $offer->store_name }}</span>
                                            <span class="text-lg font-bold text-cyan-600">{{ number_format($offer->price, 2) }} RON</span>
                                        </div>
                                        @if($offer->stock_status)
                                            <span class="text-xs text-green-600">✓ În stoc</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <a href="/produse/{{ $product->slug ?? $product->id }}" 
                           class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            Vezi detalii
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    @endif
</div>

<script>
    let selectedProducts = [];

    function updateComparison() {
        const checkboxes = document.querySelectorAll('.comparison-checkbox:checked');
        selectedProducts = Array.from(checkboxes).map(cb => cb.value);
        
        const bar = document.getElementById('comparison-bar');
        const count = document.getElementById('selected-count');
        
        count.textContent = selectedProducts.length;
        
        if (selectedProducts.length > 0) {
            bar.classList.remove('hidden');
        } else {
            bar.classList.add('hidden');
        }

        // Disable checkboxes if 3 are selected
        document.querySelectorAll('.comparison-checkbox').forEach(cb => {
            if (!cb.checked && selectedProducts.length >= 3) {
                cb.disabled = true;
            } else {
                cb.disabled = false;
            }
        });
    }

    function compareProducts() {
        if (selectedProducts.length < 2) {
            alert('Selectează cel puțin 2 produse pentru comparație');
            return;
        }
        window.location.href = '/compara?ids=' + selectedProducts.join(',');
    }
</script>
@endsection
