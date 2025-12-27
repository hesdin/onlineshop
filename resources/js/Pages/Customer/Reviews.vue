<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import AlertBanner from '@/components/AlertBanner.vue';

defineOptions({
  layout: LandingLayout,
});

const props = defineProps({
  pendingReviews: {
    type: Array,
    default: () => [],
  },
  completedReviews: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const flashSuccess = computed(() => flash.value.success ?? '');
const showSuccess = ref(false);

// Watch flash for success messages with auto-hide
watch(() => page.props.flash, (newFlash) => {
  if (newFlash?.success) {
    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 5000);
  }
}, { deep: true, immediate: true });

const activeTab = ref('pending');

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(
    Number(value ?? 0),
  );

const formatDate = (value) => {
  if (!value) return '-';
  const date = new Date(value);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Review Modal
const showReviewModal = ref(false);
const selectedItem = ref(null);
const reviewForm = ref({
  rating: 5,
  comment: '',
});
const isSubmitting = ref(false);

const openReviewModal = (item) => {
  selectedItem.value = item;
  reviewForm.value = { rating: 5, comment: '' };
  showReviewModal.value = true;
};

const closeReviewModal = () => {
  showReviewModal.value = false;
  selectedItem.value = null;
};

const setRating = (rating) => {
  reviewForm.value.rating = rating;
};

const submitReview = () => {
  if (!selectedItem.value || isSubmitting.value) return;

  isSubmitting.value = true;

  router.post('/customer/dashboard/reviews', {
    order_item_id: selectedItem.value.id,
    rating: reviewForm.value.rating,
    comment: reviewForm.value.comment,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeReviewModal();
    },
    onError: (errors) => {
      console.error('Review submission failed:', errors);
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};
</script>

<template>
  <div class="bg-slate-50 min-h-screen">

    <Head title="Ulasan Saya" />

    <div class="mx-auto flex max-w-full flex-col gap-6 px-4 py-8">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm font-medium text-slate-500">
        <a href="/" class="transition hover:text-slate-800">Beranda</a>
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
            clip-rule="evenodd" />
        </svg>
        <span class="text-slate-900">Ulasan</span>
      </nav>

      <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
        <CustomerSidebarMenu active-key="ulasan" />

        <main class="space-y-6">
          <section class="max-w-4xl space-y-6">
            <!-- Header Section -->
            <div>
              <h1 class="text-2xl font-bold text-slate-900">Ulasan Saya</h1>
              <p class="mt-1 text-sm text-slate-500">Berikan ulasan untuk produk yang sudah Anda beli</p>
            </div>

            <!-- Floating Success Alert -->
            <Teleport to="body">
              <div v-if="showSuccess && flashSuccess"
                class="fixed top-20 left-1/2 -translate-x-1/2 z-[9999] min-w-[400px] max-w-xl shadow-lg rounded-lg overflow-hidden">
                <AlertBanner type="success" :message="flashSuccess" :show="showSuccess" :dismissible="true"
                  @close="showSuccess = false" />
              </div>
            </Teleport>

            <!-- Tabs -->
            <div class="border-b border-slate-200">
              <nav class="flex gap-8">
                <button @click="activeTab = 'pending'" :class="[
                  'py-3 text-sm font-semibold border-b-2 transition-colors',
                  activeTab === 'pending'
                    ? 'border-sky-600 text-sky-600'
                    : 'border-transparent text-slate-500 hover:text-slate-700'
                ]">
                  Menunggu Ulasan
                  <span v-if="props.pendingReviews.length"
                    class="ml-1.5 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700">
                    {{ props.pendingReviews.length }}
                  </span>
                </button>
                <button @click="activeTab = 'completed'" :class="[
                  'py-3 text-sm font-semibold border-b-2 transition-colors',
                  activeTab === 'completed'
                    ? 'border-sky-600 text-sky-600'
                    : 'border-transparent text-slate-500 hover:text-slate-700'
                ]">
                  Riwayat Ulasan
                  <span v-if="props.completedReviews.length"
                    class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">
                    {{ props.completedReviews.length }}
                  </span>
                </button>
              </nav>
            </div>

            <!-- Pending Reviews Tab -->
            <div v-if="activeTab === 'pending'" class="space-y-4">
              <div v-if="props.pendingReviews.length" class="space-y-4">
                <div v-for="item in props.pendingReviews" :key="item.id"
                  class="flex gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                  <!-- Product Image -->
                  <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                    <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product?.name"
                      class="h-full w-full object-cover">
                    <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                      <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>

                  <!-- Product Info -->
                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-slate-900 line-clamp-2">{{ item.product?.name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">
                      {{ item.order?.store_name }} • {{ formatDate(item.order?.created_at) }}
                    </p>
                    <p class="mt-1 text-sm text-slate-600">
                      {{ item.quantity }} x {{ formatPrice(item.unit_price) }}
                    </p>
                  </div>

                  <!-- Action -->
                  <div class="flex items-center">
                    <button @click="openReviewModal(item)"
                      class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white shadow hover:bg-sky-700 transition-colors">
                      Beri Ulasan
                    </button>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else
                class="flex min-h-[300px] flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                  <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Tidak ada ulasan yang pending</h3>
                <p class="mt-2 max-w-sm text-slate-500">
                  Semua produk sudah diulas atau belum ada pesanan yang selesai.
                </p>
              </div>
            </div>

            <!-- Completed Reviews Tab -->
            <div v-if="activeTab === 'completed'" class="space-y-4">
              <div v-if="props.completedReviews.length" class="space-y-4">
                <div v-for="review in props.completedReviews" :key="review.id"
                  class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                  <div class="flex gap-4">
                    <!-- Product Image -->
                    <div
                      class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                      <img v-if="review.product?.image_url" :src="review.product.image_url" :alt="review.product?.name"
                        class="h-full w-full object-cover">
                      <div v-else class="flex h-full w-full items-center justify-center text-slate-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                      </div>
                    </div>

                    <!-- Review Info -->
                    <div class="flex-1 min-w-0">
                      <h3 class="font-semibold text-slate-900 line-clamp-1">{{ review.product?.name }}</h3>
                      <div class="mt-1 flex items-center gap-1">
                        <template v-for="i in 5" :key="i">
                          <svg :class="i <= review.rating ? 'text-amber-400' : 'text-slate-200'" class="h-4 w-4"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </template>
                        <span class="ml-2 text-xs text-slate-500">{{ formatDate(review.created_at) }}</span>
                      </div>
                      <p v-if="review.comment" class="mt-2 text-sm text-slate-600 line-clamp-3">{{ review.comment }}</p>
                      <p v-else class="mt-2 text-sm text-slate-400 italic">Tidak ada komentar</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else
                class="flex min-h-[300px] flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                  <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Belum ada ulasan</h3>
                <p class="mt-2 max-w-sm text-slate-500">
                  Anda belum memberikan ulasan untuk produk apapun.
                </p>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>

    <!-- Review Modal -->
    <Dialog :open="showReviewModal" @update:open="showReviewModal = $event">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Beri Ulasan</DialogTitle>
          <DialogDescription v-if="selectedItem">
            {{ selectedItem.product?.name }}
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <!-- Star Rating -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-900">Rating</label>
            <div class="flex items-center gap-1">
              <template v-for="i in 5" :key="i">
                <button @click="setRating(i)" type="button"
                  class="focus:outline-none transition-transform hover:scale-110">
                  <svg class="h-7 w-7" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                      :fill="i <= reviewForm.rating ? '#FBBF24' : 'white'"
                      :stroke="i <= reviewForm.rating ? '#FBBF24' : '#1e293b'" stroke-width="1" />
                  </svg>
                </button>
              </template>
              <span class="ml-2 text-sm font-medium text-slate-600">{{ reviewForm.rating }}/5</span>
            </div>
          </div>

          <!-- Comment -->
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-900">Komentar (opsional)</label>
            <Textarea v-model="reviewForm.comment" placeholder="Bagaimana pengalaman Anda dengan produk ini?" rows="4"
              class="resize-none" />
          </div>
        </div>

        <DialogFooter class="gap-2 sm:gap-0">
          <Button variant="outline" @click="closeReviewModal" :disabled="isSubmitting">
            Batal
          </Button>
          <Button @click="submitReview" :disabled="isSubmitting" class="bg-sky-600 hover:bg-sky-700">
            <svg v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
            </svg>
            {{ isSubmitting ? 'Mengirim...' : 'Kirim Ulasan' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
