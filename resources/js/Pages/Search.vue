<script setup lang="ts">
import LandingLayout from '@/Layouts/LandingLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps<{
    query: string | null;
    products: {
        data: Array<{
            id: number;
            name: string;
            slug: string;
            price: number | null;
            sale_price: number | null;
            image_url: string | null;
            location_city?: string | null;
            location_province?: string | null;
            store?: { id: number; name: string } | null;
            url?: string | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters?: {
        q?: string | null;
        status?: string | null;
        seller_type?: string | null;
        item_type?: string | null;
        price_min?: number | null;
        price_max?: number | null;
        location?: string | null;
        sort?: string | null;
        rating?: number | null;
        badges?: Array<string>;
    };
    options?: {
        locations?: Array<string>;
        statuses?: Array<{ value: string; label: string }>;
        itemTypes?: Array<{ value: string; label: string }>;
        sellerTypes?: Array<{ value: string; label: string }>;
        badgeOptions?: Array<{ value: string; label: string }>;
        priceRanges?: Array<{ label: string; min: number | null; max: number | null }>;
        ratingOptions?: Array<{ label: string; value: number }>;
        sortOptions?: Array<{ value: string; label: string }>;
    };
    appName?: string;
}>();

defineOptions({
    layout: LandingLayout,
});

const items = computed(() => props.products?.data ?? []);
const filterState = reactive({
    q: props.filters?.q ?? props.query ?? '',
    status: props.filters?.status ?? null,
    seller_type: props.filters?.seller_type ?? null,
    item_type: props.filters?.item_type ?? null,
    price_min: props.filters?.price_min ?? null,
    price_max: props.filters?.price_max ?? null,
    location: props.filters?.location ?? null,
    sort: props.filters?.sort ?? 'latest',
    rating: props.filters?.rating ?? null,
    badges: props.filters?.badges ?? [],
});

const formatPrice = (value: number | null | undefined) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
        Number(value ?? 0),
    );

const locationLabel = (product: any) => {
    const parts = [product.location_city, product.location_province].filter(Boolean);
    return parts.length ? parts.join(', ') : '-';
};

const applyFilters = (overrides: Record<string, unknown> = {}) => {
    const params = {
        ...filterState,
        ...overrides,
    };
    if (!params.q) delete params.q;
    params.badges = (params.badges as Array<string> | undefined)?.filter(Boolean) ?? [];
    router.get('/search', params, { preserveScroll: true, preserveState: true, replace: true });
};

const setPriceRange = (range: { min: number | null; max: number | null }) => {
    filterState.price_min = range?.min ?? null;
    filterState.price_max = range?.max ?? null;
    applyFilters();
};

const resetFilters = () => {
    filterState.status = null;
    filterState.seller_type = null;
    filterState.item_type = null;
    filterState.price_min = null;
    filterState.price_max = null;
    filterState.location = null;
    filterState.sort = 'latest';
    filterState.rating = null;
    filterState.badges = [];
    applyFilters();
};
</script>

