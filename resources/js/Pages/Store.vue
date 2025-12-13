<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
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
const storeBanner = computed(
  () =>
    props.store?.banner ||
    'https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/seller/banner_image/18122023/631a5d56aa3096cbda26050f/2edcf9ca34478d3dcc12565b4a56e9.jpg?x-oss-process=image/resize,m_fill,w_1200,h_300/quality,Q_50'
);

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
</script>

<template>
  <LandingLayout>

    <Head :title="storeTitle" />

    <section class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:py-8 space-y-6">
      <div class="space-y-4">
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100">
          <img :src="storeBanner" :alt="props.store?.name || 'Banner toko'"
            class="h-[220px] w-full object-cover sm:h-[260px] lg:h-[280px]" />
        </div>

        <div
          class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-white px-6 py-4 shadow-sm">
          <div class="flex min-w-0 items-center gap-4">
            <div class="relative">
              <div
                class="grid size-14 place-items-center overflow-hidden rounded-full border border-neutral-200 bg-white">
                <img v-if="props.store?.avatar" :src="props.store.avatar" :alt="props.store?.name"
                  class="h-full w-full object-cover" />
                <span v-else class="text-xs font-semibold text-neutral-500">LOGO</span>
              </div>
              <span
                class="absolute -bottom-1 left-1 inline-flex items-center rounded-lg bg-sky-500 px-2.5 py-0.5 text-[10px] font-semibold text-white shadow-sm">
                {{ storeBadges[0] || 'UMKM' }}
              </span>
            </div>

            <div class="min-w-0 space-y-1">
              <p class="truncate text-base font-semibold text-neutral-900 sm:text-lg">
                {{ props.store?.name }}
              </p>

              <div class="flex flex-wrap gap-2">
                <span v-for="badge in storeBadges" :key="badge"
                  class="inline-flex items-center rounded-lg bg-neutral-100 px-3 py-1 text-[11px] font-semibold text-neutral-700">
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
              class="inline-flex items-center gap-2 rounded-xl border border-sky-500 px-5 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">
              <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 6h16v9H7l-3 3V6Z" />
              </svg>
              Chat Penjual
            </button>
          </div>
        </div>
      </div>

      <div class="mt-6 grid gap-6 lg:grid-cols-[296px_minmax(0,1fr)]">
        <aside class="space-y-4">
          <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between text-sm font-semibold text-neutral-900">
              <span>Filter</span>
              <button class="text-xs font-semibold text-sky-600" type="button" @click="resetFilters">Reset</button>
            </div>

            <div class="mt-4 space-y-4">
              <div class="space-y-3 rounded-xl border border-neutral-100 bg-neutral-50/60 p-4">
                <p class="text-sm font-semibold text-neutral-900">Rentang Harga</p>

                <div class="space-y-2 text-xs">
                  <label class="block text-neutral-500">Harga Terendah</label>
                  <input v-model="filterState.price_min" type="text" inputmode="numeric" placeholder="Rp 0"
                    class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                </div>

                <div class="space-y-2 text-xs">
                  <label class="block text-neutral-500">Harga Tertinggi</label>
                  <input v-model="filterState.price_max" type="text" inputmode="numeric" placeholder="Rp 500.000"
                    class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                </div>

                <label class="flex items-center gap-2 text-sm text-neutral-700">
                  <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500" />
                  Harga Diskon
                </label>

                <div class="flex justify-end">
                  <button type="button"
                    class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600"
                    @click="applyFilters">
                    Terapkan
                  </button>
                </div>
              </div>

              <details v-for="(group, index) in filterGroups" :key="group.title"
                class="group rounded-xl border border-neutral-100 bg-neutral-50/60" :open="index === 0">
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
              <form class="flex items-center rounded-xl border border-neutral-200 bg-white px-4 py-2.5"
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
                class="appearance-none rounded-xl border border-neutral-200 bg-white px-5 py-2 pr-9 text-sm font-medium text-neutral-700 outline-none"
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
              class="flex h-full flex-col overflow-hidden rounded-lg border border-neutral-100 bg-white shadow-sm">
              <Link :href="productUrl(product)" class="group block h-full">
              <div class="relative">
                <img :src="product.image" :alt="product.name"
                  class="h-40 w-full object-cover transition duration-150 group-hover:scale-[1.01]" />
                <span
                  class="absolute left-2 top-2 rounded-full bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold text-white shadow-sm">
                  {{ storeBadges[0] || 'UMKM' }}
                </span>
                <span v-if="product.tag === 'Pre Order'"
                  class="absolute right-2 top-2 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 shadow-sm">
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
            class="flex flex-wrap items-center gap-3 rounded-xl border border-neutral-200 bg-white px-5 py-4 text-sm text-neutral-600">
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
