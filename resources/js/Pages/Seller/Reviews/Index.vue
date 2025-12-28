<script setup lang="ts">
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Star, Package, TrendingUp, MessageSquare } from 'lucide-vue-next';

interface ReviewUser {
  name: string;
  initial: string;
}

interface ReviewProduct {
  id: number;
  name: string;
  slug: string;
}

interface ReviewOrder {
  id: number;
  order_number: string;
}

interface Review {
  id: number;
  rating: number;
  comment: string | null;
  created_at: string;
  created_at_human: string;
  user: ReviewUser;
  product: ReviewProduct | null;
  order: ReviewOrder | null;
}

interface Summary {
  average_rating: number;
  total_reviews: number;
  new_reviews_count: number;
  distribution: Record<number, number>;
}

interface Filters {
  rating: number | null;
  sort: string;
}

const props = defineProps<{
  summary: Summary;
  reviews: {
    data: Review[];
    current_page?: number;
    last_page?: number;
    prev_page_url?: string | null;
    next_page_url?: string | null;
  };
  filters: Filters;
}>();

defineOptions({
  layout: SellerDashboardLayout,
});

const ratingTabs = [
  { label: 'Semua', value: null },
  { label: '⭐ 5', value: 5 },
  { label: '⭐ 4', value: 4 },
  { label: '⭐ 3', value: 3 },
  { label: '⭐ 2', value: 2 },
  { label: '⭐ 1', value: 1 },
];

const sortOptions = [
  { label: 'Terbaru', value: 'latest' },
  { label: 'Rating Tertinggi', value: 'rating_high' },
  { label: 'Rating Terendah', value: 'rating_low' },
];

const getDistributionPercentage = (rating: number) => {
  const total = props.summary.total_reviews;
  if (!total) return 0;
  const count = props.summary.distribution?.[rating] ?? 0;
  return Math.round((count / total) * 100);
};

