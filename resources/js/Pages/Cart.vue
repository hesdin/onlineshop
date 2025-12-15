<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  groups: {
    type: Array,
    default: () => [],
  },
  recommendations: {
    type: Array,
    default: () => [],
  },
});

const state = reactive({
  itemStates: [],
  loading: {},
});

const deleteSuccessVisible = ref(false);
const deleteSuccessMessage = ref('');
const deleteSuccessPendingMessage = ref('');
const DELETE_SUCCESS_DURATION = 3500;
let deleteSuccessHideTimeout = null;

const deleteLoadingVisible = ref(false);
const deleteLoadingStartedAt = ref(0);
const MIN_DELETE_LOADING_MS = 700;
let deleteLoadingHideTimeout = null;
let deleteLoadingActiveCount = 0;
const deleteLoadingMessage = 'Loading...';

const showDeleteSuccess = (message = 'Produk berhasil dihapus dari keranjang.') => {
  if (deleteLoadingVisible.value) {
    deleteSuccessPendingMessage.value = message;
    return;
  }

  deleteSuccessMessage.value = message;
  deleteSuccessVisible.value = true;
  if (deleteSuccessHideTimeout) {
    clearTimeout(deleteSuccessHideTimeout);
  }
  deleteSuccessHideTimeout = setTimeout(() => {
    deleteSuccessVisible.value = false;
    deleteSuccessMessage.value = '';
    deleteSuccessHideTimeout = null;
  }, DELETE_SUCCESS_DURATION);
};

const startDeleteLoading = () => {
  if (deleteLoadingActiveCount === 0) {
    if (deleteLoadingHideTimeout) {
      clearTimeout(deleteLoadingHideTimeout);
      deleteLoadingHideTimeout = null;
    }
    deleteLoadingStartedAt.value = Date.now();
    deleteLoadingVisible.value = true;
  }
  deleteLoadingActiveCount += 1;
};

const stopDeleteLoading = () => {
  if (deleteLoadingActiveCount === 0) return;
  deleteLoadingActiveCount -= 1;
  if (deleteLoadingActiveCount > 0) return;
  const elapsed = Date.now() - deleteLoadingStartedAt.value;
  const remaining = Math.max(0, MIN_DELETE_LOADING_MS - elapsed);
  deleteLoadingHideTimeout = setTimeout(() => {
    deleteLoadingVisible.value = false;
    deleteLoadingHideTimeout = null;
    if (deleteSuccessPendingMessage.value) {
      const pending = deleteSuccessPendingMessage.value;
      deleteSuccessPendingMessage.value = '';
      showDeleteSuccess(pending);
    }
  }, remaining);
};

const formatCurrency = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number.parseInt(value ?? 0, 10) || 0
  );

const toInteger = (value, fallback = null) => {
  const parsed = Number.parseInt(value ?? '', 10);
  return Number.isNaN(parsed) ? fallback : parsed;
};

const ensureArray = (value) => (Array.isArray(value) ? value : []);

const resolveItemId = (item, fallback = null) => {
  if (!item) return fallback;
  return item.id ?? item.itemId ?? item.item_id ?? fallback;
};

const captureSelectionState = () => {
  const selections = new Map();
  (state.itemStates ?? []).forEach((group) => {
    (group ?? []).forEach((item) => {
      const itemId = resolveItemId(item);
      if (!itemId) return;
      selections.set(itemId, Boolean(item.checked));
    });
  });
  return selections;
};

const pickImageSource = (item, index) => {
  const candidates = [
    item?.img,
    item?.image,
    item?.image_url,
    item?.imageUrl,
    item?.thumbnail,
    item?.cover_image_url,
  ];

  const validSource = candidates.find((src) => typeof src === 'string' && src.trim().length > 0);
  if (validSource) {
    return validSource;
  }

  return null;
};

