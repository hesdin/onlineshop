<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import LandingLayout from '@/Layouts/LandingLayout.vue';

const props = defineProps({
  appName: {
    type: String,
    default: 'TP-PKK Marketplace',
  },
  categories: {
    type: Array,
    default: () => [],
  },
  collections: {
    type: Array,
    default: () => [],
  },
});

const heroBanners = [
  {
    image: '/assets/img/hero/hero-1.png',
    url: '#internet-bisnis',
    label: 'Promo internet bisnis',
  },
  {
    image: '/assets/img/hero/hero-2.png',
    url: '#hibah-digital',
    label: 'Hibah digitalisasi UMKM',
  },
  {
    image: '/assets/img/hero/hero-3.jpg',
    url: '#paket-hampers',
    label: 'Paket hampers akhir tahun',
  },
];

const heroPromos = [
  {
    image: '/assets/img/hero/hero-right-1.jpg',
    alt: 'Gratis ongkir Jakarta dan diskon Sinar Bersih',
    url: '#promo-rumah-tangga',
  },
  {
    image: '/assets/img/hero/hero-right-2.png',
    alt: 'Proses kilat dapur solo lunch box',
    url: '#promo-kuliner',
  },
];

const categoryPalette = [
  'bg-amber-50 text-amber-700',
  'bg-rose-50 text-rose-700',
  'bg-emerald-50 text-emerald-700',
  'bg-indigo-50 text-indigo-700',
  'bg-sky-50 text-sky-700',
  'bg-purple-50 text-purple-700',
  'bg-cyan-50 text-cyan-700',
  'bg-pink-50 text-pink-700',
  'bg-teal-50 text-teal-700',
  'bg-lime-50 text-lime-700',
  'bg-slate-50 text-slate-700',
  'bg-orange-50 text-orange-700',
];

const categoryTiles = computed(() => {
  const baseCategories = (props.categories ?? [])
    .slice(0, 17)
    .map((category, index) => {
      const name = category?.name ?? '';
      return {
        ...category,
        color: categoryPalette[index % categoryPalette.length],
        imageUrl: category?.image_url ?? null,
        initial: name.trim().charAt(0).toUpperCase() || '?',
      };
    });

  return [
    ...baseCategories,
    {
      id: 'see-all',
      name: 'Lihat Semua',
      slug: 'lihat-semua',
      color: categoryPalette[baseCategories.length % categoryPalette.length],
      imageUrl:
        'data:image/png;base64,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',
      initial: '>>',
    },
  ];
});

const fallbackCollections = [
  { title: 'SUPERDEAL Road to PKK Sulsel Mart Forum', color: 'from-sky-600 to-sky-500' },
  { title: 'Koleksi PKK Sulsel Mart Jasa Percetakan', color: 'from-amber-500 to-orange-400' },
  { title: 'Koleksi Andalan Perjalanan Bisnis', color: 'from-emerald-500 to-teal-400' },
  { title: 'Koleksi Perawatan Teknologi', color: 'from-indigo-500 to-blue-500' },
  { title: 'Koleksi Produktif di Kantor', color: 'from-purple-500 to-fuchsia-500' },
  { title: 'Koleksi Hampers Premium', color: 'from-pink-500 to-rose-400' },
];

