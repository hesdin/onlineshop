<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  product: {
    type: Object,
    default: () => ({}),
  },
});

const mergeList = (incoming, fallback = []) => {
  if (Array.isArray(incoming) && incoming.length) {
    return incoming;
  }
  return fallback;
};

const normalizeGallery = (images) =>
  mergeList(images)
    .map((item) => (typeof item === 'string' ? item.trim() : ''))
    .filter((item) => item.length > 0);

const toTitleCase = (value = '') =>
  String(value)
    .split(' ')
    .map((word) => (word ? word[0].toUpperCase() + word.slice(1).toLowerCase() : ''))
    .join(' ');

const product = computed(() => {
  const incoming = props.product ?? {};
  const store = incoming.store ?? {};

  return {
    ...incoming,
    gallery: normalizeGallery(incoming.gallery),
    info: mergeList(incoming.info),
    badges: mergeList(incoming.badges),
    otherProducts: mergeList(
      (incoming.otherProducts ?? []).map((item) => ({
        ...item,
        tags: mergeList(item.tags),
      }))
    ),
    store: {
      ...store,
      highlights: mergeList(store.highlights),
    },
  };
});

const pageTitle = computed(() => `${product.value.name || 'Produk'} - ${props.appName}`);
const reviews = computed(() => mergeList(props.product?.reviews));
const hasRealReviews = computed(() => reviews.value.length > 0);

const activeIndex = ref(0);
const activeImage = computed(() => product.value.gallery?.[activeIndex.value] ?? '');
const hasProductImage = computed(() => Boolean(activeImage.value));
const isZoomed = ref(false);
const zoomOrigin = ref({ x: 50, y: 50 });
const isModalOpen = ref(false);
const modalIndex = ref(0);
const modalImage = computed(() => product.value.gallery?.[modalIndex.value] ?? '');
const activeTab = ref('description');
const currentUrl = ref(typeof window !== 'undefined' ? window.location.href : '');

const resetZoom = () => {
  zoomOrigin.value = { x: 50, y: 50 };
  isZoomed.value = false;
};

const updateZoomFromEvent = (event) => {
  if (!hasProductImage.value) return;
  const rect = event.currentTarget.getBoundingClientRect();
  const x = ((event.clientX - rect.left) / rect.width) * 100;
  const y = ((event.clientY - rect.top) / rect.height) * 100;

  zoomOrigin.value = {
    x: Math.min(100, Math.max(0, x)),
    y: Math.min(100, Math.max(0, y)),
  };
};

const handleImageEnter = (event) => {
  if (!hasProductImage.value) return;
  isZoomed.value = true;
  updateZoomFromEvent(event);
};

const handleImageLeave = () => {
  if (!hasProductImage.value) return;
  isZoomed.value = false;
};

const zoomImageStyle = computed(() => ({
  transformOrigin: `${zoomOrigin.value.x}% ${zoomOrigin.value.y}%`,
  transform: isZoomed.value ? 'scale(1.7)' : 'scale(1)',
}));

const selectImage = (index) => {
  if (!product.value.gallery?.length) return;
  activeIndex.value = index;
  if (isModalOpen.value) {
    modalIndex.value = index;
  }
  resetZoom();
};

watch(
  product,
  () => {
    activeIndex.value = 0;
    modalIndex.value = 0;
    activeTab.value = 'description';
    if (typeof window !== 'undefined') {
      currentUrl.value = window.location.href;
    }
    resetZoom();
  },
  { immediate: true }
);

watch(
  modalIndex,
  (value) => {
    if (!isModalOpen.value) return;
    activeIndex.value = value;
    resetZoom();
  }
);

const getSafeIndex = (index) => {
  const length = product.value.gallery?.length ?? 0;
  if (!length) return 0;
  return (index + length) % length;
};