const normalizeRecommendationItem = (item, index) => {
  const price = toInteger(item?.price ?? item?.sale_price ?? 0, 0) ?? 0;
  const rawOriginal =
    toInteger(item?.originalPrice ?? item?.original_price ?? item?.price, null) ??
    null;
  const originalPrice = rawOriginal && rawOriginal > price ? rawOriginal : null;
  const discountPercent =
    item?.discountPercent ??
    item?.discount_percent ??
    (originalPrice ? Math.max(0, Math.round(((originalPrice - price) / originalPrice) * 100)) : null);
  const rawSold = item?.sold ?? item?.sold_label ?? null;
  const sold =
    typeof rawSold === 'number'
      ? `Terjual ${rawSold}`
      : typeof rawSold === 'string' && rawSold.trim().length > 0
        ? rawSold
        : null;

  const badgesSource = item?.badges ?? item?.tags ?? [];

  return {
    id: item?.id ?? `rec-${index}`,
    title: item?.title ?? item?.name ?? 'Produk',
    price,
    originalPrice,
    discountPercent,
    location: item?.location ?? item?.location_label ?? null,
    sold,
    img: pickImageSource(item, index),
    url: item?.url ?? (item?.slug && item?.id ? `/product/${item.slug}/${item.id}` : '#'),
    badges: ensureArray(badgesSource),
    storeBadge: item?.storeBadge ?? item?.store_badge ?? (item?.store?.is_umkm ? 'UMKM' : null),
  };
};

const initializeState = () => {
  const previousSelections = captureSelectionState();
  state.itemStates = (props.groups ?? []).map((group) =>
    (group.items ?? []).map((item) => {
      const itemId = resolveItemId(item);
      return {
        id: itemId,
        qty: item.qty ?? item.quantity ?? 1,
        price: item.price ?? item.unit_price ?? 0,
        stock: item.stock ?? null,
        minOrder: item.minOrder ?? 1,
        checked: itemId ? previousSelections.get(itemId) ?? false : false,
      };
    })
  );
};

watch(
  () => props.groups,
  () => initializeState(),
  { immediate: true, deep: true }
);

const setLoading = (id, value) => {
  if (!id) return;
  if (value) {
    state.loading[id] = true;
  } else {
    Reflect.deleteProperty(state.loading, id);
  }
};

const isLoading = (id) => Boolean(id && state.loading[id]);

const toggleAll = (value) => {
  state.itemStates = state.itemStates.map((group) => group.map((item) => ({ ...item, checked: value })));
};

const toggleGroup = (groupIndex, value) => {
  if (!state.itemStates[groupIndex]) return;
  state.itemStates[groupIndex] = state.itemStates[groupIndex].map((item) => ({ ...item, checked: value }));
};

const toggleItem = (groupIndex, itemIndex, value) => {
  if (!state.itemStates[groupIndex] || !state.itemStates[groupIndex][itemIndex]) return;
  state.itemStates[groupIndex][itemIndex].checked = value;
};

const isGroupChecked = (groupIndex) => state.itemStates[groupIndex]?.every((item) => item.checked);
const isGroupIndeterminate = (groupIndex) => {
  const group = state.itemStates[groupIndex] ?? [];
  const some = group.some((item) => item.checked);
  const all = group.every((item) => item.checked);
  return some && !all;
};

const totalItems = computed(() => state.itemStates.reduce((sum, group) => sum + group.length, 0));
const selectedCount = computed(() => state.itemStates.flat().filter((item) => item.checked).length);
const selectedGroups = computed(() => state.itemStates.filter((group) => group.some((item) => item.checked)).length);
const selectedTotal = computed(() =>
  state.itemStates.reduce((sum, group, groupIndex) => {
    return (
      sum +
      group.reduce((inner, item, itemIndex) => {
        if (!item.checked) return inner;
        const product = props.groups?.[groupIndex]?.items?.[itemIndex];
        const price = product?.price ?? item.price ?? 0;
        return inner + price * item.qty;
      }, 0)
    );
  }, 0)
);

const orderSummaries = computed(() =>
  (props.groups ?? [])
    .map((group, groupIndex) => {
      const selected = (state.itemStates[groupIndex] ?? [])
        .map((itemState, itemIndex) =>
          itemState.checked ? { state: itemState, product: group.items?.[itemIndex] } : null
        )
        .filter(Boolean);

      if (!selected.length) return null;

      const itemCount = selected.reduce((sum, entry) => sum + entry.state.qty, 0);
      const total = selected.reduce(
        (sum, entry) => sum + (entry.product?.price ?? entry.state.price ?? 0) * entry.state.qty,
        0
      );

      return { vendor: group.vendor, itemCount, total };
    })
    .filter(Boolean)
);

const isAllChecked = computed(() => totalItems.value > 0 && selectedCount.value === totalItems.value);
const hasPartialAll = computed(() => selectedCount.value > 0 && selectedCount.value < totalItems.value);

const clampQty = (stateItem, product, value) => {
  const min = Math.max(1, product?.minOrder ?? stateItem?.minOrder ?? 1);
  let next = Math.max(min, value);
  const stock = stateItem?.stock ?? product?.stock ?? null;
  if (stock !== null) {
    next = Math.min(next, stock);
  }
  return next;
};

