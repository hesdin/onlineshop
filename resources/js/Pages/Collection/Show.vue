<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

type ProductItem = {
  id: number;
  slug: string;
  title: string;
  vendor: string;
  price: number | string;
  badge: string | null;
  location: string | null;
  sold: string | null;
  status: string | null;
  tags: string[];
  image: string | null;
};

const props = defineProps<{
  appName: string;
  collection: {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    banner: string | null;
  };
  products: {
    data: ProductItem[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: { label: string; url: string | null; active: boolean }[];
  };
}>();

const filters = [
  {
    title: 'Kategori',
    options: [
      'Mainan & Hobi',
      'Puzzle',
      'Olahraga',
      'Teknologi',
      'Perawatan Rumah',
      'Aksesori Komputer & Laptop',
      'Komputer & Laptop',
      'Software',
      'Lihat Semua',
    ],
  },
  {
    title: 'Tipe Penjual',
    options: ['Premium', 'UMKM', 'Vendor Besar', 'Koperasi', 'PKP', 'Non PKP'],
  },
  {
    title: 'Lokasi',
    options: ['DKI Jakarta', 'Jawa Tengah', 'Jawa Timur', 'Jawa Barat', 'Banten', 'Lihat Semua'],
  },
  { title: 'Rentang Harga', type: 'price' as const },
  { title: 'Stok Produk', options: ['Pre Order'] },
  { title: 'Rating', options: ['4 Ke atas'] },
  { title: 'Sertifikat', options: ['CSMS', 'Produk Dalam Negeri', 'TKDN'] },
];

const formatPrice = (value: number | string) => {
  if (typeof value === 'string') return value;
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value ?? 0);
};

const productUrl = (product: ProductItem) => {
  if (!product?.id || !product?.slug) return '#';
  return `/product/${product.slug}/${product.id}`;
};

const numberedPaginationLinks = computed(() =>
  (props.products.links ?? []).filter((link) => Number.isInteger(Number(link.label))),
);
</script>

