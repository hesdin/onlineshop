<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CustomerCareButton from '@/components/CustomerCareButton.vue';

const slides = [
  {
    title: 'Bersama TP-PKK Marketplace',
    description: 'Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.',
    image: '/images/illustrations/storefront.svg',
  },
  {
    title: 'UMKM Berkembang Pesat',
    description: 'Akses pengadaan barang dan jasa dengan lebih mudah, aman, dan transparan.',
    image: '/images/illustrations/shopping-bags.svg',
  },
  {
    title: 'Digitalisasi Pengadaan',
    description: 'Satu platform untuk menghubungkan UMKM dengan BUMN dan pembeli korporat.',
    image: '/images/illustrations/delivering.svg',
  },
];

const current = ref(0);

const showSlide = (index: number) => {
  current.value = index;
};

const nextSlide = () => {
  current.value = (current.value + 1) % slides.length;
};

const prevSlide = () => {
  current.value = (current.value - 1 + slides.length) % slides.length;
};

const dots = computed(() => slides.map((_, index) => index));

let timer: ReturnType<typeof setInterval> | null = null;
const start = () => {
  stop();
  timer = setInterval(() => {
    nextSlide();
  }, 4500);
};
const stop = () => {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
};

onMounted(start);
onUnmounted(stop);
</script>

<template>

  <Head title="Pilih Login - PKK UMKM" />

  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-sky-600 text-white">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-linear-to-b from-sky-500/40 via-sky-500/10 to-sky-700/50"></div>
      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-4xl border border-white/10"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-4xl border border-white/10"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-500/30 blur-3xl"></span>
    </div>

    <div
      class="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-10 px-6 py-12 lg:flex-row lg:items-stretch lg:gap-16 lg:py-16">
      <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
        <div class="max-w-md">
          <div v-for="(slide, index) in slides" :key="index" :class="index === current ? '' : 'hidden'"
            class="space-y-6">
            <!-- Image commented out for now -->
            <!-- <img :src="slide.image" alt="Login TP-PKK Marketplace"
              class="mx-auto w-[230px] sm:w-[290px] lg:w-[340px]" /> -->
            <div>
              <h2 class="text-2xl sm:text-3xl font-bold">
                {{ slide.title }}
              </h2>
              <p class="mt-3 text-sm sm:text-base text-white/90">
                {{ slide.description }}
              </p>
            </div>
          </div>

          <div class="mt-6 flex items-center justify-center gap-2 lg:justify-start">
            <button v-for="dot in dots" :key="dot" @click="showSlide(dot)"
              :class="dot === current ? 'h-2 w-6 rounded-full bg-white' : 'h-2 w-2 rounded-full bg-white/50'"
              class="transition-all"></button>
          </div>
        </div>
      </div>

      <!-- Arrow buttons commented out for now -->
      <!-- <button type="button" @click="prevSlide"
        class="pointer-events-auto absolute left-8 top-1/2 hidden h-10 w-10 -translate-y-1/2 transform items-center justify-center rounded-full bg-white text-sky-500 shadow-lg hover:bg-sky-50 lg:inline-flex">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 7l-5 5 5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
      <button type="button" @click="nextSlide"
        class="pointer-events-auto absolute right-8 top-1/2 hidden h-10 w-10 -translate-y-1/2 transform items-center justify-center rounded-full bg-white text-sky-500 shadow-lg hover:bg-sky-50 lg:inline-flex">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button> -->

      <div class="relative flex-1 flex items-center justify-center">
        <div
          class="relative z-10 w-full max-w-xl rounded-md bg-white p-8 sm:p-10 text-slate-900 shadow-lg min-h-[520px]">
          <div class="mb-8 flex items-center justify-between gap-6">
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-900">
              Login Sebagai
            </h3>
            <div
              class="flex h-10 min-w-28 items-center justify-center rounded-md border border-slate-200 text-[11px] font-bold text-sky-600 p-3">
              TP-PKK Marketplace
            </div>
          </div>

          <div class="space-y-4">
            <Link href="/seller/login"
              class="flex min-h-[100px] items-center justify-between rounded-2xl border border-slate-300 bg-slate-50/80 p-5 transition hover:border-sky-300 hover:bg-white hover:shadow-sm">
              <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M3 7h18M5 7v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7M9 7V5a3 3 0 0 1 6 0v2"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
                <div>
                  <p class="text-lg font-bold text-slate-900">Penjual</p>
                  <p class="text-sm font-semibold text-slate-600">
                    Jual produk secara efisien ke BUMN maupun retail
                  </p>
                </div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </Link>

            <Link href="/customer/login"
              class="flex min-h-[100px] items-center justify-between rounded-2xl border border-slate-300 bg-slate-50/80 p-5 transition hover:border-sky-300 hover:bg-white hover:shadow-sm">
              <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M3 5h2l1.68 8.39A2 2 0 0 0 8.64 15h8.05a2 2 0 0 0 1.92-1.51L20 7H6" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <circle cx="9" cy="19" r="1.5" />
                    <circle cx="17" cy="19" r="1.5" />
                  </svg>
                </span>
                <div>
                  <p class="text-lg font-bold text-slate-900">Pembeli</p>
                  <p class="text-sm font-semibold text-slate-600">
                    Login pembeli retail dan B2B
                  </p>
                </div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
