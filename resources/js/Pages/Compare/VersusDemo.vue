<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import VersusCompare from '@/Components/Compare/VersusCompare.vue';
import { computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  metricDefinitions: {
    type: Array,
    default: () => []
  }
});

// Generate dynamic title based on products
const comparisonTitle = computed(() => {
  if (!props.items || props.items.length === 0) return 'Comparație Produse';
  
  if (props.items.length === 2) {
    return `${props.items[0].brand} vs ${props.items[1].brand}`;
  }
  
  // For 3+ products, use brand names
  const brands = [...new Set(props.items.map(item => item.brand))];
  if (brands.length === 1) {
    return `Comparație ${brands[0]}`;
  }
  
  return 'Comparație Produse';
});

const comparisonSubtitle = computed(() => {
  return 'Analiză detaliată specificații și prețuri';
});
</script>

<template>
  <PublicLayout title="Comparație Versus-Style">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Demo Comparație Versus
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Debug info -->
        <div v-if="!items || items.length === 0" class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
          <strong class="font-bold">Debug:</strong>
          <span class="block sm:inline">Nu există items. Items count: {{ items?.length || 0 }}</span>
        </div>
        
        <VersusCompare
          v-if="items && items.length > 0"
          :items="items"
          :metricDefinitions="metricDefinitions"
          :title="comparisonTitle"
          :subtitle="comparisonSubtitle"
        />
        <div v-else class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          <strong class="font-bold">Eroare:</strong>
          <span class="block sm:inline">Nu există date pentru comparație.</span>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