<template>
  <LandingLayout>
    <Head :title="`${props.collection.title} - ${props.appName}`" />

    <section class="space-y-6">
      <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a class="text-sky-600" href="/">Beranda</a>
        <span>/</span>
        <span class="font-semibold text-slate-900">{{ props.collection.title }}</span>
      </nav>

      <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <img v-if="props.collection.banner" :src="props.collection.banner" :alt="props.collection.title"
          class="h-64 w-full object-cover md:h-72 lg:h-80" />
        <div v-else class="flex h-64 w-full items-center justify-center bg-slate-50 text-slate-500 md:h-72 lg:h-80">
          <div class="text-center">
            <p class="text-sm font-semibold">No Image</p>
            <p class="mt-1 text-xs text-slate-400">Banner koleksi belum diatur</p>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-start gap-4">
        <div class="space-y-1">
          <p class="text-xs font-semibold uppercase text-slate-500">Koleksi</p>
          <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
            {{ props.collection.title }}
          </h1>
        </div>
        <a href="#" class="ml-auto inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:underline">
          Syarat &amp; Ketentuan
        </a>
      </div>

      <div class="grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
        <aside class="space-y-4">
          <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
              <span>Filter</span>
              <button type="button" class="text-xs font-semibold text-sky-600">Reset</button>
            </div>

            <div class="mt-4 space-y-3">
              <template v-for="(filter, filterIndex) in filters" :key="`${filter.title}-${filterIndex}`">
                <div v-if="filter.type === 'price'" class="space-y-3 rounded-lg border border-slate-100 bg-slate-50/60 p-4">
                  <p class="text-sm font-semibold text-slate-900">Rentang Harga</p>
                  <div class="space-y-2 text-xs">
                    <label class="block text-slate-500">Harga Terendah</label>
                    <input type="text" placeholder="Rp 0"
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                  </div>
                  <div class="space-y-2 text-xs">
                    <label class="block text-slate-500">Harga Tertinggi</label>
                    <input type="text" placeholder="Rp 500.000"
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                  </div>
                  <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" />
                    Harga Diskon
                  </label>
                </div>

                <details v-else class="group rounded-lg border border-slate-100 bg-slate-50/60"
                  :open="filterIndex === 0">
                  <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    {{ filter.title }}
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 px-4 pb-4 text-sm text-slate-700">
                    <label v-for="option in filter.options ?? []" :key="`${filter.title}-${option}`"
                      class="flex items-center gap-2">
                      <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" />
                      <span>{{ option }}</span>
                    </label>
                  </div>
                </details>
              </template>
            </div>
          </div>
        </aside>

        <div class="space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative flex-1">
              <input type="text" placeholder="Cari di koleksi"
                class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
            </div>

            <div class="relative inline-flex min-w-[150px]">
              <select
                class="w-full appearance-none rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm font-medium text-slate-700 outline-none">
                <option>Urutkan</option>
                <option>Harga Terendah</option>
                <option>Harga Tertinggi</option>
                <option>Ulasan</option>
                <option>Terlaris</option>
              </select>
              <span class="pointer-events-none absolute inset-y-0 right-3 inline-flex items-center">
                <svg class="h-4 w-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </span>
            </div>
          </div>

          <p class="text-sm text-slate-600">
            Menampilkan {{ props.products.data.length }} produk dari koleksi
            <span class="font-semibold text-slate-900">{{ props.collection.title }}</span>
          </p>

          <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            <a v-for="product in props.products.data" :key="product.id" :href="productUrl(product)"
              class="flex h-full flex-col overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
              <div class="relative">
                <img v-if="product.image" :src="product.image" :alt="product.title" class="h-44 w-full object-cover" />
                <div v-else class="flex h-44 w-full items-center justify-center bg-slate-100 text-xs font-semibold text-slate-400">
                  No Image
                </div>
                <span v-if="product.badge"
                  class="absolute left-2 top-2 rounded-full bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold uppercase text-white shadow-sm">
                  {{ product.badge }}
                </span>
                <span v-if="product.status"
                  class="absolute right-2 top-2 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 shadow-sm">
                  {{ product.status }}
                </span>
              </div>

              <div class="flex flex-1 flex-col gap-2 p-3">
                <span
                  class="inline-flex w-fit items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                  <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                  {{ product.vendor }}
                </span>

                <h3 class="line-clamp-2 text-sm font-semibold leading-tight text-slate-900">
                  {{ product.title }}
                </h3>

                <p class="text-sm font-bold text-slate-900">{{ formatPrice(product.price) }}</p>

                <p class="flex items-center gap-1 text-xs text-slate-500">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                    <circle cx="12" cy="11" r="3" />
                  </svg>
                  {{ product.location }}
                </p>

                <p v-if="product.sold" class="text-[11px] font-semibold text-amber-600">
                  {{ product.sold }}
                </p>

                <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-slate-600">
                  <span v-for="tag in product.tags" :key="`${product.id}-${tag}`"
                    class="rounded-md bg-slate-100 px-2 py-0.5 font-semibold text-slate-700">
                    {{ tag }}
                  </span>
                </div>
              </div>
            </a>
          </div>

          <div
            class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
            <p class="text-xs text-slate-500">
              Menampilkan {{ props.products.data.length }} dari {{ props.products.total }} produk
            </p>
            <div class="ml-auto flex items-center gap-2">
              <a :href="props.products.prev_page_url || '#'"
                class="rounded-full border border-slate-200 px-3 py-1 text-xs hover:bg-slate-50"
                :class="!props.products.prev_page_url ? 'pointer-events-none opacity-50' : ''">Sebelumnya</a>
              <div class="flex items-center gap-1 text-xs">
                <a v-for="link in numberedPaginationLinks" :key="`page-${link.label}`" :href="link.url || '#'"
                  class="grid h-7 w-7 place-items-center rounded-full"
                  :class="link.active ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                  {{ link.label }}
                </a>
              </div>
              <a :href="props.products.next_page_url || '#'"
                class="rounded-full border border-slate-200 px-3 py-1 text-xs hover:bg-slate-50"
                :class="!props.products.next_page_url ? 'pointer-events-none opacity-50' : ''">Selanjutnya</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>