<template>
    <div class="bg-slate-50">
        <Head :title="`Hasil Pencarian - ${props.appName ?? 'TP-PKK Marketplace'}`" />

        <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <Link href="/" class="text-sky-600 hover:underline">Beranda</Link>
                <span>/</span>
                <span class="text-slate-900">Hasil Pencarian</span>
            </nav>

            <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-12">
                <aside class="space-y-4 md:col-span-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
                            <span>Filter</span>
                            <button class="text-xs font-semibold text-sky-600" type="button" @click="resetFilters">Reset</button>
                        </div>

                        <div class="mt-4 space-y-3">
                            <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                                    Badge
                                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </summary>
                                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                                    <label v-for="badge in props.options?.badgeOptions ?? []" :key="badge.value"
                                        class="flex cursor-pointer items-center gap-2">
                                        <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="checkbox"
                                            :value="badge.value" :checked="filterState.badges.includes(badge.value)"
                                            @change="() => {
                                                if (filterState.badges.includes(badge.value)) {
                                                    filterState.badges = filterState.badges.filter((b: string) => b !== badge.value);
                                                } else {
                                                    filterState.badges = [...filterState.badges, badge.value];
                                                }
                                                applyFilters();
                                            }" />
                                        <span>{{ badge.label }}</span>
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
                                        <span>Semua lokasi</span>
                                    </label>
                                    <label v-for="loc in props.options?.locations ?? []" :key="loc"
                                        class="flex cursor-pointer items-center gap-2">
                                        <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                                            name="location" :value="loc" :checked="filterState.location === loc"
                                            @change="filterState.location = loc; applyFilters()" />
                                        <span>{{ loc }}</span>
                                    </label>
                                </div>
                            </details>

                            <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
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
                                    <label v-for="seller in props.options?.sellerTypes ?? []" :key="seller.value"
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
                                    Rating Toko
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
                                    <label v-for="ratingOpt in props.options?.ratingOptions ?? []" :key="ratingOpt.value"
                                        class="flex cursor-pointer items-center gap-2">
                                        <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                                            name="rating" :value="ratingOpt.value" :checked="filterState.rating === ratingOpt.value"
                                            @change="filterState.rating = ratingOpt.value; applyFilters()" />
                                        <span>{{ ratingOpt.label }}</span>
                                    </label>
                                </div>
                            </details>

                            <details class="group rounded-lg border border-slate-100 bg-slate-50/60" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                                    Status Produk
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
                                    <label v-for="status in props.options?.statuses ?? []" :key="status.value"
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
                                    Tipe Barang
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
                                    <label v-for="type in props.options?.itemTypes ?? []" :key="type.value"
                                        class="flex cursor-pointer items-center gap-2">
                                        <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                                            name="item_type" :value="type.value" :checked="filterState.item_type === type.value"
                                            @change="filterState.item_type = type.value; applyFilters()" />
                                        <span>{{ type.label }}</span>
                                    </label>
                                </div>
                            </details>

                            <details class="group rounded-lg border border-slate-100 bg-slate-50/60">
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
                                            @change="setPriceRange({ min: null, max: null })" />
                                        <span>Semua</span>
                                    </label>
                                    <label v-for="range in props.options?.priceRanges ?? []" :key="range.label"
                                        class="flex cursor-pointer items-center gap-2">
                                        <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="radio"
                                            name="price_range" :value="range.label"
                                            :checked="filterState.price_min === range.min && filterState.price_max === range.max"
                                            @change="setPriceRange(range)" />
                                        <span>{{ range.label }}</span>
                                    </label>
                                </div>
                            </details>
                        </div>
                    </div>
                </aside>

                <section class="space-y-4 md:col-span-9">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm text-slate-500">Menampilkan hasil untuk:</p>
                            <h1 class="text-2xl font-semibold text-slate-900">“{{ filterState.q || 'Semua' }}”</h1>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span>Urutkan:</span>
                            <select v-model="filterState.sort" @change="applyFilters()"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <option v-for="opt in props.options?.sortOptions ?? []" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <article
                            v-for="product in items"
                            :key="product.id"
                            class="flex h-full flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                        >
                            <Link :href="product.url || `/product/${product.slug}/${product.id}`" class="block">
                                <div class="relative h-40 w-full overflow-hidden bg-slate-100">
                                    <img
                                        :src="product.image_url || `https://picsum.photos/seed/product-${product.id}/400/300`"
                                        :alt="product.name"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <div class="flex flex-1 flex-col gap-2 p-4">
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ product.name }}</p>
                                    <p class="text-base font-bold text-slate-900">
                                        {{ product.sale_price ? formatPrice(product.sale_price) : formatPrice(product.price) }}
                                    </p>
                                    <p class="text-xs text-slate-500">Lokasi: {{ locationLabel(product) }}</p>
                                    <p v-if="product.store" class="text-xs text-slate-500">Toko: {{ product.store.name }}</p>
                                </div>
                            </Link>
                        </article>
                    </div>

                    <div v-if="!items.length" class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-600">
                        Tidak ada produk yang ditemukan.
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-2" v-if="products?.links?.length">
                        <Link
                            v-for="(link, index) in products.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'rounded-lg border px-3 py-1.5 text-sm font-semibold transition',
                                link.active
                                    ? 'border-sky-500 bg-sky-50 text-sky-700'
                                    : link.url
                                      ? 'border-slate-200 text-slate-600 hover:border-sky-300 hover:text-sky-700'
                                      : 'cursor-not-allowed border-slate-100 text-slate-400',
                            ]"
                            :preserve-state="true"
                            :preserve-scroll="true"
                        />
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
