<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  store: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Array,
    default: () => [],
  },
  products: {
    type: Object,
    default: () => ({ data: [], links: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  filterGroups: {
    type: Array,
    default: () => [],
  },
  sortOptions: {
    type: Array,
    default: () => [],
  },
  reviews: {
    type: Object,
    default: () => ({
      summary: { average_rating: 0, total_reviews: 0, distribution: {} },
      items: { data: [] },
      filter: { rating: null },
    }),
  },
});

const filterState = reactive({
  search: props.filters?.search ?? '',
  sort: props.filters?.sort ?? 'latest',
  price_min: props.filters?.price_min ?? '',
  price_max: props.filters?.price_max ?? '',
  status: props.filters?.status ?? '',
  certificates: [...(props.filters?.certificates ?? [])],
});

const storeTitle = computed(() => `${props.store?.name ?? 'Toko'} - ${props.appName}`);
const storeLocation = computed(() => props.store?.location || 'Lokasi tidak tersedia');
const storeBadges = computed(() => props.store?.badges ?? []);
const storeBanner = computed(() => props.store?.banner_url || null);
const storeLogo = computed(() => props.store?.logo_url || null);

const productsList = computed(() => props.products?.data ?? []);
const pagination = computed(() => props.products ?? {});
const pageLinks = computed(() =>
  (pagination.value?.links ?? []).filter((link) => Number.isInteger(Number(link.label)))
);
const storeSlug = computed(() => props.store?.slug || props.store?.id || '');
const productTotal = computed(() => pagination.value?.total ?? productsList.value.length ?? 0);
const prevPageUrl = computed(() => pagination.value?.prev_page_url);
const nextPageUrl = computed(() => pagination.value?.next_page_url);

const normalizeNumber = (value) => {
  const parsed = Number.parseInt(value, 10);
  return Number.isNaN(parsed) ? null : parsed;
};

const applyFilters = (overrides = {}) => {
  const payload = {
    ...filterState,
    ...overrides,
    price_min: normalizeNumber(filterState.price_min),
    price_max: normalizeNumber(filterState.price_max),
    certificates: filterState.certificates.filter(Boolean),
  };

  if (!storeSlug.value) {
    return;
  }

  router.get(`/store/${storeSlug.value}`, payload, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filterState.search = '';
  filterState.sort = 'latest';
  filterState.price_min = '';
  filterState.price_max = '';
  filterState.status = '';
  filterState.certificates = [];
  applyFilters();
};

const changePage = (url) => {
  if (!url) return;

  router.get(
    url,
    { ...filterState, certificates: filterState.certificates.filter(Boolean) },
    { preserveScroll: true, preserveState: true }
  );
};

const toggleCertificate = (value, checked) => {
  if (checked) {
    filterState.certificates = [...filterState.certificates, value];
  } else {
    filterState.certificates = filterState.certificates.filter((item) => item !== value);
  }
  applyFilters();
};

const setStatus = (value, checked) => {
  filterState.status = checked ? value : '';
  applyFilters();
};

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value ?? 0);

const productUrl = (product) =>
  `/product/${product.slug || 'produk'}/${product.id || '69301fac70d5bce72c24be6d'}`;

const displayRange = computed(() => {
  const { from, to, total } = pagination.value ?? {};
  if (!from && !to) {
    return null;
  }
  return { from, to, total };
});

const optionLabel = (option) => (typeof option === 'string' ? option : option.label);
const optionValue = (option) => (typeof option === 'string' ? option : option.value);
const sortChoices = computed(() =>
  (props.sortOptions?.length ? props.sortOptions : [{ value: 'latest', label: 'Terbaru' }]).map((item) => ({
    value: item.value,
    label: item.label,
  }))
);

const statIconClass = (icon) => {
  if (icon === 'rating') return 'bg-amber-50';
  if (icon === 'transactions') return 'bg-sky-50';
  if (icon === 'response') return 'bg-neutral-100';
  return 'bg-sky-50';
};

// Reviews section
const reviewsSummary = computed(() => props.reviews?.summary ?? { average_rating: 0, total_reviews: 0, distribution: {} });
const reviewsList = computed(() => props.reviews?.items?.data ?? []);
const reviewsPagination = computed(() => props.reviews?.items ?? {});
const activeRatingFilter = computed(() => props.reviews?.filter?.rating ?? null);

const ratingTabs = [
  { label: 'Semua', value: null },
  { label: '⭐ 5', value: 5 },
  { label: '⭐ 4', value: 4 },
  { label: '⭐ 3', value: 3 },
  { label: '⭐ 2', value: 2 },
  { label: '⭐ 1', value: 1 },
];

const filterByRating = (rating) => {
  const payload = { ...filterState, review_rating: rating || undefined };
  router.get(`/store/${storeSlug.value}`, payload, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const getDistributionPercentage = (rating) => {
  const total = reviewsSummary.value.total_reviews;
  if (!total) return 0;
  const count = reviewsSummary.value.distribution?.[rating] ?? 0;
  return Math.round((count / total) * 100);
};

const changeReviewPage = (url) => {
  if (!url) return;
  router.get(url, {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <LandingLayout>

    <Head :title="storeTitle" />

    <section class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:py-8 space-y-6">
      <div class="space-y-4">
        <div v-if="storeBanner" class="overflow-hidden rounded-md border border-neutral-200 bg-neutral-100">
          <img :src="storeBanner" :alt="props.store?.name || 'Banner toko'"
            class="h-[220px] w-full object-cover sm:h-[260px] lg:h-[280px]" />
        </div>

        <div
          class="flex flex-wrap items-center justify-between gap-4 rounded-md border border-neutral-200 bg-white px-6 py-4">
          <div class="flex min-w-0 items-center gap-4">
            <div class="relative">
              <div
                class="grid size-24 place-items-center overflow-hidden rounded-full border border-neutral-200 bg-neutral-50">
                <img v-if="storeLogo" :src="storeLogo" :alt="props.store?.name" class="h-full w-full object-cover" />
                <svg v-else class="size-6 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5">
                  <rect x="3" y="3" width="18" height="18" rx="2" />
                  <circle cx="8.5" cy="8.5" r="1.5" />
                  <path d="m21 15-5-5L5 21" />
                </svg>
              </div>
            </div>

            <div class="min-w-0 space-y-1">
              <p class="truncate text-base font-semibold text-neutral-900 sm:text-lg">
                {{ props.store?.name }}
              </p>

              <div class="flex flex-wrap gap-2">
                <span v-for="badge in storeBadges" :key="badge"
                  class="text-xs inline-flex items-center rounded-md bg-neutral-100 px-3 py-1 text-[11px] font-semibold text-neutral-700">
                  {{ badge }}
                </span>
              </div>

              <p class="flex items-center gap-1 text-sm text-neutral-500">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                  <circle cx="12" cy="11" r="3" />
                </svg>
                {{ storeLocation }}
              </p>
            </div>
          </div>

          <div class="flex flex-1 flex-wrap items-center justify-end gap-6">
            <div v-for="stat in stats" :key="stat.label" class="flex items-center gap-2 text-sm">
              <div class="grid size-10 place-items-center rounded-full" :class="statIconClass(stat.icon)">
                <template v-if="stat.icon === 'bumn'">
                  <span class="text-[10px] font-semibold text-sky-600">BUMN</span>
                </template>
                <template v-else-if="stat.icon === 'transactions'">
                  <svg class="size-5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <rect x="4" y="4" width="16" height="16" rx="2" />
                    <path d="M8 10h8M8 14h4" />
                  </svg>
                </template>
                <template v-else-if="stat.icon === 'rating'">
                  <svg class="size-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                  </svg>
                </template>
                <template v-else>
                  <svg class="size-5 text-neutral-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M12 8v5l3 2" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="12" cy="12" r="8" />
                  </svg>
                </template>
              </div>
              <div class="text-left">
                <p class="text-xs text-neutral-500">{{ stat.label }}</p>
                <p class="text-sm font-semibold text-neutral-800">{{ stat.value }}</p>
              </div>
            </div>

            <button type="button"
              class="inline-flex items-center gap-2 rounded-md border border-sky-500 px-5 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp"
                viewBox="0 0 16 16">
                <path
                  d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
              </svg>
              Chat Penjual
            </button>
          </div>
        </div>
      </div>

      <div class="mt-6 grid gap-6 lg:grid-cols-[296px_minmax(0,1fr)]">
        <aside class="space-y-4">
          <div class="rounded-md border border-neutral-200 bg-white p-4">
            <div class="flex items-center justify-between text-sm font-semibold text-neutral-900">
              <span>Filter</span>
              <button class="text-xs font-semibold text-sky-600" type="button" @click="resetFilters">Reset</button>
            </div>

            <div class="mt-4 space-y-4">
              <div class="space-y-3 rounded-md border border-neutral-100 bg-neutral-50/60 p-4">
                <p class="text-sm font-semibold text-neutral-900">Rentang Harga</p>

                <div class="space-y-2 text-xs">
                  <label class="block text-neutral-500">Harga Terendah</label>
                  <input v-model="filterState.price_min" type="text" inputmode="numeric" placeholder="Rp 0"
                    class="w-full rounded-md border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                </div>

                <div class="space-y-2 text-xs">
                  <label class="block text-neutral-500">Harga Tertinggi</label>
                  <input v-model="filterState.price_max" type="text" inputmode="numeric" placeholder="Rp 500.000"
                    class="w-full rounded-md border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                </div>

                <label class="flex items-center gap-2 text-sm text-neutral-700">
                  <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500" />
                  Harga Diskon
                </label>

                <div class="flex justify-end">
                  <button type="button"
                    class="rounded-md bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600"
                    @click="applyFilters">
                    Terapkan
                  </button>
                </div>
              </div>

              <details v-for="(group, index) in filterGroups" :key="group.title"
                class="group rounded-md border border-neutral-100 bg-neutral-50/60" :open="index === 0">
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-neutral-800">
                  {{ group.title }}
                  <svg class="size-4 text-neutral-400 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-neutral-100 px-4 py-3 text-xs text-neutral-700">
                  <template v-for="option in group.options" :key="optionLabel(option)">
                    <label v-if="group.key === 'status'" class="flex cursor-pointer items-center gap-2">
                      <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500"
                        :checked="filterState.status === optionValue(option)"
                        @change="setStatus(optionValue(option), $event.target.checked)" />
                      <span>{{ optionLabel(option) }}</span>
                    </label>
                    <label v-else-if="group.key === 'certificates'" class="flex cursor-pointer items-center gap-2">
                      <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500"
                        :checked="filterState.certificates.includes(optionValue(option))"
                        @change="toggleCertificate(optionValue(option), $event.target.checked)" />
                      <span>{{ optionLabel(option) }}</span>
                    </label>
                    <label v-else class="flex cursor-pointer items-center gap-2">
                      <input type="checkbox"
                        class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500" />
                      <span>{{ optionLabel(option) }}</span>
                    </label>
                  </template>
                </div>
              </details>
            </div>
          </div>
        </aside>

        <div class="space-y-4">
          <div class="flex flex-wrap items-center gap-4">
            <div class="min-w-0 flex-1">
              <form class="flex items-center rounded-md border border-neutral-200 bg-white px-4 py-2.5"
                @submit.prevent="applyFilters">
                <input v-model="filterState.search" type="text" placeholder="Cari produk toko ini"
                  class="w-full bg-transparent text-sm text-neutral-700 placeholder:text-neutral-400 outline-none" />

                <button type="submit" class="ml-2 inline-flex items-center justify-center">
                  <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m16 16 4 4" />
                  </svg>
                </button>
              </form>
            </div>

            <div class="relative inline-flex">
              <select v-model="filterState.sort"
                class="appearance-none rounded-md border border-neutral-200 bg-white px-5 py-2 pr-9 text-sm font-medium text-neutral-700 outline-none"
                @change="applyFilters">
                <option v-for="option in sortChoices" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>

              <span class="pointer-events-none absolute inset-y-0 right-3 inline-flex items-center">
                <svg class="size-4 text-neutral-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </span>
            </div>
          </div>

          <p class="text-sm text-neutral-600">
            Menampilkan {{ productTotal }} produk dari
            <span class="font-semibold text-neutral-900">{{ props.store?.name }}</span>
          </p>

          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <article v-for="product in productsList" :key="product.id"
              class="flex h-full flex-col overflow-hidden rounded-md border border-neutral-100 bg-white">
              <Link :href="productUrl(product)" class="group block h-full">
                <div class="relative">
                  <img :src="product.image" :alt="product.name"
                    class="h-40 w-full object-cover transition duration-150 group-hover:scale-[1.01]" />
                  <span
                    class="absolute left-2 top-2 rounded-full bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                    {{ storeBadges[0] || 'UMKM' }}
                  </span>
                  <span v-if="product.tag === 'Pre Order'"
                    class="absolute right-2 top-2 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">
                    {{ product.tag }}
                  </span>
                </div>
                <div class="flex flex-1 flex-col gap-2 p-3">
                  <h3 class="line-clamp-2 text-sm font-semibold leading-tight text-neutral-900">
                    {{ product.name }}
                  </h3>
                  <p class="text-sm font-bold text-neutral-900">{{ formatPrice(product.price) }}</p>

                  <p class="mt-0.5 flex items-center gap-1 text-xs text-neutral-500">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                      <circle cx="12" cy="11" r="3" />
                    </svg>
                    {{ product.location || storeLocation }}
                  </p>

                  <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-neutral-600">
                    <span v-for="badge in product.badges" :key="badge"
                      class="rounded-md bg-neutral-100 px-2 py-0.5 font-semibold text-neutral-700">
                      {{ badge }}
                    </span>
                  </div>
                </div>
              </Link>
            </article>
          </div>

          <div
            class="flex flex-wrap items-center gap-3 rounded-md border border-neutral-200 bg-white px-5 py-4 text-sm text-neutral-600">
            <p class="text-xs text-neutral-500">
              <template v-if="displayRange">
                Menampilkan {{ displayRange.from }}-{{ displayRange.to }} dari {{ displayRange.total }} produk
              </template>
              <template v-else>Menampilkan semua produk</template>
            </p>

            <div class="ml-auto flex items-center gap-2">
              <button type="button"
                class="rounded-full border border-neutral-200 px-3 py-1 text-xs hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!prevPageUrl" @click="changePage(prevPageUrl)">
                Sebelumnya
              </button>

              <div class="flex items-center gap-1 text-xs">
                <button v-for="link in pageLinks" :key="link.label" class="size-7 rounded-full" :class="link.active
                  ? 'bg-sky-500 text-white'
                  : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200'" type="button"
                  @click="changePage(link.url)">
                  {{ link.label }}
                </button>
              </div>

              <button type="button"
                class="rounded-full border border-neutral-200 px-3 py-1 text-xs hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!nextPageUrl" @click="changePage(nextPageUrl)">
                Selanjutnya
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </LandingLayout>
</template>
