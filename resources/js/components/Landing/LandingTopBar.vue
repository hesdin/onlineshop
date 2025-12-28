<script setup>
import { computed } from 'vue';

const whatsappNumber = import.meta.env.VITE_CUSTOMER_CARE_WA || '6282187654321';
const whatsappLink = computed(() => `https://wa.me/${whatsappNumber}`);
</script>

<template>
  <div class="border-b border-slate-200 bg-slate-50">
    <div class="container mx-auto flex items-center justify-end px-0 py-2 text-xs text-slate-500 lg:justify-between">
      <nav class="hidden items-center gap-4 lg:flex">
        <a href="#" class="font-bold hover:text-slate-700">UMKM Sulsel Berdaya</a>
        <a href="/register?role=seller" class="hover:text-slate-700">Menjadi Penjual</a>
        <a href="/#faq" class="hover:text-slate-700">Info</a>
        <a :href="whatsappLink" target="_blank" rel="noopener noreferrer" class="hover:text-slate-700">Pusat Bantuan</a>
      </nav>
      <div class="flex items-center gap-3">

        <!-- Commented out for now -->
        <!-- <div class="flex overflow-hidden rounded-sm border border-slate-200 text-[11px] font-semibold">
          <button class="bg-sky-500 px-3 py-1 text-white">IND</button>
          <button class="bg-slate-100 px-3 py-1 text-slate-500">ENG</button>
        </div> -->
        <!-- <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
          Logo BUMN / Lainnya
        </span> -->
      </div>
    </div>
  </div>
</template>