const persistQuantity = (itemId, quantity) => {
  if (!itemId) return;
  setLoading(itemId, true);
  router.patch(
    `/cart/items/${itemId}`,
    { quantity },
    {
      preserveScroll: true,
      replace: true,
      onFinish: () => setLoading(itemId, false),
    }
  );
};

const changeQuantity = (groupIndex, itemIndex, delta) => {
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  const product = props.groups?.[groupIndex]?.items?.[itemIndex];
  if (!stateItem) return;
  const next = clampQty(stateItem, product, (stateItem.qty || 0) + delta);
  if (next === stateItem.qty) return;
  stateItem.qty = next;
  persistQuantity(stateItem.id, next);
};

const setQuantity = (groupIndex, itemIndex, value) => {
  const numeric = Number.parseInt(value, 10);
  const stateItem = state.itemStates[groupIndex]?.[itemIndex];
  const product = props.groups?.[groupIndex]?.items?.[itemIndex];
  if (!stateItem) return;
  const next = clampQty(stateItem, product, Number.isNaN(numeric) ? stateItem.minOrder : numeric);
  stateItem.qty = next;
  persistQuantity(stateItem.id, next);
};

const removeItem = (itemId) => {
  if (!itemId) return;
  setLoading(itemId, true);
  startDeleteLoading();
  router.delete(`/cart/items/${itemId}`, {
    preserveScroll: true,
    replace: true,
    onSuccess: () => showDeleteSuccess(),
    onFinish: () => {
      setLoading(itemId, false);
      stopDeleteLoading();
    },
  });
};

const goToCheckout = () => {
  if (selectedCount.value === 0) return;
  const selectedIds = [];
  state.itemStates.forEach((group, groupIndex) => {
    group.forEach((itemState, itemIndex) => {
      if (itemState.checked) {
        const item = props.groups?.[groupIndex]?.items?.[itemIndex];
        if (item?.id) {
          selectedIds.push(item.id);
        }
      }
    });
  });

  router.get('/cart/checkout', { items: selectedIds }, { preserveScroll: true });
};

const groups = computed(() => props.groups ?? []);
const recommendations = computed(() =>
  ensureArray(props.recommendations).map((item, index) => normalizeRecommendationItem(item, index))
);
const hasRecommendations = computed(() => recommendations.value.length > 0);
</script>

