<script setup>
import { Link } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
  isOpen: Boolean,
  megaMenuData: Array,
});

const emit = defineEmits(['close']);

const state = reactive({
  activeIndex: -1,
});
</script>

<template>
  <div class="fixed inset-0 z-[60] lg:hidden" v-if="isOpen">
    <div class="absolute inset-0 bg-black/50" @click="$emit('close')"></div>
    <div class="absolute inset-y-0 left-0 w-[280px] bg-white shadow-xl">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
        <span class="font-bold text-slate-900">Menu</span>
        <button @click="$emit('close')" class="rounded-lg p-2 text-slate-500 hover:bg-slate-50">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="h-[calc(100vh-60px)] overflow-y-auto p-4">
        <div class="space-y-1 border-b border-slate-100 pb-4">
          <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Mitra
            TP-PKK Marketplace</a>
          <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Menjadi
            Penjual</a>
          <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Info</a>
          <a href="#" class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Pusat
            Bantuan</a>
        </div>

        <div class="mt-4">
          <p class="mb-2 px-3 text-xs font-bold uppercase text-slate-400">Kategori</p>
          <div class="space-y-1">
            <div v-for="(category, index) in megaMenuData" :key="category.slug ?? category.label ?? index"
              class="space-y-1">
              <button @click="state.activeIndex = state.activeIndex === index ? -1 : index"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                <span>{{ category.label ?? category }}</span>
                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': state.activeIndex === index }"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div v-show="state.activeIndex === index" class="pl-4">
                <div v-for="(column, colIndex) in category.columns" :key="colIndex" class="mb-2">
                  <Link v-for="(item, itemIndex) in column" :key="item.slug ?? item.label ?? itemIndex"
                    :href="item.url || '#'"
                    class="block rounded-lg px-3 py-1.5 text-sm text-slate-500 hover:text-sky-600">
                  {{ item.label ?? item }}
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
