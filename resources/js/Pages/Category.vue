<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  category: {
    type: Object,
    default: () => ({ name: 'Kategori', slug: '' }),
  },
  products: {
    type: Object,
    default: () => ({ data: [], meta: {}, links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  options: {
    type: Object,
    default: () => ({}),
  },
});

const filterState = reactive({
  location: props.filters?.location ?? null,
  status: props.filters?.status ?? null,
  seller_type: props.filters?.seller_type ?? null,
  price_min: props.filters?.price_min ?? null,
  price_max: props.filters?.price_max ?? null,
  badges: [...(props.filters?.badges ?? [])],
  sort: props.filters?.sort ?? 'latest',
  item_type: props.filters?.item_type ?? null,
  rating: props.filters?.rating ?? null,
  child_category: props.filters?.child_category ?? null,
});

const uiState = reactive({
  showFilters: false,
});

const categoryName = computed(() => props.category?.name ?? 'Kategori');
const productList = computed(() => props.products?.data ?? []);
const pagination = computed(() => props.products ?? {});
const baseUrl = computed(() => (props.category?.id ? `/c/${props.category.slug}` : '/search'));

const pageLinks = computed(() =>
  (pagination.value?.links ?? []).filter((link) => Number.isInteger(Number(link.label)))
);

const options = computed(() => ({
  locations: props.options?.locations ?? [],
  statuses: props.options?.statuses ?? [],
  sellerTypes: props.options?.sellerTypes ?? [],
  badgeOptions: props.options?.badgeOptions ?? [],
  priceRanges: props.options?.priceRanges ?? [],
  ratingOptions: props.options?.ratingOptions ?? [],
  sortOptions: props.options?.sortOptions ?? [],
  childCategories: props.options?.childCategories ?? [],
  itemTypes: props.options?.itemTypes ?? [],
}));

const formatPrice = (value) => {
  if (value == null) return 'Rp0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value);
};

const productUrl = (product) => `/product/${product.slug || 'paket-sembako-117500'}/${product.id || '69301fac70d5bce72c24be6d'}`;

const toTitleCase = (value) => {
  if (!value) return '-';
  return value
    .toString()
    .toLowerCase()
    .split(/\s+/)
    .filter(Boolean)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

const locationLabel = (product) => toTitleCase(product.location_city);

const applyFilters = (overrides = {}) => {
  const params = {
    ...filterState,
    ...overrides,
    badges: filterState.badges.filter(Boolean),
  };

  router.get(baseUrl.value, params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const setPriceRange = (range) => {
  filterState.price_min = range?.min ?? null;
  filterState.price_max = range?.max ?? null;
  applyFilters();
};

const toggleBadge = (badgeValue) => {
  if (filterState.badges.includes(badgeValue)) {
    filterState.badges = filterState.badges.filter((badge) => badge !== badgeValue);
  } else {
    filterState.badges = [...filterState.badges, badgeValue];
  }
  applyFilters();
};

const resetFilters = () => {
  filterState.location = null;
  filterState.status = null;
  filterState.seller_type = null;
  filterState.price_min = null;
  filterState.price_max = null;
  filterState.badges = [];
  filterState.sort = 'latest';
  filterState.item_type = null;
  filterState.rating = null;
  filterState.child_category = null;
  applyFilters();
};

const closeFilters = () => {
  uiState.showFilters = false;
};

const paginationInfo = computed(() => {
  const { from, to, total } = pagination.value || {};
  if (!from && !to) {
    return null;
  }
  return { from, to, total };
});

const changePage = (url) => {
  if (!url) return;
  router.get(url, { ...filterState, badges: filterState.badges.filter(Boolean) }, { preserveScroll: true, replace: true });
};
</script>

<template>
  <LandingLayout>

    <Head :title="`${categoryName} - ${props.appName}`" />

    <section class="space-y-6">

      <!-- Breadcrumb -->
      <!-- <nav class="flex items-center gap-2 text-xs text-slate-500">
        <Link class="text-sky-600" href="/">Beranda K</Link>
        <span>/</span>
        <span>Produk</span>
        <span>/</span>
        <span class="font-semibold text-slate-900">{{ categoryName }}</span>
      </nav> -->

      <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-12">
        <aside class="hidden space-y-4 lg:col-span-3 lg:block">
          <div class="rounded-lg border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
              <span>Filter</span>
              <button class="text-xs font-semibold text-sky-600" type="button" @click="resetFilters">Reset</button>
            </div>

            <div class="mt-4 space-y-3">
              <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Kategori
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="child_category" value="" :checked="!filterState.child_category"
                      @change="filterState.child_category = null; applyFilters()" />
                    <span>Semua {{ categoryName }}</span>
                  </label>
                  <label v-for="child in options.childCategories" :key="child.id"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="child_category" :value="child.id" :checked="filterState.child_category === child.id"
                      @change="filterState.child_category = child.id; applyFilters()" />
                    <span>{{ child.name }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Tipe Penjual
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="seller_type" value="" :checked="!filterState.seller_type"
                      @change="filterState.seller_type = null; applyFilters()" />
                    <span>Semua</span>
                  </label>
                  <label v-for="seller in options.sellerTypes" :key="seller.value"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="seller_type" :value="seller.value" :checked="filterState.seller_type === seller.value"
                      @change="filterState.seller_type = seller.value; applyFilters()" />
                    <span>{{ seller.label }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Lokasi
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="location" value="" :checked="!filterState.location"
                      @change="filterState.location = null; applyFilters()" />
                    <span>Semua</span>
                  </label>
                  <label v-for="location in options.locations" :key="location"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="location" :value="location" :checked="filterState.location === location"
                      @change="filterState.location = location; applyFilters()" />
                    <span>{{ location }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Rentang Harga
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="price_range" value="" :checked="!filterState.price_min && !filterState.price_max"
                      @change="setPriceRange(null)" />
                    <span>Semua harga</span>
                  </label>
                  <label v-for="range in options.priceRanges" :key="range.label"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="price_range" :value="range.label"
                      :checked="filterState.price_min === range.min && filterState.price_max === range.max"
                      @change="setPriceRange(range)" />
                    <span>{{ range.label }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Tipe Produk
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="item_type" value="" :checked="!filterState.item_type"
                      @change="filterState.item_type = null; applyFilters()" />
                    <span>Semua</span>
                  </label>
                  <label v-for="type in options.itemTypes" :key="type.value"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="item_type" :value="type.value" :checked="filterState.item_type === type.value"
                      @change="filterState.item_type = type.value; applyFilters()" />
                    <span>{{ type.label }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Stok Produk
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="status" value="" :checked="!filterState.status"
                      @change="filterState.status = null; applyFilters()" />
                    <span>Semua</span>
                  </label>
                  <label v-for="status in options.statuses" :key="status.value"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="status" :value="status.value" :checked="filterState.status === status.value"
                      @change="filterState.status = status.value; applyFilters()" />
                    <span>{{ status.label }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Rating
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="rating" value="" :checked="!filterState.rating"
                      @change="filterState.rating = null; applyFilters()" />
                    <span>Semua</span>
                  </label>
                  <label v-for="rating in options.ratingOptions" :key="rating.value"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                      name="rating" :value="rating.value" :checked="filterState.rating === rating.value"
                      @change="filterState.rating = rating.value; applyFilters()" />
                    <span>{{ rating.label }}</span>
                  </label>
                </div>
              </details>

              <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  Sertifikat
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  <label v-for="badge in options.badgeOptions" :key="badge.value"
                    class="flex cursor-pointer items-center gap-2">
                    <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="checkbox"
                      :value="badge.value" :checked="filterState.badges.includes(badge.value)"
                      @change="toggleBadge(badge.value)" />
                    <span>{{ badge.label }}</span>
                  </label>
                </div>
              </details>
            </div>
          </div>
        </aside>

        <div class="space-y-4 md:col-span-9">
          <div class="flex flex-wrap items-center gap-3 text-md font-semibold">
            <div class="flex gap-4 font-semibold text-slate-500">
              <button class="border-b-2 border-sky-500 pb-2 text-slate-900">Produk</button>
              <button class="pb-2 text-slate-400" disabled>Vendor</button>
              <button class="pb-2 text-slate-400" disabled>Layanan</button>
            </div>
            <div class="ml-auto hidden flex-wrap items-center gap-3 text-sm lg:flex">
              <span class="text-[13px] font-bold">
                Urutkan:
              </span>

              <select class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium bg-white"
                v-model="filterState.sort" @change="applyFilters()">
                <option v-for="option in options.sortOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-3 rounded-md py-3 text-sm lg:hidden">
            <select class="flex-1 rounded-sm border border-slate-300 px-3 py-2 text-sm font-medium"
              v-model="filterState.sort" @change="applyFilters()">
              <option v-for="option in options.sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <button type="button"
              class="flex-1 inline-flex gap-2 rounded-sm border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-sky-400"
              @click="uiState.showFilters = true">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6" />
              </svg>
              Filter
            </button>
          </div>

          <p v-if="paginationInfo" class="text-sm text-slate-600">
            Menampilkan {{ paginationInfo.from }} - {{ paginationInfo.to }} dari total {{ paginationInfo.total }}
            <span class="font-semibold text-slate-900">{{ categoryName }}</span>
          </p>

          <div v-if="productList.length"
            class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            <Link v-for="product in productList" :key="product.id" :href="productUrl(product)"
              class="group block h-full">
              <article
                class="flex h-full flex-col overflow-hidden rounded-sm border border-slate-100 bg-white transition hover:-translate-y-0.5 hover:shadow-sm">
                <div class="relative">
                  <div v-if="product.image_url" class="h-40 w-full">
                    <img :src="product.image_url" :alt="product.name" class="h-full w-full object-cover"
                      loading="lazy" />
                  </div>
                  <div v-else class="flex h-40 w-full items-center justify-center bg-slate-100 text-slate-400">
                    <div class="flex flex-col items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-8 w-8 text-slate-300">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                      </svg>
                      <span class="text-[10px] font-medium text-slate-400">No Image</span>
                    </div>
                  </div>
                  <span
                    class="absolute left-2 top-2 rounded-sm bg-sky-600 px-2.5 py-0.5 text-[10px] font-semibold text-white">
                    {{ product.store?.is_umkm ? 'UMKM' : 'Vendor' }}
                  </span>
                </div>
                <div class="flex flex-1 flex-col gap-2 p-3">
                  <span
                    class="inline-flex min-w-0 max-w-full items-center gap-1 truncate rounded-sm border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-700">
                    <span class="h-1.5 w-1.5 rounded-lg bg-sky-500"></span>
                    {{ product.store?.name || 'Tanpa Store' }}
                  </span>
                  <h3 class="text-sm font-semibold leading-tight text-slate-900 group-hover:text-sky-600 line-clamp-1">
                    {{
                      product.name
                    }}</h3>
                  <p class="text-sm font-bold text-slate-900">
                    {{ formatPrice(product.sale_price ?? product.price) }}
                  </p>
                  <p class="flex items-center gap-1 text-xs text-slate-500">
                    {{ locationLabel(product) }}
                  </p>
                  <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-slate-600">
                    <span v-if="product.is_pdn"
                      class="rounded-md bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-600">PDN</span>
                    <span v-if="product.is_pkp"
                      class="rounded-md bg-slate-100 px-2 py-0.5 font-semibold text-slate-700">PKP</span>
                    <span v-if="product.is_tkdn"
                      class="rounded-md bg-orange-50 px-2 py-0.5 font-semibold text-orange-700">TKDN</span>
                  </div>
                </div>
              </article>
            </Link>
          </div>
          <div v-else
            class="rounded-lg border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-slate-500">
            Tidak ada produk yang sesuai filter.
          </div>

          <div
            class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
            <p class="text-xs text-slate-500">
              <span v-if="paginationInfo">Menampilkan {{ paginationInfo.from }}-{{ paginationInfo.to }} dari {{
                paginationInfo.total }} produk</span>
            </p>
            <div class="ml-auto flex items-center gap-2">
              <button class="rounded-full border border-slate-200 px-3 py-1 text-xs"
                :disabled="!pagination.prev_page_url" @click="changePage(pagination.prev_page_url)">
                Sebelumnya
              </button>
              <div class="flex items-center gap-1 text-xs">
                <button v-for="link in pageLinks" :key="link.url ?? link.label" class="h-7 w-7 rounded-full"
                  :class="link.active ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-600'"
                  @click="changePage(link.url)">
                  {{ link.label }}
                </button>
              </div>
              <button class="rounded-full border border-slate-200 px-3 py-1 text-xs"
                :disabled="!pagination.next_page_url" @click="changePage(pagination.next_page_url)">
                Selanjutnya
              </button>
            </div>
          </div>

          <article class="space-y-4 rounded-lg bg-white p-6 shadow">
            <h2 class="text-2xl font-bold text-slate-900">
              Jual Ragam Pengadaan {{ categoryName }} Hanya di TP-PKK Marketplace
            </h2>
            <p class="text-sm text-slate-600">
              Solusi kurasi bagi instansi yang ingin kolaborasi dengan pelaku UMKM. Kategori {{ categoryName }} di
              TP-PKK Marketplace
              menghadirkan pilihan kurasi yang siap dikirimkan ke seluruh Indonesia.
            </p>
            <div class="space-y-4 text-sm text-slate-600">
              <div>
                <h3 class="text-base font-semibold text-slate-900">Sambut Harimu dengan Produk Bergizi</h3>
                <p>Penuhi kebutuhan pantry kantor atau acara perusahaan dengan kudapan sehat, kopi botolan, hingga
                  frozen food dari vendor terpercaya. Setiap produk telah melewati proses verifikasi agar kualitasnya
                  terjaga.</p>
              </div>
              <div>
                <h3 class="text-base font-semibold text-slate-900">Rasakan Kurasi Hampers Premium</h3>
                <p>Paket parsel dan hampers eksklusif siap dikustomisasi sesuai tema perusahaan. Tim vendor UMKM
                  tersebar di berbagai kota, memudahkan distribusi massal dengan biaya terjangkau.</p>
              </div>
              <div>
                <h3 class="text-base font-semibold text-slate-900">Bagaimana Cara Menjadi Pemasok?</h3>
                <p>Jika Anda pelaku UMKM kategori {{ categoryName.toLowerCase() }}, segera daftar sebagai mitra TP-PKK
                  Marketplace untuk menikmati akses tender dan pembeli BUMN. Tim kami siap membantu proses onboarding
                  dan sertifikasi.</p>
              </div>
            </div>
          </article>
        </div>
      </div>

      <transition name="fade">
        <div v-if="uiState.showFilters" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm lg:hidden"
          @click.self="closeFilters">
          <div class="absolute inset-y-0 right-0 w-full max-w-md overflow-y-auto bg-white shadow-xl">
            <div class="flex items-center gap-3 border-b border-slate-100 px-4 py-3">
              <button type="button" class="rounded-full p-2 text-slate-500 hover:bg-slate-100" @click="closeFilters">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 18l-6-6 6-6" />
                </svg>
              </button>
              <h2 class="text-base font-semibold text-slate-900">Filter</h2>
              <button type="button" class="ml-auto text-sm font-semibold text-sky-600"
                @click="resetFilters">Reset</button>
            </div>

            <div class="space-y-3 p-4">
              <!-- Reuse the same filter blocks for mobile -->
              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group" open>
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Kategori
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-child_category" value="" :checked="!filterState.child_category"
                        @change="filterState.child_category = null; applyFilters(); closeFilters()" />
                      <span>Semua {{ categoryName }}</span>
                    </label>
                    <label v-for="child in options.childCategories" :key="child.id"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-child_category" :value="child.id" :checked="filterState.child_category === child.id"
                        @change="filterState.child_category = child.id; applyFilters(); closeFilters()" />
                      <span>{{ child.name }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group" open>
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Tipe Penjual
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-seller_type" value="" :checked="!filterState.seller_type"
                        @change="filterState.seller_type = null; applyFilters()" />
                      <span>Semua</span>
                    </label>
                    <label v-for="seller in options.sellerTypes" :key="seller.value"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-seller_type" :value="seller.value" :checked="filterState.seller_type === seller.value"
                        @change="filterState.seller_type = seller.value; applyFilters()" />
                      <span>{{ seller.label }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group">
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Lokasi
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-location" value="" :checked="!filterState.location"
                        @change="filterState.location = null; applyFilters()" />
                      <span>Semua</span>
                    </label>
                    <label v-for="location in options.locations" :key="location"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-location" :value="location" :checked="filterState.location === location"
                        @change="filterState.location = location; applyFilters()" />
                      <span>{{ location }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group" open>
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Rentang Harga
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-price_range" value="" :checked="!filterState.price_min && !filterState.price_max"
                        @change="setPriceRange(null)" />
                      <span>Semua harga</span>
                    </label>
                    <label v-for="range in options.priceRanges" :key="range.label"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-price_range" :value="range.label"
                        :checked="filterState.price_min === range.min && filterState.price_max === range.max"
                        @change="setPriceRange(range)" />
                      <span>{{ range.label }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group">
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Tipe Produk
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-item_type" value="" :checked="!filterState.item_type"
                        @change="filterState.item_type = null; applyFilters()" />
                      <span>Semua</span>
                    </label>
                    <label v-for="type in options.itemTypes" :key="type.value"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-item_type" :value="type.value" :checked="filterState.item_type === type.value"
                        @change="filterState.item_type = type.value; applyFilters()" />
                      <span>{{ type.label }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group" open>
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Stok Produk
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-status" value="" :checked="!filterState.status"
                        @change="filterState.status = null; applyFilters()" />
                      <span>Semua</span>
                    </label>
                    <label v-for="status in options.statuses" :key="status.value"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-status" :value="status.value" :checked="filterState.status === status.value"
                        @change="filterState.status = status.value; applyFilters()" />
                      <span>{{ status.label }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group">
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Rating
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-rating" value="" :checked="!filterState.rating"
                        @change="filterState.rating = null; applyFilters()" />
                      <span>Semua</span>
                    </label>
                    <label v-for="rating in options.ratingOptions" :key="rating.value"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                        name="m-rating" :value="rating.value" :checked="filterState.rating === rating.value"
                        @change="filterState.rating = rating.value; applyFilters()" />
                      <span>{{ rating.label }}</span>
                    </label>
                  </div>
                </details>
              </div>

              <div class="rounded-lg border border-slate-100 bg-slate-50/60">
                <details class="group">
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    Sertifikat
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                      stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </summary>
                  <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                    <label v-for="badge in options.badgeOptions" :key="badge.value"
                      class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="checkbox"
                        :value="badge.value" :checked="filterState.badges.includes(badge.value)"
                        @change="toggleBadge(badge.value)" />
                      <span>{{ badge.label }}</span>
                    </label>
                  </div>
                </details>
              </div>
            </div>

            <div class="sticky bottom-0 flex gap-3 border-t border-slate-100 bg-white p-4">
              <button type="button"
                class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700"
                @click="resetFilters">
                Reset
              </button>
              <button type="button"
                class="flex-1 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"
                @click="closeFilters">
                Terapkan
              </button>
            </div>
          </div>
        </div>
      </transition>
    </section>
  </LandingLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.25s ease, opacity 0.25s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