const openModal = () => {
  if (!product.value.gallery?.length) return;
  modalIndex.value = activeIndex.value;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const nextModalImage = () => {
  modalIndex.value = getSafeIndex(modalIndex.value + 1);
};

const prevModalImage = () => {
  modalIndex.value = getSafeIndex(modalIndex.value - 1);
};

const handleKeydown = (event) => {
  if (!isModalOpen.value) return;
  if (event.key === 'Escape') {
    closeModal();
  } else if (event.key === 'ArrowRight') {
    event.preventDefault();
    nextModalImage();
  } else if (event.key === 'ArrowLeft') {
    event.preventDefault();
    prevModalImage();
  }
};

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const minOrderQuantity = computed(() => {
  const raw = product.value.minOrderQuantity ?? product.value.min_order ?? 1;
  const parsed = Number.parseInt(raw, 10);
  return Number.isNaN(parsed) ? 1 : Math.max(1, parsed);
});

const stockAvailable = computed(() => (product.value.stock ?? null));
const isOutOfStock = computed(() => stockAvailable.value !== null && stockAvailable.value <= 0);

const quantity = ref(minOrderQuantity.value);
const addToCartForm = useForm({
  product_id: product.value.id ?? null,
  quantity: quantity.value,
  note: '',
});
const cartError = ref('');
const cartNotifications = ref([]);
const cartNotificationTimers = new Map();
const CART_NOTIFICATION_DURATION = 3500;
const cartLoadingVisible = ref(false);
const cartLoadingStartedAt = ref(0);
const cartLoadingHideTimeout = ref(null);
const MIN_CART_LOADING_MS = 700;
const isBuyingNow = ref(false);
const forceHideCartLoadingOverlay = () => {
  if (cartLoadingHideTimeout.value) {
    clearTimeout(cartLoadingHideTimeout.value);
    cartLoadingHideTimeout.value = null;
  }
  cartLoadingVisible.value = false;
};

// loading hanya mengandalkan state bawaan Inertia form
const isAddingToCart = computed(() => addToCartForm.processing);

const syncQuantity = (value) => {
  addToCartForm.quantity = value;
};

const setQuantity = (value) => {
  if (stockAvailable.value !== null && stockAvailable.value <= 0) {
    quantity.value = 0;
    syncQuantity(0);
    return;
  }

  const min = minOrderQuantity.value;
  const numericValue = Number.isNaN(Number(value)) ? min : Number(value);
  let next = Math.max(min, numericValue);

  if (stockAvailable.value !== null) {
    next = Math.min(next, stockAvailable.value);
  }

  quantity.value = next;
  syncQuantity(next);
  cartError.value = '';
  addToCartForm.clearErrors();
};

watch(
  product,
  () => {
    addToCartForm.product_id = product.value.id ?? null;
    setQuantity(minOrderQuantity.value);
    addToCartForm.note = '';
    addToCartForm.clearErrors();
    cartError.value = '';
  },
  { immediate: true }
);

watch(quantity, (value) => syncQuantity(value));

watch(
  () => addToCartForm.processing,
  (processing) => {
    if (processing) {
      if (cartLoadingHideTimeout.value) {
        clearTimeout(cartLoadingHideTimeout.value);
        cartLoadingHideTimeout.value = null;
      }
      cartLoadingStartedAt.value = Date.now();
      cartLoadingVisible.value = true;
      return;
    }

    const elapsed = Date.now() - cartLoadingStartedAt.value;
    const remaining = Math.max(0, MIN_CART_LOADING_MS - elapsed);

    cartLoadingHideTimeout.value = setTimeout(() => {
      forceHideCartLoadingOverlay();
    }, remaining);
  }
);

const changeQuantity = (delta) => {
  setQuantity((quantity.value || 0) + delta);
};

const removeCartNotification = (id) => {
  const timer = cartNotificationTimers.get(id);
  if (timer) {
    clearTimeout(timer);
    cartNotificationTimers.delete(id);
  }
  cartNotifications.value = cartNotifications.value.filter((item) => item.id !== id);
};

const queueCartNotification = (payload = {}) => {
  const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  const gallery = product.value.gallery ?? [];
  const type = payload.type ?? 'success';
  const notification = {
    id,
    type,
    message: payload.message ?? 'Produk masuk keranjang',
    name: payload.name ?? product.value.name ?? 'Produk',
    image: (payload.image ?? gallery[0]) || '',
    price: payload.price ?? product.value.price ?? 0,
    quantity: payload.quantity ?? addToCartForm.quantity,
    detail: payload.detail ?? null,
    showAction: payload.showAction ?? type === 'success',
  };

  cartNotifications.value = [notification, ...cartNotifications.value];

  const timer = setTimeout(() => {
    removeCartNotification(id);
  }, payload.duration ?? CART_NOTIFICATION_DURATION);
  cartNotificationTimers.set(id, timer);
};

const clearAllCartNotifications = () => {
  cartNotifications.value.forEach((item) => removeCartNotification(item.id));
};

const handleQuantityInput = (event) => {
  const value = parseInt(event.target.value, 10);
  setQuantity(Number.isNaN(value) ? minOrderQuantity.value : value);
};

const submitCartRequest = (options = {}) => {
  const { onSuccess, onFinish, skipNotification = false } = options;

  if (isAddingToCart.value) {
    return false;
  }

  if (isOutOfStock.value) {
    cartError.value = 'Stok produk sedang habis.';
    queueCartNotification({
      type: 'error',
      message: 'Stok produk sedang habis.',
      detail: 'Produk tidak dapat ditambahkan ke keranjang saat ini.',
    });
    forceHideCartLoadingOverlay();
    return false;
  }

  if (stockAvailable.value !== null && quantity.value > stockAvailable.value) {
    cartError.value = 'Jumlah melebihi stok tersedia.';
    setQuantity(stockAvailable.value);
    queueCartNotification({
      type: 'error',
      message: 'Jumlah melebihi stok tersedia.',
      detail: stockAvailable.value ? `Stok tersisa ${stockAvailable.value} pcs.` : null,
    });
    forceHideCartLoadingOverlay();
    return false;
  }

  if (!isAuthenticated.value) {
    router.visit('/customer/login');
    forceHideCartLoadingOverlay();
    return false;
  }

  cartError.value = '';
  addToCartForm.clearErrors();

  addToCartForm.post('/cart', {
    preserveScroll: true,
    onSuccess: () => {
      const flash = page.props.flash ?? {};

      if (flash.error) {
        cartError.value = flash.error;
        return;
      }

      if (!skipNotification) {
        queueCartNotification();
      }

      if (typeof onSuccess === 'function') {
        onSuccess();
      }
    },
    onError: (errors) => {
      cartError.value =
        errors.quantity || errors.product_id || page.props.flash?.error || 'Gagal menambahkan ke keranjang.';
    },
    onFinish: () => {
      if (typeof onFinish === 'function') {
        onFinish();
      }
    },
  });

  return true;
};

const addToCart = () => {
  submitCartRequest();
};

const goToCheckout = () => {
  if (isBuyingNow.value) {
    return;
  }

  isBuyingNow.value = true;
  const started = submitCartRequest({
    skipNotification: true,
    onSuccess: () => router.visit('/cart/checkout'),
    onFinish: () => {
      isBuyingNow.value = false;
    },
  });

  if (!started) {
    isBuyingNow.value = false;
  }
};

const formatPrice = (value) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value ?? 0);

