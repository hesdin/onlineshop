<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
  layout: LandingLayout,
});

const props = defineProps({
  products: {
    type: Array,
    default: () => [],
  },
});

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number(value ?? 0),
  );

const formatDate = (value) => {
  if (!value) return '-';
  const date = new Date(value);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const buyingProduct = ref(null);

const buyAgain = (product) => {
  if (buyingProduct.value === product.id) return;

  buyingProduct.value = product.id;

  router.post('/cart', {
    product_id: product.id,
    quantity: product.last_purchased?.quantity || 1,
    note: '',
  }, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/cart');
    },
    onError: (errors) => {
      console.error('Failed to add product:', errors);
      alert('Gagal menambahkan produk ke keranjang. Silakan coba lagi.');
      buyingProduct.value = null;
    },
    onFinish: () => {
      // Reset only if we haven't navigated away
      setTimeout(() => {
        buyingProduct.value = null;
      }, 500);
    },
  });
};
</script>

<template>
  <div class="bg-slate-50 min-h-screen">

    <Head title="Beli Lagi" />

    <div class="mx-auto flex max-w-full flex-col gap-6 px-4 py-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
        <a href="/" class="transition hover:text-slate-800">Beranda</a>
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
            clip-rule="evenodd" />
        </svg>
        <span class="text-slate-900">Beli Lagi</span>
      </nav>

      <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <CustomerSidebarMenu active-key="beli-lagi" />

        <main class="space-y-6">
          <section class="max-w-4xl space-y-6">
            <!-- Header Section -->
            <div class="flex flex-wrap items-end justify-between gap-4">
              <div>
                <h1 class="text-2xl font-bold text-slate-900">Beli Lagi</h1>
                <p class="mt-1 text-sm text-slate-500">Produk yang pernah Anda beli sebelumnya</p>
              </div>
            </div>

            <!-- Products List -->
            <div v-if="props.products.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div v-for="product in props.products" :key="product.id"
                class="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">

                <!-- Product Image -->
                <div class="aspect-square overflow-hidden bg-slate-100">
                  <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                    class="h-full w-full object-cover transition-transform group-hover:scale-105">
                  <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                    <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </div>

                <!-- Product Info -->
                <div class="p-4 space-y-3">
                  <div>
                    <a :href="`/product/${product.slug}`"
                      class="font-semibold text-slate-900 line-clamp-2 hover:text-sky-600 transition-colors">
                      {{ product.name }}
                    </a>
                    <p v-if="product.store?.name" class="mt-1 text-xs text-slate-500">
                      {{ product.store.name }}
                    </p>
                  </div>

                  <div class="flex items-center justify-between">
                    <span class="text-lg font-bold text-sky-600">{{ formatPrice(product.price) }}</span>
                    <span v-if="!product.is_available"
                      class="rounded bg-rose-100 px-2 py-0.5 text-xs font-medium text-rose-700">
                      Stok Habis
                    </span>
                  </div>

                  <div class="text-xs text-slate-500 border-t border-slate-100 pt-3">
                    <div class="flex justify-between">
                      <span>Terakhir dibeli:</span>
                      <span class="font-medium text-slate-700">{{ formatDate(product.last_purchased?.date) }}</span>
                    </div>
                    <div class="flex justify-between mt-1">
                      <span>Jumlah terakhir:</span>
                      <span class="font-medium text-slate-700">{{ product.last_purchased?.quantity || 1 }} item</span>
                    </div>
                  </div>

                  <button @click="buyAgain(product)" :disabled="!product.is_available || buyingProduct === product.id"
                    class="w-full rounded-md bg-sky-600 px-4 py-2.5 font-bold text-white shadow transition-all hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg v-if="buyingProduct === product.id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2">
                      <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                      <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ buyingProduct === product.id ? 'Memproses...' : 'Beli Lagi' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else
              class="flex min-h-[400px] flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
              <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-slate-900">Belum ada riwayat pembelian</h3>
              <p class="mt-2 max-w-sm text-slate-500">
                Anda belum memiliki pesanan yang sudah selesai. Mulai belanja untuk melihat produk yang bisa dibeli
                ulang.
              </p>
              <a href="/"
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-sky-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Mulai Belanja
              </a>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