const fallbackProducts = [
  {
    title: 'Stop Kontak Industri 4 Lubang',
    vendor: 'CENTRIN AFATEC SUPPLIES',
    price: 'Rp680.000',
    badge: 'UMKM',
    location: 'Jakarta Pusat',
    sold: 'Terjual 8',
    tags: ['PKP'],
    image: 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=640&q=80',
  },
  {
    title: 'Produksi Kalender 2025 PT Jasamarga',
    vendor: 'TIARA IMPRESA GRAFIKA',
    price: 'Rp190.612.250',
    badge: 'UMKM',
    location: 'Kota Depok',
    sold: 'Terjual 3',
    tags: ['PDN', 'PKP'],
    image: 'https://images.unsplash.com/photo-1473186578172-c141e6798cf4?auto=format&fit=crop&w=640&q=80',
  },
  {
    title: 'Dokumentasi Temu Pelanggan',
    vendor: 'TIARA IMPRESA GRAFIKA',
    price: 'Rp52.087.860',
    badge: 'UMKM',
    location: 'Kota Depok',
    sold: 'Terjual 1',
    tags: ['PDN', 'PKP'],
    image: 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=640&q=80',
  },
  {
    title: 'BROTHER Printer MFC T920DW',
    vendor: 'CENTRIN AFATEC SUPPLIES',
    price: 'Rp4.900.000',
    badge: 'UMKM',
    location: 'Jakarta Pusat',
    sold: 'Terjual 12',
    tags: ['PKP'],
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=640&q=80',
  },
  {
    title: 'UPS Kantor 1200VA',
    vendor: 'POWERSAFE INDONESIA',
    price: 'Rp1.850.000',
    badge: 'UMKM',
    location: 'Kota Bandung',
    sold: 'Terjual 6',
    tags: ['PKP'],
    image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=640&q=80',
  },
  {
    title: 'Headset Wireless Meeting',
    vendor: 'AUDIOPRO PARTNER',
    price: 'Rp1.250.000',
    badge: 'UMKM',
    location: 'Jakarta Selatan',
    sold: 'Terjual 9',
    tags: ['PDN', 'PKP'],
    image: 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=640&q=80',
  },
];

const collectionList = computed(() => {
  if (props.collections?.length) {
    return props.collections.map((item) => ({
      ...item,
      products: item.products ?? [],
    }));
  }
  return fallbackCollections.map((item) => ({
    ...item,
    products: fallbackProducts,
    banner:
      'https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/product-collection/image_section_banner/24112025/superdeal-road-to-padi-business-forum-and-showcase/ed27e7533e0ae63bb045d2520334d1.jpg',
    url: '/template/collection/superdeal-road-to-pa-di-business-forum-and-showcase',
  }));
});

const formatPrice = (value) => {
  if (value == null) return 'Rp0';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(value);
};

const benefits = [
  {
    title: 'Kemudahan Pembayaran',
    desc: 'Mendukung berbagai metode pembayaran B2B yang aman dan transparan.',
  },
  {
    title: 'Kepastian Pengiriman',
    desc: 'Didukung logistik nasional untuk menjangkau seluruh Indonesia.',
  },
  {
    title: 'Pasar yang Pasti',
    desc: 'Akses ke BUMN & perusahaan besar untuk memperluas bisnis.',
  },
  {
    title: 'Sistem Penilaian Produk',
    desc: 'Penilaian vendor dan produk guna menjaga kualitas pengadaan.',
  },
];

const faqs = [
  'Pengadaan barang dan jasa pemerintah di marketplace TP-PKK Marketplace',
  'Belanja barang dan jasa dari UMKM jadi lebih mudah',
  'Cara daftar / registrasi di TP-PKK Marketplace (Buyer/Seller)',
  'Bagaimana proses verifikasi vendor?',
];

const heroActive = ref(0);
let heroTimer = null;

const startHero = () => {
  if (typeof window === 'undefined') return;
  stopHero();
  heroTimer = window.setInterval(() => {
    heroActive.value = (heroActive.value + 1) % heroBanners.length;
  }, 4500);
};

const stopHero = () => {
  if (heroTimer) {
    clearInterval(heroTimer);
    heroTimer = null;
  }
};

const prevHero = () => {
  heroActive.value = (heroActive.value + heroBanners.length - 1) % heroBanners.length;
};

const nextHero = () => {
  heroActive.value = (heroActive.value + 1) % heroBanners.length;
};

const setHeroSlide = (index) => {
  heroActive.value = index;
};

const restartHero = () => {
  stopHero();
  startHero();
};

onMounted(() => {
  startHero();
});

onBeforeUnmount(() => {
  stopHero();
});

const collectionScrollers = new Map();

const setCollectionScroller = (index, el) => {
  if (el) {
    collectionScrollers.set(index, el);
  } else {
    collectionScrollers.delete(index);
  }
};