const shareUrl = computed(
  () => currentUrl.value || (typeof window !== 'undefined' ? window.location.href : '')
);

const shareMessage = computed(
  () => `${product.value.name ?? 'Produk'} - ${shareUrl.value || 'Cek produk ini'}`
);

const shareLinks = computed(() => {
  const url = encodeURIComponent(shareUrl.value || '');
  const text = encodeURIComponent(shareMessage.value || '');

  return {
    whatsapp: `https://wa.me/?text=${text}`,
    telegram: `https://t.me/share/url?url=${url}&text=${text}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
    x: `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
  };
});

const copyStatus = ref(false);
const formattedProductLocation = computed(() => {
  const location = product.value.location ?? '';
  if (!location) return '';

  const normalized = toTitleCase(location);
  const delimiters = [',', '-', '|'];

  for (const delimiter of delimiters) {
    if (normalized.includes(delimiter)) {
      const city = normalized.split(delimiter)[0]?.trim();
      if (city) return city;
    }
  }

  return normalized;
});

const shippingSummary = computed(() => {
  const address = page.props.customerAddress ?? null;
  const shipping = page.props.customerShipping ?? null;
  const normalizeRegion = (value) => (value ? toTitleCase(value) : '');
  const regionDetail =
    [address?.district, address?.city, address?.province].map(normalizeRegion).filter(Boolean).join(', ') || null;

  return {
    addressTitle: address?.label ?? 'Alamat pengiriman belum dipilih',
    addressDetail: regionDetail,
    courierTitle: shipping?.courier ?? shipping?.service ?? 'Pilih kurir pengiriman',
    courierCost: shipping?.cost != null ? formatPrice(shipping.cost) : null,
    courierEstimate: shipping?.estimate ?? shipping?.etd ?? null,
  };
});

const copyShareLink = async () => {
  try {
    await navigator.clipboard.writeText(shareUrl.value || '');
    copyStatus.value = true;
    setTimeout(() => {
      copyStatus.value = false;
    }, 1500);
  } catch (error) {
    copyStatus.value = false;
    console.error('Gagal menyalin link', error);
  }
};

const reviewStats = computed(() => {
  const items = reviews.value;
  const totalReviews = items.length;
  const hasReviews = totalReviews > 0;
  const sumRatings = items.reduce((total, item) => total + Number(item.rating ?? 0), 0);
  const averageFromReviews = hasReviews && totalReviews ? sumRatings / totalReviews : null;
  const fallbackAverage = Number(product.value.rating ?? 0);
  const normalizedAverage = Number(averageFromReviews ?? fallbackAverage ?? 0);
  const average = Number.parseFloat(normalizedAverage.toFixed(1));
  const total = hasReviews ? totalReviews : Number(product.value.reviewCount ?? 0);

  return {
    average: Number.isNaN(average) ? 0 : average,
    total,
  };
});

const ratingBreakdown = computed(() => {
  const total = reviews.value.length;

  return [5, 4, 3, 2, 1].map((score) => {
    const count = reviews.value.filter((review) => Math.round(review.rating ?? 0) === score).length;
    const percentage = total ? Math.round((count / total) * 100) : 0;

    return { score, count, percentage };
  });
});

const productDiscount = computed(() => {
  if (product.value.discountPercent != null) {
    return product.value.discountPercent;
  }
  if (product.value.originalPrice && product.value.originalPrice > product.value.price) {
    const diff = product.value.originalPrice - product.value.price;
    return Math.round((diff / product.value.originalPrice) * 100);
  }
  return null;
});

const totalPrice = computed(() => (product.value.price ?? 0) * quantity.value);

const ratingStars = (rating) => Array.from({ length: 5 }, (_, index) => index < Math.round(rating ?? 0));

const reviewerInitials = (name = '') => {
  const initials = name
    .split(' ')
    .filter(Boolean)
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();

  return initials || 'U';
};

const badgeClass = (badge) => {
  const normalized = String(badge).toLowerCase();
  if (normalized === 'pdn') return 'bg-emerald-50 text-emerald-700';
  if (normalized === 'pph22') return 'bg-sky-50 text-sky-700';
  if (normalized === 'non pkp' || normalized === 'pkp') {
    return 'bg-slate-100 text-slate-700';
  }
  return 'bg-slate-100 text-slate-700';
};

const productUrl = (item) => {
  const slug = item.slug ?? '';
  const id = item.id ?? '';
  if (!slug || !id) {
    return '#';
  }
  return `/product/${slug}/${id}`;
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
  if (typeof window !== 'undefined') {
    currentUrl.value = window.location.href;
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown);
  clearAllCartNotifications();
  forceHideCartLoadingOverlay();
});
</script>

