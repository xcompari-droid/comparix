<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
      <h1 class="text-5xl font-black text-gray-900 mb-3">
        {{ title || 'Comparație Versus-Style' }}
      </h1>
      <p class="text-xl text-gray-600">
        {{ subtitle || 'Analiză detaliată side-by-side' }}
      </p>
    </div>

    <!-- Product Cards -->
    <div class="grid gap-8 mb-12" :class="`grid-cols-1 md:grid-cols-${items.length}`">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="bg-white rounded-2xl shadow-xl p-8 border-4 transform transition-all hover:scale-105 hover:shadow-2xl"
        :style="{ borderColor: item.color }"
      >
        <!-- Victory Count Badge -->
        <div v-if="getWinnerCount(item) > 0" class="flex justify-between items-center mb-4">
          <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
            :style="{ backgroundColor: item.color + '20', color: item.color }"
          >
            🏆 {{ getWinnerCount(item) }} victorii
          </div>
        </div>

        <!-- Product Image -->
        <div class="aspect-square bg-gray-50 rounded-xl mb-6 flex items-center justify-center overflow-hidden">
          <img
            v-if="item.image_url"
            :src="item.image_url"
            :alt="item.name"
            class="max-w-full max-h-full object-contain p-4"
          />
          <div v-else class="text-gray-400 text-6xl">📦</div>
        </div>

        <!-- Product Info -->
        <h3 class="text-2xl font-black mb-2" :style="{ color: item.color }">
          {{ item.name }}
        </h3>
        <p class="text-gray-600 text-base mb-6">{{ item.brand }}</p>

        <!-- Quick Stats -->
        <div class="space-y-3 bg-gray-50 rounded-lg p-4">
          <div
            v-for="(metric, metricKey) in getTopMetrics(item, 3)"
            :key="metricKey"
            class="flex justify-between items-center text-sm"
          >
            <span class="text-gray-600 font-medium">{{ getMetricLabel(metricKey) }}:</span>
            <span class="font-bold text-gray-900">{{ formatMetricValue(metricKey, metric) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparison Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-12">
      <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b-2 border-gray-200">
        <h2 class="text-2xl font-black text-gray-900">Comparație Detaliată</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
                Specificație
              </th>
              <th
                v-for="(item, index) in items"
                :key="index"
                class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider"
                :style="{ color: item.color }"
              >
                {{ item.name }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="metricKey in getSpecMetricKeys()"
              :key="metricKey"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                {{ getMetricLabel(metricKey) }}
              </td>
              <td
                v-for="(item, index) in items"
                :key="index"
                class="px-6 py-4 text-center text-sm relative"
                :class="getWinnerClass(metricKey, item)"
              >
                <!-- Trophy for Winner -->
                <div v-if="isWinner(metricKey, item)" class="absolute top-2 right-2 text-lg">
                  🏆
                </div>
                <div class="font-bold text-base">
                  {{ formatMetricValue(metricKey, item.metrics[metricKey]) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Price Section -->
    <div class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 rounded-2xl shadow-xl p-10">
      <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">💰 Prețuri și Oferte</h2>

      <div class="grid gap-8" :class="`grid-cols-1 md:grid-cols-${items.length}`">
        <div
          v-for="(item, index) in items"
          :key="index"
          class="bg-white rounded-xl p-8 border-l-8 shadow-lg transform transition-all hover:scale-105"
          :style="{ borderColor: item.color }"
        >
          <h4 class="font-black text-xl mb-4" :style="{ color: item.color }">
            {{ item.name }}
          </h4>

          <div v-if="item.price" class="mb-6">
            <div class="text-4xl font-black text-gray-900 mb-1">
              {{ item.price.toFixed(2) }}
              <span class="text-2xl text-gray-500">EUR</span>
            </div>
            <!-- Cheapest Badge -->
            <div
              v-if="isCheapest(item)"
              class="inline-flex items-center gap-2 mt-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-bold"
            >
              🏆 Cel mai ieftin
            </div>
          </div>
          <div v-else class="text-2xl text-gray-400 mb-6">Preț indisponibil</div>

          <a
            v-if="item.product_url"
            :href="item.product_url"
            class="block w-full text-center px-6 py-3 rounded-lg text-white font-bold transition-all transform hover:scale-105 hover:shadow-lg"
            :style="{ backgroundColor: item.color }"
          >
            Vezi Oferte →
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  metricDefinitions: {
    type: Array,
    default: () => []
  },
  title: String,
  subtitle: String
});

// Get top N metrics for quick stats
const getTopMetrics = (item, count = 3) => {
  const entries = Object.entries(item.metrics || {});
  return Object.fromEntries(entries.slice(0, count));
};

// Get all unique metric keys from all items
const getAllMetricKeys = () => {
  const keys = new Set();
  props.items.forEach(item => {
    Object.keys(item.metrics || {}).forEach(key => keys.add(key));
  });
  return Array.from(keys);
};

// Get metric keys excluding price
const getSpecMetricKeys = () => {
  return getAllMetricKeys().filter(key => {
    const keyLower = key.toLowerCase();
    return !keyLower.includes('price') && 
           !keyLower.includes('pret') && 
           !keyLower.includes('preț') && 
           keyLower !== 'price_eur';
  });
};

// Get metric label from definitions
const getMetricLabel = (key) => {
  const def = props.metricDefinitions?.find(m => m.key === key);
  return def?.label || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Format metric value with unit
const formatMetricValue = (key, value) => {
  if (value === null || value === undefined) return '-';
  
  // Handle boolean values
  if (typeof value === 'boolean') {
    return value ? 'Da' : 'Nu';
  }
  
  // Handle string boolean values
  if (value === 'true' || value === true) return 'Da';
  if (value === 'false' || value === false) return 'Nu';
  
  const def = props.metricDefinitions?.find(m => m.key === key);
  return def?.unit ? `${value}${def.unit}` : value;
};

// Check if item is winner for specific metric
const isWinner = (metricKey, item) => {
  const def = props.metricDefinitions?.find(m => m.key === metricKey);
  if (!def) return false;

  const values = props.items
    .map(i => i.metrics[metricKey])
    .filter(v => v !== null && v !== undefined);

  if (values.length === 0) return false;

  const currentValue = item.metrics[metricKey];
  if (currentValue === null || currentValue === undefined) return false;

  return def.higherIsBetter
    ? currentValue === Math.max(...values)
    : currentValue === Math.min(...values);
};

// Get CSS class for winner cells
const getWinnerClass = (metricKey, item) => {
  const currentValue = item.metrics[metricKey];
  if (currentValue === null || currentValue === undefined) {
    return 'text-gray-400';
  }

  return isWinner(metricKey, item)
    ? 'bg-green-50 text-green-700 font-bold'
    : '';
};

// Count how many metrics each item wins
const getWinnerCount = (item) => {
  return getSpecMetricKeys().filter(key => isWinner(key, item)).length;
};

// Check if item has the lowest price
const isCheapest = (item) => {
  if (!item.price) return false;
  
  const prices = props.items
    .map(i => i.price)
    .filter(p => p !== null && p !== undefined);
  
  return item.price === Math.min(...prices);
};
</script>