<template>
  <LandingLayout>

    <Head :title="`Keranjang - ${appName}`" />

    <section class="space-y-6">
      <div class="flex flex-col gap-3 mb-3">
        <h1 class="text-3xl font-bold text-slate-900">Keranjang</h1>
        <div class="rounded-md border border-slate-200 bg-white">
          <div class="flex items-center gap-3 px-5 py-4">
            <label class="inline-flex items-center gap-3">
              <input type="checkbox" class="h-5 w-5 rounded-md border-slate-300 text-sky-600 focus:ring-sky-500"
                :checked="isAllChecked" :indeterminate="hasPartialAll" @change="toggleAll($event.target.checked)" />
              <span class="text-base font-semibold text-slate-900">
                Pilih Semua
                <span class="text-sm font-normal text-slate-400">({{ totalItems }})</span>
              </span>
            </label>
          </div>
        </div>
      </div>

      <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,2.3fr)_minmax(320px,1fr)]">
        <div class="space-y-4">
          <div v-if="!groups.length"
            class="rounded-md border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-600">
            Keranjang masih kosong.
          </div>

          <div v-for="(group, groupIndex) in groups" :key="group.storeId ?? groupIndex"
            class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
              <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                :checked="isGroupChecked(groupIndex)" :indeterminate="isGroupIndeterminate(groupIndex)"
                @change="toggleGroup(groupIndex, $event.target.checked)" />
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900">{{ group.vendor }}</p>
                <p class="text-xs text-slate-500">{{ group.location || 'Lokasi tidak tersedia' }}</p>
              </div>
              <span v-if="group.benefit"
                class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">
                {{ group.benefit }}
              </span>
            </div>

            <div class="divide-y divide-slate-100">
              <div v-for="(item, itemIndex) in group.items" :key="item.id ?? itemIndex" class="space-y-3 px-5 py-4">
                <div class="flex items-start gap-4">
                  <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    :checked="state.itemStates?.[groupIndex]?.[itemIndex]?.checked"
                    @change="toggleItem(groupIndex, itemIndex, $event.target.checked)" />

                  <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center md:gap-6">
                    <div class="h-24 w-24 overflow-hidden rounded-md border border-slate-100 bg-slate-50">
                      <img :src="item.img" :alt="item.name" class="h-full w-full object-cover" />
                    </div>

                    <div class="flex-1 space-y-2">
                      <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-md px-3 py-1 font-semibold"
                          :class="item.status === 'Tersedia' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                          {{ item.status }}
                        </span>
                        <div v-if="item.location" class="flex items-center gap-1 text-slate-500">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                            <circle cx="12" cy="9.5" r="2.3" />
                          </svg>
                          <span>{{ item.location }}</span>
                        </div>
                      </div>

                      <p class="text-base font-semibold leading-snug text-slate-900">
                        <Link v-if="item.url" :href="item.url" class="hover:text-sky-600">{{ item.name }}</Link>
                        <span v-else>{{ item.name }}</span>
                      </p>

                      <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                        <span v-for="tag in item.tags" :key="tag" class="rounded-md bg-slate-100 px-2.5 py-1">{{ tag
                          }}</span>
                      </div>
                    </div>

                    <div class="flex flex-col items-end gap-3">
                      <p class="text-lg font-bold text-slate-900">
                        {{ formatCurrency(item.price) }}
                      </p>

                      <div class="flex items-center gap-3 text-slate-400">
                        <button type="button"
                          class="rounded-md p-3 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                          :disabled="isLoading(item.id)" aria-label="Hapus item" @click="removeItem(item.id)">
                          <svg class="h-5 w-5" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                            stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                            </path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                          </svg>
                        </button>

                        <div class="inline-flex overflow-hidden rounded-md border border-slate-200">
                          <button type="button"
                            class="px-3 py-2 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isLoading(item.id)" @click="changeQuantity(groupIndex, itemIndex, -1)">−</button>
                          <input type="number"
                            class="qty-input w-14 border-x border-slate-200 px-3 py-2 text-center text-sm font-semibold text-slate-800 focus:outline-none disabled:cursor-not-allowed"
                            :value="state.itemStates?.[groupIndex]?.[itemIndex]?.qty" min="1"
                            :disabled="isLoading(item.id)"
                            @change="setQuantity(groupIndex, itemIndex, $event.target.value)" />
                          <button type="button"
                            class="px-3 py-2 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isLoading(item.id)" @click="changeQuantity(groupIndex, itemIndex, 1)">+</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="pl-11 md:pl-[140px]">
                  <button type="button"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:text-sky-700">
                    <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12.5 20H21.5" stroke="#00A6F4" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path
                        d="M17 3.49998C17.3978 3.10216 17.9374 2.87866 18.5 2.87866C18.7786 2.87866 19.0544 2.93353 19.3118 3.04014C19.5692 3.14674 19.803 3.303 20 3.49998C20.197 3.69697 20.3532 3.93082 20.4598 4.18819C20.5664 4.44556 20.6213 4.72141 20.6213 4.99998C20.6213 5.27856 20.5664 5.55441 20.4598 5.81178C20.3532 6.06915 20.197 6.303 20 6.49998L7.5 19L3.5 20L4.5 16L17 3.49998Z"
                        stroke="#00A6F4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <span>Catatan Untuk Penjual</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <aside class="space-y-3">
          <div class="rounded-md border border-slate-200 bg-white p-5">
            <p class="text-lg font-semibold text-slate-900">Ringkasan Belanja</p>

            <div class="mt-4 space-y-4 text-sm text-slate-600">
              <p v-if="orderSummaries.length === 0" class="text-xs text-slate-500">
                Pilih produk untuk melihat ringkasan pesanan.
              </p>

              <div v-else v-for="(order, idx) in orderSummaries" :key="`order-${idx}`"
                class="space-y-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                <p class="text-base font-semibold text-slate-800">Pesanan ke {{ idx + 1 }}</p>

                <div class="flex items-start justify-between gap-4">
                  <span class="text-slate-500">Nama Toko</span>
                  <span class="max-w-[60%] text-right text-sm font-semibold text-slate-800">{{ order.vendor }}</span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Total Harga {{ order.itemCount }} Barang</span>
                  <span class="font-semibold text-slate-800">{{ formatCurrency(order.total) }}</span>
                </div>

                <div class="flex items-center justify-between font-semibold text-slate-800">
                  <span>Total Pesanan</span>
                  <span>{{ formatCurrency(order.total) }}</span>
                </div>
              </div>

              <div class="flex items-center justify-between text-xs text-slate-500">
                <span>
                  {{
                    selectedCount
                      ? `${selectedGroups} toko, ${selectedCount} barang dipilih`
                      : 'Belum ada barang dipilih'
                  }}
                </span>
              </div>

              <div
                class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-800">
                <span>Total Harga</span>
                <span class="text-base text-slate-900">{{ formatCurrency(selectedTotal) }}</span>
              </div>
            </div>

            <button type="button" class="mt-5 w-full rounded-md px-4 py-3 text-sm font-semibold transition"
              :class="selectedCount === 0 ? 'bg-slate-200 text-slate-500 cursor-not-allowed' : 'bg-sky-500 text-white hover:bg-sky-600'"
              :disabled="selectedCount === 0" @click="goToCheckout">
              <span>{{ selectedCount === 0 ? 'Pilih produk terlebih dahulu' : 'Beli' }}</span>
            </button>
          </div>

          <div class="rounded-md border border-dashed border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
            <p class="font-semibold text-slate-800">Butuh bantuan?</p>
            <p class="mt-1 text-slate-500">Hubungi pusat bantuan atau chat penjual sebelum checkout.</p>
          </div>
        </aside>
      </div>
    </section>

    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-3xl font-bold text-slate-900">Rekomendasi Untuk Kamu</h2>
        <Link href="/" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Semua</Link>
      </div>

      <div v-if="hasRecommendations" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
        <article v-for="item in recommendations" :key="item.id"
          class="flex flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:-translate-y-0.5 hover:shadow-md">
          <div class="h-40 w-full overflow-hidden bg-slate-100">
            <img v-if="item.img" :src="item.img" :alt="item.title" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center text-xs font-medium text-slate-400">
              Tidak ada gambar
            </div>
          </div>
          <div class="flex flex-1 flex-col gap-2 p-4">
            <div class="flex items-center gap-2 text-[11px] font-semibold">
              <span v-if="item.storeBadge"
                class="rounded-sm bg-sky-500 px-2 py-0.5 uppercase tracking-wide text-white">{{ item.storeBadge
                }}</span>
              <span v-for="badge in item.badges" :key="badge"
                class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">{{ badge }}</span>
            </div>

            <p class="line-clamp-2 text-sm font-semibold text-slate-900">
              <Link :href="item.url" class="hover:text-sky-600">{{ item.title }}</Link>
            </p>

            <div v-if="item.location" class="flex items-center gap-1 text-[11px] text-slate-500">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M10 2.5a5.25 5.25 0 00-5.25 5.25c0 3.714 4.338 7.458 4.83 7.88a.56.56 0 00.72 0c.492-.422 4.95-4.166 4.95-7.88A5.25 5.25 0 0010 2.5zm0 7.25a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
              <span>{{ item.location }}</span>
            </div>

            <p class="text-base font-bold text-slate-900">
              {{ formatCurrency(item.price) }}
            </p>

            <div v-if="item.originalPrice" class="flex items-center gap-2 text-xs">
              <span class="text-slate-400 line-through">{{ formatCurrency(item.originalPrice) }}</span>
              <span v-if="item.discountPercent"
                class="rounded-md bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600">
                {{ item.discountPercent }}%
              </span>
            </div>

            <div class="mt-auto flex items-center justify-between text-xs text-slate-500">
              <span>{{ item.sold || 'Stok aman' }}</span>
              <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Stok
                aman</span>
            </div>
          </div>
        </article>
      </div>
      <div v-else
        class="rounded-md border border-dashed border-slate-200 bg-white px-6 py-8 text-center text-sm text-slate-500">
        Belum ada rekomendasi berdasarkan aktivitas belanjamu.
      </div>
    </section>

    <Teleport to="body">
      <div v-if="deleteLoadingVisible"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/35">
        <div
          class="flex items-center gap-3 rounded-xl bg-white/95 px-4 py-3 text-slate-800 shadow-xl ring-1 ring-slate-200">
          <svg class="h-5 w-5 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
            <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
          </svg>
          <span class="text-sm font-semibold">{{ deleteLoadingMessage }}</span>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="-translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100" leave-to-class="-translate-y-2 opacity-0">
        <div v-if="deleteSuccessVisible"
          class="fixed top-0 left-0 z-[70] w-full bg-emerald-600 px-4 py-3 text-center text-sm font-semibold text-white shadow-md">
          {{ deleteSuccessMessage || 'Produk berhasil dihapus dari keranjang.' }}
        </div>
      </Transition>
    </Teleport>
  </LandingLayout>
</template>

<style scoped>
.qty-input::-webkit-inner-spin-button,
.qty-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.qty-input[type='number'] {
  -moz-appearance: textfield;
}
</style>