<template>
  <LandingLayout>

    <Head :title="pageTitle" />

    <section class="py-4 lg:py-4">
      <div class="mx-auto space-y-6">

        <!-- Breadcrumb -->
        <!-- <nav class="flex items-center gap-2 text-xs text-slate-500">
          <Link class="text-sky-600 hover:underline" href="/">Beranda</Link>
          <span>/</span>
          <Link v-if="product.categoryUrl" class="text-sky-600 hover:underline" :href="product.categoryUrl">
          {{ product.category }}
          </Link>
          <span v-else class="text-slate-700">{{ product.category }}</span>
          <span>/</span>
          <span class="font-semibold text-slate-900">{{ product.name }}</span>
        </nav> -->

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,2.2fr)_minmax(320px,0.8fr)]">
          <div class="space-y-4 lg:space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
              <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
                <div>
                  <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
                    :class="hasProductImage ? 'cursor-zoom-in' : 'cursor-default'" @mouseenter="handleImageEnter"
                    @mouseleave="handleImageLeave" @mousemove="updateZoomFromEvent" @click="openModal">
                    <template v-if="hasProductImage">
                      <img :src="activeImage" :alt="product.name" :style="zoomImageStyle"
                        class="h-[320px] w-full object-cover transition-transform duration-200 ease-out md:h-[420px]" />
                      <div
                        class="pointer-events-none absolute right-3 top-3 hidden items-center gap-2 rounded-full bg-white/80 px-2 py-1 text-[11px] font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200/70 sm:flex">
                        <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                          stroke-width="1.6">
                          <circle cx="9" cy="9" r="5.5" />
                          <path d="m12.5 12.5 3.5 3.5" stroke-linecap="round" />
                        </svg>
                        <span>{{ isZoomed ? 'Gerakkan untuk geser' : 'Sorot untuk zoom' }}</span>
                      </div>
                    </template>
                    <div v-else
                      class="flex h-[320px] w-full items-center justify-center bg-white text-sm font-semibold text-slate-400 md:h-[420px]">
                      No Image
                    </div>
                  </div>

                  <div v-if="product.gallery?.length" class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5 md:grid-cols-6">
                    <button v-for="(thumb, index) in product.gallery" :key="thumb + index" type="button"
                      class="overflow-hidden rounded-xl border bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-sky-500/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500"
                      :class="activeIndex === index ? 'border-sky-500' : 'border-slate-200'"
                      @click="selectImage(index)">
                      <img :src="thumb" :alt="`Galeri ${index + 1}`" class="h-16 w-full object-cover" />
                    </button>
                  </div>
                </div>

                <div class="space-y-4 lg:space-y-6">
                  <div class="space-y-3 border-b border-slate-200 pb-4">
                    <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">
                      {{ product.name }}
                    </h1>

                    <div class="space-y-1">
                      <p class="text-3xl font-bold text-sky-600 sm:text-4xl">
                        {{ formatPrice(product.price) }}
                      </p>
                      <div v-if="product.originalPrice && product.originalPrice > product.price"
                        class="flex items-center gap-2 text-xs">
                        <span v-if="productDiscount"
                          class="inline-flex items-center rounded px-1 py-0.5 text-[11px] font-extrabold bg-red-100 text-red-600">
                          {{ productDiscount }}%
                        </span>
                        <span class="text-slate-400 line-through">
                          {{ formatPrice(product.originalPrice) }}
                        </span>
                      </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                      <div class="flex items-center gap-1">
                        <span class="font-semibold text-slate-900">{{ product.sold }}</span>
                        <span>Terjual</span>
                      </div>
                      <span>·</span>
                      <div class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                        </svg>
                        <span class="font-semibold text-slate-900">{{ reviewStats.average }}</span>
                        <span>({{ reviewStats.total }} Ulasan)</span>
                      </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] font-semibold">
                      <span v-for="badge in product.badges" :key="badge" class="rounded-full px-3 py-1"
                        :class="badgeClass(badge)">
                        {{ badge }}
                      </span>
                    </div>
                  </div>

                  <div class="space-y-4 border-b border-slate-200 pb-4">
                    <p class="text-base font-semibold text-slate-900">Informasi Produk</p>

                    <div class="space-y-2 text-sm">
                      <div v-for="info in product.info" :key="info.label" class="flex gap-6">
                        <div class="w-40 text-slate-400">{{ info.label }}</div>
                        <div class="flex-1 font-semibold text-slate-800">
                          <p>{{ info.value }}</p>
                          <p v-if="info.helper" class="font-normal text-slate-600">
                            {{ info.helper }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div
              class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:gap-4 sm:px-5">
              <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-800">
                <span>Bagikan Produk:</span>
                <div class="flex items-center gap-2 sm:gap-3">
                  <a :href="shareLinks.whatsapp" target="_blank" rel="noreferrer"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 transition hover:scale-[1.02] hover:ring-emerald-200">
                    <span class="sr-only">Bagikan ke WhatsApp</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-whatsapp" viewBox="0 0 16 16">
                      <path
                        d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                    </svg>
                  </a>
                  <a :href="shareLinks.telegram" target="_blank" rel="noreferrer"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-sky-50 text-sky-600 ring-1 ring-sky-100 transition hover:scale-[1.02] hover:ring-sky-200">
                    <span class="sr-only">Bagikan ke Telegram</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-telegram" viewBox="0 0 16 16">
                      <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.287 5.906q-1.168.486-4.666 2.01-.567.225-.595.442c-.03.243.275.339.69.47l.175.055c.408.133.958.288 1.243.294q.39.01.868-.32 3.269-2.206 3.374-2.23c.05-.012.12-.026.166.016s.042.12.037.141c-.03.129-1.227 1.241-1.846 1.817-.193.18-.33.307-.358.336a8 8 0 0 1-.188.186c-.38.366-.664.64.015 1.088.327.216.589.393.85.571.284.194.568.387.936.629q.14.092.27.187c.331.236.63.448.997.414.214-.02.435-.22.547-.82.265-1.417.786-4.486.906-5.751a1.4 1.4 0 0 0-.013-.315.34.34 0 0 0-.114-.217.53.53 0 0 0-.31-.093c-.3.005-.763.166-2.984 1.09" />
                    </svg>
                  </a>
                  <a :href="shareLinks.facebook" target="_blank" rel="noreferrer"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-700 ring-1 ring-slate-100 transition hover:scale-[1.02] hover:ring-slate-200">
                    <span class="sr-only">Bagikan ke Facebook</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-facebook" viewBox="0 0 16 16">
                      <path
                        d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951" />
                    </svg>
                  </a>
                  <a :href="shareLinks.x" target="_blank" rel="noreferrer"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-700 ring-1 ring-slate-100 transition hover:scale-[1.02] hover:ring-slate-200">
                    <span class="sr-only">Bagikan ke X</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-twitter-x" viewBox="0 0 16 16">
                      <path
                        d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
                    </svg>
                  </a>
                  <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-700 ring-1 ring-slate-100 transition hover:scale-[1.02] hover:ring-slate-200"
                    @click="copyShareLink">
                    <span class="sr-only">Salin tautan</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                      class="bi bi-link-45deg" viewBox="0 0 16 16">
                      <path
                        d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1 1 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4 4 0 0 1-.128-1.287z" />
                      <path
                        d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243z" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                  class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-1.5 text-sm font-semibold text-sky-700 transition hover:border-sky-300 hover:bg-white">
                  <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="m7 5 6 5-6 5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 5v10" stroke-linecap="round" />
                  </svg>
                  <span>Bandingkan Produk</span>
                </button>
                <span v-if="copyStatus" class="text-xs font-semibold text-emerald-600">Link disalin</span>
              </div>
            </div>

            <!-- Store -->
            <component :is="product.store.url ? Link : 'div'" :href="product.store.url || undefined"
              class="block space-y-4 rounded-lg border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:border-sky-200 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/60 sm:px-5 sm:py-5">
              <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                <div class="relative">
                  <img class="h-12 w-12 rounded-full border border-slate-200 object-cover" :src="product.store.avatar"
                    :alt="product.store.name" />
                </div>
                <div class="flex-1 min-w-[200px]">
                  <div class="flex flex-wrap items-center gap-2">
                    <p class="text-base font-semibold text-slate-900 sm:text-lg">{{ product.store.name }}</p>
                  </div>
                  <div class="flex items-center text-xs text-slate-600">
                    <svg class="h-4 w-4 text-sky-500" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M10 2.5a5.25 5.25 0 0 0-5.25 5.25c0 3.714 4.338 7.458 4.83 7.88a.56.56 0 0 0 .72 0c.492-.422 4.95-4.166 4.95-7.88A5.25 5.25 0 0 0 10 2.5zm0 7.25a2 2 0 1 1 0-4 2 2 0 0 1 0 4z" />
                    </svg>
                    <span>{{ product.store.location || 'Lokasi tidak tersedia' }}</span>
                  </div>
                </div>
              </div>

              <div
                class="grid grid-cols-2 gap-3 border-t border-slate-200 pt-3 sm:grid-cols-3 sm:gap-0 sm:divide-x sm:divide-slate-200">
                <div class="flex items-center justify-center gap-3 px-1 text-slate-700">
                  <svg class="h-5 w-5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8">
                    <path d="M6 13.5 11 7l3 4 4-5.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M4 17h16" stroke-linecap="round" />
                  </svg>
                  <div class="text-left">
                    <p class="text-base font-semibold text-slate-900">{{ product.store.transactionsCount }}</p>
                    <p class="text-xs text-slate-500">Transaksi Selesai</p>
                  </div>
                </div>
                <div class="flex items-center justify-center gap-3 px-1 text-slate-700 sm:px-4">
                  <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                  </svg>
                  <div class="text-left">
                    <p class="text-base font-semibold text-slate-900">
                      {{ product.store.rating ?? product.rating }}
                    </p>
                    <p class="text-xs text-slate-500">Rating &amp; Ulasan</p>
                  </div>
                </div>
                <div class="hidden items-center justify-center gap-3 px-1 text-slate-700 sm:flex sm:px-4">
                  <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M8.5 12h7M12 8.5v7" stroke-linecap="round" />
                  </svg>
                  <div class="text-left">
                    <p class="text-base font-semibold text-slate-900">{{ product.store.highlights[0] || 'Aktif' }}</p>
                    <p class="text-xs text-slate-500">Status Toko</p>
                  </div>
                </div>
              </div>
            </component>

            <div class="rounded-lg border border-slate-300 bg-white">
              <div
                class="flex gap-6 border-b border-slate-200 px-4 py-4 text-sm font-semibold text-slate-500 sm:px-5 sm:py-5">
                <button type="button" class="border-b-2 px-1 pb-1 transition"
                  :class="activeTab === 'description' ? 'border-sky-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                  @click="activeTab = 'description'">
                  Deskripsi Produk
                </button>
                <button type="button" class="border-b-2 px-1 pb-1 transition"
                  :class="activeTab === 'reviews' ? 'border-sky-500 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800'"
                  @click="activeTab = 'reviews'">
                  Review ({{ reviewStats.total }})
                </button>
              </div>

              <div v-if="activeTab === 'description'"
                class="space-y-2 px-4 pb-5 pt-4 text-sm text-slate-600 sm:px-5 sm:pb-6">
                <p v-if="product.description">
                  {{ product.description }}
                </p>
                <p v-else class="text-slate-400">Deskripsi produk belum tersedia.</p>
              </div>

              <div v-else class="space-y-4 px-4 pb-5 pt-4 text-sm text-slate-700 sm:px-5 sm:pb-6">
                <div class="grid gap-4 rounded-lg border border-slate-100 bg-slate-50 p-4 sm:grid-cols-[180px_1fr]">
                  <div class="flex flex-col items-center justify-center rounded-lg bg-white p-4 text-center">
                    <p class="text-3xl font-bold text-slate-900">{{ reviewStats.average }}</p>
                    <div class="flex items-center gap-0.5 text-amber-400">
                      <svg v-for="(star, index) in ratingStars(reviewStats.average)" :key="'avg-star-' + index"
                        class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                      </svg>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ reviewStats.total }} Ulasan</p>
                  </div>

                  <div class="space-y-2">
                    <div v-for="row in ratingBreakdown" :key="row.score" class="flex items-center gap-3">
                      <span class="w-10 text-xs font-semibold text-slate-600">{{ row.score }}★</span>
                      <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-amber-400" :style="{ width: `${row.percentage}%` }" />
                      </div>
                      <span class="w-10 text-right text-xs text-slate-500">{{ row.count }}</span>
                    </div>
                  </div>
                </div>

                <p v-if="!hasRealReviews" class="text-xs text-slate-500">
                  Belum ada ulasan untuk produk ini.
                </p>

                <div class="space-y-3">
                  <article v-for="review in reviews" :key="review.id ?? review.user"
                    class="rounded-lg border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                      <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                        {{ reviewerInitials(review.user) }}
                      </div>
                      <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-900">{{ review.user }}</p>
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                          <div class="flex items-center gap-0.5 text-amber-400">
                            <svg v-for="(star, index) in ratingStars(review.rating)"
                              :key="`review-star-${review.id}-${index}`" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                              fill="currentColor">
                              <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                            </svg>
                          </div>
                          <span class="text-slate-400">•</span>
                          <span>{{ review.date }}</span>
                          <template v-if="review.variant">
                            <span class="text-slate-400">•</span>
                            <span>{{ review.variant }}</span>
                          </template>
                        </div>
                      </div>
                    </div>

                    <p class="mt-3 text-sm text-slate-700">{{ review.comment }}</p>

                    <div v-if="review.images?.length" class="mt-3 flex flex-wrap gap-2">
                      <img v-for="(image, idx) in review.images" :key="image + idx" :src="image"
                        class="h-16 w-16 rounded-md object-cover ring-1 ring-slate-200"
                        :alt="`Foto review ${idx + 1}`" />
                    </div>

                    <div v-if="review.reply" class="mt-3 rounded-md bg-slate-50 p-3 text-xs text-slate-700">
                      <p class="font-semibold text-slate-900">{{ review.reply.from }}</p>
                      <p class="mt-1 text-slate-700">{{ review.reply.message }}</p>
                      <p class="mt-1 text-slate-500">{{ review.reply.date }}</p>
                    </div>
                  </article>
                </div>
              </div>
            </div>
          </div>

          <aside class="space-y-4 lg:sticky lg:top-32 lg:z-20 lg:max-h-[calc(100vh-8rem)]">
            <div class="space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
              <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-slate-900">Atur Pembelian</p>
                <p class="text-xs font-semibold" :class="isOutOfStock ? 'text-red-600' : 'text-slate-500'">
                  Stok: {{ isOutOfStock ? 'Habis' : product.stock }}
                </p>
              </div>

              <div class="space-y-2">
                <p class="text-sm font-semibold text-slate-500">Jumlah Pembelian</p>

                <div class="inline-flex overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                  <button type="button"
                    class="px-4 py-1 text-lg text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="isOutOfStock || quantity <= minOrderQuantity" @click="changeQuantity(-1)">
                    –
                  </button>

                  <input type="number" :value="quantity" :min="minOrderQuantity" :disabled="isOutOfStock"
                    class="w-16 border-x border-slate-200 bg-white px-3 py-1 text-center text-sm font-semibold text-slate-800 focus:outline-none disabled:cursor-not-allowed"
                    @input="handleQuantityInput" />

                  <button type="button"
                    class="px-4 py-1 text-lg text-slate-600 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="isOutOfStock || (stockAvailable !== null && quantity >= stockAvailable)"
                    @click="changeQuantity(1)">
                    +
                  </button>
                </div>
                <p v-if="!isOutOfStock" class="text-xs font-semibold text-slate-500">
                  Min pembelian {{ minOrderQuantity }} pcs
                </p>
                <p v-if="isOutOfStock" class="text-xs font-semibold text-red-600">Stok produk sedang habis.</p>
              </div>

              <div v-if="cartError || addToCartForm.errors.quantity || addToCartForm.errors.product_id"
                class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">
                {{ cartError || addToCartForm.errors.quantity || addToCartForm.errors.product_id }}
              </div>

              <div class="space-y-1 pt-2">
                <p class="text-sm font-semibold text-slate-500">Total Harga</p>
                <p class="text-xl font-bold text-sky-600 sm:text-2xl">
                  {{ formatPrice(totalPrice) }}
                </p>
              </div>

              <div
                class="flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-xs text-slate-500 sm:text-sm">
                <span>{{ formatPrice(totalPrice) }}</span>
                <span class="text-slate-400">(inc. PPN)</span>
              </div>

              <div class="space-y-4 border-t border-slate-200 pt-4">
                <p class="text-sm font-semibold text-slate-500">Informasi Pengiriman</p>
                <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50/60 p-3">
                  <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate-500">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M3 10.5 4.5 5h15L21 10.5" />
                        <path d="M5 11h14v8H5z" />
                        <path d="M10 15h4" />
                      </svg>
                    </span>
                    <div>
                      <p class="text-xs text-slate-500">Dikirim dari</p>
                      <p class="text-xs font-semibold text-slate-800">
                        {{ formattedProductLocation || 'Lokasi belum tersedia' }}
                      </p>
                    </div>
                  </div>

                  <button type="button"
                    class="flex w-full items-start gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm text-slate-700 transition hover:border-sky-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                        <circle cx="12" cy="9.5" r="2.3" />
                      </svg>
                    </span>
                    <div class="flex-1">
                      <p class="font-semibold text-slate-800">{{ shippingSummary.addressTitle }}</p>
                      <p v-if="shippingSummary.addressDetail" class="text-xs text-slate-500">
                        {{ shippingSummary.addressDetail }}
                      </p>
                      <p v-else class="text-xs text-slate-400">Pilih alamat pengiriman</p>
                    </div>
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                      stroke-width="1.8">
                      <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </button>

                  <button type="button"
                    class="flex w-full items-start gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-left text-sm text-slate-700 transition hover:border-sky-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M3 6h11v9H3z" />
                        <path d="M14 10h4l2 3v2h-6z" />
                        <circle cx="7" cy="17" r="1.4" />
                        <circle cx="17" cy="17" r="1.4" />
                      </svg>
                    </span>
                    <div class="flex-1">
                      <p class="font-semibold text-slate-800">{{ shippingSummary.courierTitle }}</p>
                      <p class="text-xs text-slate-500">
                        {{ shippingSummary.courierCost || 'Belum ada biaya' }}
                        <span v-if="shippingSummary.courierEstimate" class="text-slate-400">·
                          {{ shippingSummary.courierEstimate }}</span>
                      </p>
                    </div>
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                      stroke-width="1.8">
                      <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="border-t border-slate-200 pt-4">
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-500">
                  <button type="button"
                    class="inline-flex items-center gap-2 text-slate-500 transition hover:text-slate-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                      <path d="M9 13.5 16.5 6a3 3 0 1 1 4.2 4.2l-7.5 7.5a4 4 0 0 1-5.6-5.6L15 4.7"
                        stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Catatan Penjual</span>
                  </button>
                  <span class="text-base text-slate-300">•</span>
                  <button type="button"
                    class="inline-flex items-center gap-2 text-slate-500 transition hover:text-slate-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                      <path d="M20 13.5a6.5 6.5 0 0 1-8 6.3L7 21l1.3-3.3a6.5 6.5 0 1 1 11.7-4.2Z" stroke-linecap="round"
                        stroke-linejoin="round" />
                      <path d="M10 11h.01M13 11h.01M16 11h.01" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Chat Penjual</span>
                  </button>
                </div>
              </div>

              <div class="grid gap-3 pt-1">
                <button type="button"
                  class="inline-flex items-center justify-center gap-2 rounded-lg bg-cyan-600 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="isOutOfStock || isBuyingNow || isAddingToCart" @click="goToCheckout">
                  <template v-if="isBuyingNow">
                    <svg class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                      <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
                    </svg>
                  </template>
                  <span>{{ isBuyingNow ? 'Memproses...' : 'Beli Langsung' }}</span>
                </button>

                <button type="button"
                  class="inline-flex items-center justify-center gap-2 rounded-lg border border-sky-500 bg-white py-2.5 text-sm font-semibold text-sky-600 transition hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-60"
                  :disabled="isOutOfStock || isAddingToCart" @click="addToCart">
                  <template v-if="isAddingToCart">
                    <svg class="h-4 w-4 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                      <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
                    </svg>
                  </template>
                  <template v-else>
                    <span class="text-lg leading-none">+</span>
                  </template>
                  <span>{{ isAddingToCart ? 'Loading...' : 'Keranjang' }}</span>
                </button>
              </div>
            </div>
          </aside>
        </div>

        <section class="mt-6 lg:mt-8">
          <div class="mb-4 flex items-center justify-between px-1 sm:px-0">
            <h2 class="text-lg font-semibold text-slate-900">
              Lainnya Di Toko ini
            </h2>
            <Link v-if="product.store.url" :href="product.store.url"
              class="inline-flex items-center gap-1 text-sm font-semibold text-sky-600 hover:text-sky-700">
              <span>Lihat Semua</span>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7.25 4.75L12.5 10l-5.25 5.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                  stroke-linejoin="round" fill="none" />
              </svg>
            </Link>
            <button v-else type="button" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-400"
              disabled>
              <span>Lihat Semua</span>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7.25 4.75L12.5 10l-5.25 5.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                  stroke-linejoin="round" fill="none" />
              </svg>
            </button>
          </div>

          <div class="relative -mx-4 overflow-x-auto pb-4">
            <div class="flex gap-4 px-4">
              <Link v-for="item in product.otherProducts" :key="item.id" :href="productUrl(item)"
                class="min-w-[210px] max-w-[230px] flex-1 group block">
                <article
                  class="h-full rounded-lg border border-slate-200 bg-white transition hover:border-sky-300 hover:shadow-md">
                  <div class="overflow-hidden rounded-t-lg h-40 bg-slate-50 flex items-center justify-center">
                    <img v-if="item.image" :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
                    <span v-else class="text-xs text-slate-400 font-medium">No Image</span>
                  </div>

                  <div class="space-y-2 p-3">
                    <span v-if="item.badge"
                      class="inline-block rounded-sm bg-sky-500 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                      {{ item.badge }}
                    </span>

                    <p class="line-clamp-2 text-sm font-semibold text-slate-900">
                      {{ item.name }}
                    </p>

                    <div class="space-y-0.5 text-xs">
                      <p class="text-sm font-bold text-slate-900">
                        {{ formatPrice(item.price) }}
                      </p>
                      <div class="flex items-center gap-2">
                        <span v-if="item.originalPrice" class="text-[11px] text-slate-400 line-through">
                          {{ formatPrice(item.originalPrice) }}
                        </span>
                        <span v-if="item.discountPercent" class="text-[11px] font-semibold text-sky-600">
                          {{ item.discountPercent }}%
                        </span>
                      </div>
                    </div>

                    <div class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
                      <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          d="M10 2.5a5.25 5.25 0 00-5.25 5.25c0 3.714 4.338 7.458 4.83 7.88a.56.56 0 00.72 0c.492-.422 4.95-4.166 4.95-7.88A5.25 5.25 0 0010 2.5zm0 7.25a2 2 0 110-4 2 2 0 010 4z" />
                      </svg>
                      <span>{{ item.location }}</span>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-1">
                      <span v-for="tag in item.tags" :key="tag"
                        class="inline-block rounded-md border border-sky-100 bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                        {{ tag }}
                      </span>
                    </div>
                  </div>
                </article>
              </Link>
            </div>

            <div class="pointer-events-none absolute inset-y-0 right-0 hidden items-center pr-3 sm:flex">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white shadow-md">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M7.25 4.75L12.5 10l-5.25 5.25" stroke="currentColor" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
              </span>
            </div>
          </div>
        </section>
      </div>
    </section>

    <Teleport to="body">
      <div v-if="cartLoadingVisible"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/35">
        <div
          class="flex items-center gap-3 rounded-xl bg-white/95 px-4 py-3 text-slate-800 shadow-xl ring-1 ring-slate-200">
          <svg class="h-5 w-5 animate-spin text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
            <path class="opacity-75" d="M4 12a8 8 0 0 1 8-8" stroke-linecap="round"></path>
          </svg>
          <span class="text-sm font-semibold">Loading...</span>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <TransitionGroup tag="div"
        class="pointer-events-none fixed bottom-6 left-1/2 z-40 flex w-full max-w-md -translate-x-1/2 flex-col gap-3 px-4 sm:px-0"
        enter-active-class="duration-300 ease-out" enter-from-class="translate-y-3 opacity-0 scale-[0.97]"
        enter-to-class="translate-y-0 opacity-100 scale-100" leave-active-class="duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100 scale-100" leave-to-class="translate-y-3 opacity-0 scale-[0.97]"
        move-class="transition-transform duration-200">
        <div v-for="notification in cartNotifications" :key="notification.id"
          :class="[
            'pointer-events-auto flex w-full items-center gap-3 rounded-2xl px-4 py-3 shadow-2xl ring-1 transition hover:scale-[1.005]',
            notification.type === 'error'
              ? 'border-red-100 bg-white ring-red-100'
              : 'border-slate-100 bg-white ring-slate-900/5'
          ]">
          <template v-if="notification.type === 'success'">
            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-slate-50">
              <img v-if="notification.image" :src="notification.image" :alt="notification.name"
                class="h-full w-full object-cover" />
              <span v-else class="text-xs font-semibold text-slate-400">No Image</span>
            </div>
            <div class="flex-1">
              <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                {{ notification.message }}
              </p>
              <p class="text-sm font-semibold text-slate-900">{{ notification.name }}</p>
              <p class="text-xs text-slate-500">
                Jumlah: {{ notification.quantity }} · {{ formatPrice(notification.price) }}
              </p>
            </div>
            <button v-if="notification.showAction" type="button"
              class="rounded-full border border-sky-200 px-3 py-1.5 text-xs font-semibold text-sky-600 transition hover:bg-sky-50"
              @click="router.visit('/cart')">
              Lihat Keranjang
            </button>
          </template>
          <template v-else>
            <div class="flex items-start gap-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-600">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10 4v6" stroke-linecap="round" />
                  <circle cx="10" cy="14" r="0.5" fill="currentColor" stroke="none" />
                  <circle cx="10" cy="10" r="8" />
                </svg>
              </span>
              <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-red-600">
                  {{ notification.message }}
                </p>
                <p v-if="notification.detail" class="text-xs font-semibold text-red-600/80">
                  {{ notification.detail }}
                </p>
              </div>
              <button type="button"
                class="rounded-full border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-600 transition hover:bg-red-50"
                @click="removeCartNotification(notification.id)">
                Tutup
              </button>
            </div>
          </template>
        </div>
      </TransitionGroup>
    </Teleport>

    <Teleport to="body">
      <div v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/85 px-4 py-6 backdrop-blur-sm"
        @click.self="closeModal">
        <button type="button"
          class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md ring-1 ring-white/40 hover:bg-white"
          @click="closeModal">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7">
            <path d="M5 5l10 10M15 5 5 15" stroke-linecap="round" />
          </svg>
        </button>

        <button type="button"
          class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 text-slate-700 shadow-md ring-1 ring-white/40 hover:bg-white disabled:opacity-40"
          :disabled="(product.gallery?.length ?? 0) <= 1" @click.stop="prevModalImage">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 5 7 10l5 5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <button type="button"
          class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 text-slate-700 shadow-md ring-1 ring-white/40 hover:bg-white disabled:opacity-40"
          :disabled="(product.gallery?.length ?? 0) <= 1" @click.stop="nextModalImage">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m8 5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <div class="w-full max-w-5xl space-y-4">
          <div class="overflow-hidden rounded-lg bg-white ring-1 ring-slate-200">
            <img :src="modalImage" :alt="product.name" class="mx-auto max-h-[70vh] w-full object-contain bg-white"
              @click.stop="nextModalImage" />
          </div>

          <div class="flex items-center justify-center gap-2 overflow-x-auto px-2">
            <button v-for="(thumb, index) in product.gallery" :key="'modal-thumb-' + thumb + index" type="button"
              class="overflow-hidden rounded-md border transition"
              :class="modalIndex === index ? 'border-sky-400 ring-2 ring-sky-200' : 'border-white/40 opacity-80 hover:opacity-100'"
              @click.stop="modalIndex = index">
              <img :src="thumb" :alt="`Galeri ${index + 1}`" class="h-16 w-16 object-cover" />
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </LandingLayout>
</template>