const scrollCollection = (index, direction) => {
  const scroller = collectionScrollers.get(index);
  if (!scroller) return;
  const card = scroller.querySelector('.product-card');
  const styles = window.getComputedStyle(scroller);
  const gap = parseFloat(styles.columnGap || styles.gap || '16');
  const width = card ? card.getBoundingClientRect().width + gap : scroller.clientWidth * 0.7;
  scroller.scrollTo({
    left: scroller.scrollLeft + direction * width,
    behavior: 'smooth',
  });
};
</script>

<template>
  <LandingLayout>

    <Head :title="`${props.appName} - Marketplace B2B`" />

    <section>
      <div class="grid gap-4 lg:grid-cols-3 lg:items-stretch">
        <article class="lg:col-span-2" @mouseenter="stopHero" @mouseleave="startHero">
          <div class="relative h-full overflow-hidden rounded-lg bg-slate-50" style="aspect-ratio: 1440 / 620;">
            <template v-for="(banner, index) in heroBanners" :key="banner.label">
              <a v-show="heroActive === index" class="block h-full w-full" :href="banner.url" target="_blank"
                rel="noopener">
                <img :src="banner.image" :alt="banner.label"
                  class="h-full w-full object-cover transition duration-500" />
              </a>
            </template>

            <div class="pointer-events-none absolute inset-0 flex items-center justify-between px-4">
              <button class="pointer-events-auto rounded-full bg-white/80 p-2 text-slate-700 shadow"
                @click="prevHero(); restartHero();">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="m15 18-6-6 6-6" />
                </svg>
              </button>
              <button class="pointer-events-auto rounded-full bg-white/80 p-2 text-slate-700 shadow"
                @click="nextHero(); restartHero();">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="m9 6 6 6-6 6" />
                </svg>
              </button>
            </div>

            <div class="absolute bottom-4 left-4 flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 shadow">
              <span class="h-[6px] w-10 rounded-full bg-slate-200"></span>
              <button v-for="(banner, index) in heroBanners" :key="`dot-${banner.label}`"
                class="h-2 w-2 rounded-full bg-slate-200 transition hover:bg-sky-500"
                :class="heroActive === index ? 'scale-100 bg-sky-500' : 'opacity-80'"
                @click="setHeroSlide(index); restartHero();" :aria-label="`Slide ${index + 1}`"></button>
            </div>
          </div>
        </article>

        <article class="flex h-full flex-col gap-4">
          <a v-for="promo in heroPromos" :key="promo.alt" :href="promo.url"
            class="group flex-1 overflow-hidden rounded-lg ring-1 ring-slate-100">
            <img :src="promo.image" :alt="promo.alt"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" />
          </a>
        </article>
      </div>

      <div class="mt-2 flex items-center justify-end text-md font-bold text-sky-600">
        <a class="inline-flex items-center gap-2 hover:text-sky-700" href="#promo-terkini">
          Lihat semua promo
        </a>
      </div>
    </section>

    <section class="space-y-4">
      <header class="flex items-center justify-between">
        <h3 class="text-2xl font-bold text-slate-900">Kategori</h3>
      </header>
      <div
        class="grid gap-3 rounded-lg bg-white p-6 shadow-sm grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9">
        <a v-for="category in categoryTiles" :key="category.id ?? category.slug ?? category.name"
          class="flex flex-col items-center rounded-lg border border-slate-100 p-4 text-center transition hover:-translate-y-0.5 hover:border-sky-100 hover:shadow-sm"
          :href="category.slug === 'lihat-semua' ? '/c' : category.slug ? `/c/${category.slug}` : '/c'">
          <div
            class="mb-3 inline-flex h-12 w-12 items-center justify-center overflow-hidden rounded-md text-base font-semibold"
            :class="category.color">
            <img v-if="category.imageUrl" :src="category.imageUrl" :alt="category.name"
              class="h-full w-full object-cover" loading="lazy" />
            <span v-else>{{ category.initial }}</span>
          </div>
          <p class="text-xs font-semibold text-slate-900">{{ category.name }}</p>
        </a>
      </div>
    </section>

    <section v-for="(collection, index) in collectionList" :key="collection.title">
      <div class="overflow-hidden rounded-lg bg-linear-to-r p-6 text-white shadow-xl ring-1 ring-black/5"
        :class="collection.color">
        <header class="flex flex-wrap items-center gap-3">
          <h3 class="text-2xl font-bold">{{ collection.title }}</h3>
          <a :href="collection.url || '/search'"
            class="ml-auto inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-sky-700 shadow transition hover:-translate-y-0.5 hover:shadow-md">
            Lihat Semua
          </a>
        </header>

        <div class="mt-5 flex flex-col gap-4 lg:flex-row lg:items-start">
          <div class="max-w-sm overflow-hidden rounded-lg ring-1 ring-white/20 backdrop-blur-sm lg:min-h-full">
            <img :src="collection.banner" :alt="`Koleksi pilihan ${collection.title}`"
              class="h-full w-full object-cover" loading="lazy" />
          </div>

          <div class="relative flex-1 overflow-hidden">
            <div
              class="grid min-w-full grid-flow-col auto-cols-[75%] gap-4 overflow-x-auto pb-3 pl-1 pr-6 sm:auto-cols-[60%] md:auto-cols-[50%] lg:auto-cols-[var(--card-width-lg)]"
              style="--card-width-lg: calc((100% - 48px) / 4); scroll-padding-inline: 1.5rem;"
              :ref="(el) => setCollectionScroller(index, el)">
              <article v-for="product in collection.products || []" :key="product.id ?? product.title"
                class="product-card flex h-full flex-col overflow-hidden rounded-lg border border-white/40 bg-white p-4 text-slate-900 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="-mx-4 -mt-4">
                  <div class="relative h-40 overflow-hidden bg-slate-100">
                    <div
                      class="absolute left-2 top-2 rounded-md bg-sky-500 px-2 py-1 text-[11px] font-semibold uppercase text-white shadow">
                      {{ product.badge }}
                    </div>
                    <img :src="product.image" :alt="product.title" loading="lazy" class="h-full w-full object-cover" />
                  </div>
                </div>
                <div class="mt-3 flex flex-1 flex-col gap-1.5">
                  <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-600">
                    <span
                      class="inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50/60 px-1.5 py-0.5 text-[11px] text-sky-700">
                      <span class="inline-flex h-4 w-4 items-center justify-center">
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path opacity="0.4"
                            d="M10.9349 6.19499V9.18999C10.9349 10.57 9.81494 11.69 8.43494 11.69H4.06494C2.68494 11.69 1.56494 10.57 1.56494 9.18999V6.22999C1.94494 6.63999 2.48494 6.87499 3.06994 6.87499C3.69994 6.87499 4.30494 6.55999 4.68494 6.05499C5.02494 6.55999 5.60494 6.87499 6.24994 6.87499C6.88994 6.87499 7.45994 6.57499 7.80494 6.07499C8.18994 6.56999 8.78494 6.87499 9.40494 6.87499C10.0099 6.87499 10.5599 6.62999 10.9349 6.19499Z"
                            fill="#0092AC" />
                          <path
                            d="M7.74517 1.125H4.74517L4.37517 4.805C4.34517 5.145 4.39517 5.465 4.52017 5.755C4.81017 6.435 5.49017 6.875 6.25017 6.875C7.02017 6.875 7.68517 6.445 7.98517 5.76C8.07517 5.545 8.13017 5.295 8.13517 5.04V4.945L7.74517 1.125Z"
                            fill="#0092AC" />
                          <path opacity="0.6"
                            d="M11.4299 4.635L11.2849 3.25C11.0749 1.74 10.3899 1.125 8.92488 1.125H7.00488L7.37488 4.875C7.37988 4.925 7.38488 4.98 7.38488 5.075C7.41488 5.335 7.49488 5.575 7.61488 5.79C7.97488 6.45 8.67488 6.875 9.40488 6.875C10.0699 6.875 10.6699 6.58 11.0449 6.06C11.3449 5.66 11.4799 5.155 11.4299 4.635Z"
                            fill="#0092AC" />
                          <path opacity="0.6"
                            d="M3.54483 1.125C2.07483 1.125 1.39482 1.74 1.17982 3.265L1.04482 4.64C0.994825 5.175 1.13983 5.695 1.45483 6.1C1.83483 6.595 2.41982 6.875 3.06982 6.875C3.79982 6.875 4.49983 6.45 4.85483 5.8C4.98483 5.575 5.06982 5.315 5.09482 5.045L5.48483 1.13H3.54483V1.125Z"
                            fill="#0092AC" />
                          <path
                            d="M5.92506 8.83C5.29006 8.895 4.81006 9.435 4.81006 10.075V11.69H7.68506V10.25C7.69006 9.205 7.07506 8.71 5.92506 8.83Z"
                            fill="#0092AC" />
                        </svg>
                      </span>
                      <span class="max-w-[120px] truncate text-[8px]">
                        {{ product.vendor }}
                      </span>
                    </span>
                  </div>
                  <h5 class="text-[13px] leading-snug text-slate-900 line-clamp-2">
                    {{ product.title }}
                  </h5>
                  <p class="text-sm font-bold text-slate-900">
                    {{ typeof product.price === 'number' ? formatPrice(product.price) : product.price }}
                  </p>
                  <div class="flex items-center gap-1 text-[11px] text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-sky-600" viewBox="0 0 20 20"
                      fill="currentColor">
                      <path
                        d="M10 2a6 6 0 0 0-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 0 0-6-6Zm0 8.25a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Z" />
                    </svg>
                    <span>{{ product.location }}</span>
                  </div>
                  <div class="text-[11px] text-amber-600">
                    {{ product.sold }}
                  </div>
                  <div class="mt-auto flex flex-wrap gap-1.5">
                    <span v-for="tag in product.tags" :key="`${product.title}-${tag}`"
                      class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">
                      {{ tag }}
                    </span>
                  </div>
                </div>
              </article>
            </div>

            <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-1">
              <button type="button" @click="scrollCollection(index, -1)"
                class="pointer-events-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow transition hover:scale-105 hover:shadow-md">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="m15 18-6-6 6-6" />
                </svg>
              </button>
              <button type="button" @click="scrollCollection(index, 1)"
                class="pointer-events-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow transition hover:scale-105 hover:shadow-md">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="m9 6 6 6-6 6" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="space-y-6 rounded-lg bg-white p-8 shadow-sm">
      <header class="flex flex-wrap items-center justify-center gap-4 text-center">
        <h3 class="text-2xl font-bold text-slate-900">Keuntungan bergabung</h3>
      </header>
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <article v-for="benefit in benefits" :key="benefit.title"
          class="flex flex-col items-center rounded-xl border border-slate-100 p-5 text-center">
          <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
            ☆
          </div>
          <h4 class="text-lg font-semibold text-slate-900">{{ benefit.title }}</h4>
          <p class="mt-2 text-sm text-slate-500">{{ benefit.desc }}</p>
        </article>
      </div>
    </section>

    <section class="space-y-4">
      <header>
        <h3 class="text-2xl font-bold text-slate-900">Pertanyaan umum</h3>
      </header>
      <div class="space-y-3">
        <details v-for="faq in faqs" :key="faq"
          class="group rounded-lg border border-slate-200 bg-white text-slate-900 shadow-sm [&_summary::-webkit-details-marker]:hidden">
          <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-4 text-lg font-bold">
            <span>{{ faq }}</span>
            <svg class="h-5 w-5 text-slate-700 transition-transform duration-200 group-open:rotate-180"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </summary>
          <div class="border-t border-slate-100 px-6 py-4">
            <p class="text-sm text-slate-500">
              Tim TP-PKK Marketplace siap membantu proses registrasi, verifikasi, hingga pengelolaan katalog produk
              Anda.
            </p>
          </div>
        </details>
      </div>
    </section>
  </LandingLayout>
</template>