const applyFilters = (overrides: Partial<Filters> = {}) => {
  const payload = {
    rating: overrides.rating !== undefined ? overrides.rating : props.filters.rating,
    sort: overrides.sort !== undefined ? overrides.sort : props.filters.sort,
  };
  router.get('/seller/reviews', payload, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

const changePage = (url: string | null | undefined) => {
  if (!url) return;
  router.get(url, {}, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <div class="space-y-6">

    <Head title="Ulasan Toko" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Ulasan Toko</h1>
        <p class="mt-1 text-sm text-slate-500">Lihat dan kelola ulasan dari pelanggan Anda</p>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Average Rating -->
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center rounded-lg bg-amber-50">
            <Star class="h-6 w-6 text-amber-500" fill="currentColor" />
          </div>
          <div>
            <p class="text-sm text-slate-500">Rata-rata Rating</p>
            <p class="text-2xl font-bold text-slate-900">{{ summary.average_rating }}</p>
          </div>
        </div>
      </div>

      <!-- Total Reviews -->
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center rounded-lg bg-sky-50">
            <MessageSquare class="h-6 w-6 text-sky-500" />
          </div>
          <div>
            <p class="text-sm text-slate-500">Total Ulasan</p>
            <p class="text-2xl font-bold text-slate-900">{{ summary.total_reviews }}</p>
          </div>
        </div>
      </div>

      <!-- New Reviews -->
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="flex items-center gap-3">
          <div class="grid h-12 w-12 place-items-center rounded-lg bg-emerald-50">
            <TrendingUp class="h-6 w-6 text-emerald-500" />
          </div>
          <div>
            <p class="text-sm text-slate-500">Ulasan Baru (7 hari)</p>
            <p class="text-2xl font-bold text-slate-900">{{ summary.new_reviews_count }}</p>
          </div>
        </div>
      </div>

      <!-- Rating Distribution -->
      <div class="rounded-xl border border-slate-200 bg-white p-5">
        <p class="text-sm font-medium text-slate-700 mb-3">Distribusi Rating</p>
        <div class="space-y-1.5">
          <template v-for="rating in [5, 4, 3, 2, 1]" :key="rating">
            <div class="flex items-center gap-2 text-xs">
              <span class="w-3 text-slate-500">{{ rating }}</span>
              <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full transition-all"
                  :style="{ width: `${getDistributionPercentage(rating)}%` }"></div>
              </div>
              <span class="w-6 text-right text-slate-400">{{ summary.distribution?.[rating] ?? 0 }}</span>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Filters & Reviews List -->
    <div class="rounded-xl border border-slate-200 bg-white">
      <!-- Filters -->
      <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
        <!-- Rating Tabs -->
        <div class="flex flex-wrap gap-2">
          <button v-for="tab in ratingTabs" :key="tab.value" @click="applyFilters({ rating: tab.value })"
            class="px-3 py-1.5 text-sm font-medium rounded-full border transition-colors" :class="filters.rating === tab.value
              ? 'bg-sky-500 text-white border-sky-500'
              : 'bg-white text-slate-600 border-slate-200 hover:border-sky-300'">
            {{ tab.label }}
          </button>
        </div>

        <!-- Sort -->
        <select v-model="filters.sort" @change="applyFilters({ sort: ($event.target as HTMLSelectElement).value })"
          class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:ring-2 focus:ring-sky-500">
          <option v-for="option in sortOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>

      <!-- Reviews List -->
      <div v-if="reviews.data.length" class="divide-y divide-slate-100">
        <div v-for="review in reviews.data" :key="review.id" class="p-5">
          <div class="flex gap-4">
            <!-- Avatar -->
            <div
              class="size-11 rounded-full bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
              {{ review.user?.initial ?? 'P' }}
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                  <p class="font-semibold text-slate-900">{{ review.user?.name }}</p>
                  <!-- Stars -->
                  <div class="flex items-center gap-0.5">
                    <template v-for="i in 5" :key="i">
                      <svg class="size-4" viewBox="0 0 20 20"
                        :class="i <= review.rating ? 'text-amber-400' : 'text-slate-200'" fill="currentColor">
                        <path
                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </template>
                  </div>
                </div>
                <span class="text-xs text-slate-400">{{ review.created_at_human }}</span>
              </div>

              <!-- Comment -->
              <p v-if="review.comment" class="mt-2 text-sm text-slate-600">{{ review.comment }}</p>
              <p v-else class="mt-2 text-sm text-slate-400 italic">Tidak ada komentar</p>

              <!-- Product & Order info -->
              <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                <Link v-if="review.product" :href="`/seller/products/${review.product.id}/edit`"
                  class="inline-flex items-center gap-1 hover:text-sky-600">
                  <Package class="size-3.5" />
                  {{ review.product.name }}
                </Link>
                <span v-if="review.order" class="inline-flex items-center gap-1">
                  Order: #{{ review.order.order_number }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-16 px-6 text-center">
        <div class="grid h-16 w-16 place-items-center rounded-full bg-slate-100 mb-4">
          <Star class="h-8 w-8 text-slate-400" />
        </div>
        <h3 class="text-lg font-semibold text-slate-900">Belum ada ulasan</h3>
        <p class="mt-1 text-sm text-slate-500 max-w-sm">
          {{ filters.rating ? 'Tidak ada ulasan dengan rating ini.' : 'Ulasan dari pelanggan akan muncul di sini.' }}
        </p>
      </div>

      <!-- Pagination -->
      <div v-if="reviews.last_page && reviews.last_page > 1"
        class="flex items-center justify-between border-t border-slate-100 px-5 py-4">
        <button @click="changePage(reviews.prev_page_url)" :disabled="!reviews.prev_page_url"
          class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
          Sebelumnya
        </button>
        <span class="text-sm text-slate-500">
          Halaman {{ reviews.current_page }} dari {{ reviews.last_page }}
        </span>
        <button @click="changePage(reviews.next_page_url)" :disabled="!reviews.next_page_url"
          class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
          Selanjutnya
        </button>
      </div>
    </div>
  </div>
</template>
