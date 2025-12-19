<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CustomerCareButton from '@/components/CustomerCareButton.vue';
// import heroIllustration from '@/../images/illustrations/online-shopping.svg';

const logoUrl = '/images/logo-pkk.png';

const options = [
  {
    label: 'Pembeli Retail',
    description: 'Dapatkan diskon dan promo menarik setiap hari',
    href: '/register?role=retail',
    // bg: 'bg-emerald-50 text-emerald-600',
    icon: 'cart',
  },
  {
    label: 'Penjual',
    description: 'Jual produk secara efisien ke BUMN maupun retail',
    href: '/register?role=seller',
    bg: 'bg-sky-50 text-sky-600',
    icon: 'store',
  },
];
</script>

<template>

  <Head title="Daftar Sebagai - PKK UMKM" />

  <section class="relative flex min-h-screen items-center justify-center bg-slate-100 px-4 py-16">
    <div
      class="relative mx-auto flex w-full max-w-5xl min-h-[70vh] flex-col overflow-hidden rounded-lg bg-white shadow-sm md:min-h-[75vh] md:flex-row">
      <div
        class="flex w-full flex-col justify-between border-b border-slate-100 px-8 py-12 md:w-1/2 md:border-b-0 md:border-r">
        <div>
          <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Daftar Sebagai</h1>
            <div class="flex h-14 w-24 items-center justify-center text-xs font-bold text-sky-600">
              <img :src="logoUrl" alt="TP-PKK Marketplace" class="h-full w-full object-contain" decoding="async"
                draggable="false" />
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <Link v-for="option in options" :key="option.label" :href="option.href"
              class="flex items-center gap-4 rounded-lg bg-slate-50 px-5 py-4 transition hover:bg-slate-100">
              <span :class="['flex h-20 w-20 flex-none items-center justify-center', option.bg]">
                <svg v-if="option.icon === 'cart'" width="32" height="33" viewBox="0 0 32 33" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path opacity="0.4"
                    d="M25.6532 7.94H25.1198L20.6132 3.43333C20.2532 3.07333 19.6665 3.07333 19.2932 3.43333C18.9332 3.79333 18.9332 4.38 19.2932 4.75333L22.4798 7.94H9.51984L12.7065 4.75333C13.0665 4.39333 13.0665 3.80666 12.7065 3.43333C12.3465 3.07333 11.7598 3.07333 11.3865 3.43333L6.89317 7.94H6.35984C5.15984 7.94 2.6665 7.94 2.6665 11.3533C2.6665 12.6467 2.93317 13.5 3.49317 14.06C3.81317 14.3933 4.19984 14.5667 4.61317 14.66C4.99984 14.7533 5.41317 14.7667 5.81317 14.7667H26.1865C26.5998 14.7667 26.9865 14.74 27.3598 14.66C28.4798 14.3933 29.3332 13.5933 29.3332 11.3533C29.3332 7.94 26.8398 7.94 25.6532 7.94Z"
                    fill="#0084D1" />
                  <path
                    d="M26.1999 14.7667H5.81328C5.41328 14.7667 4.99995 14.7534 4.61328 14.66L6.29328 24.9C6.66661 27.1934 7.66661 29.8334 12.1066 29.8334H19.5866C24.0799 29.8334 24.8799 27.58 25.3599 25.06L27.3733 14.66C26.9999 14.74 26.5999 14.7667 26.1999 14.7667ZM14.1466 23.38C14.1466 23.9 13.7333 24.3134 13.2133 24.3134C12.6933 24.3134 12.2799 23.9 12.2799 23.38V18.98C12.2799 18.46 12.6933 18.0467 13.2133 18.0467C13.7333 18.0467 14.1466 18.46 14.1466 18.98V23.38ZM19.8533 23.38C19.8533 23.9 19.4399 24.3134 18.9199 24.3134C18.3999 24.3134 17.9866 23.9 17.9866 23.38V18.98C17.9866 18.46 18.3999 18.0467 18.9199 18.0467C19.4399 18.0467 19.8533 18.46 19.8533 18.98V23.38Z"
                    fill="#0084D1" />
                </svg>


                <svg v-else class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M4 10V7l2-3h12l2 3v3" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M5 10h14v8H5z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10 14h4" stroke-linecap="round" />
                </svg>
              </span>
              <div class="space-y-1">
                <p class="text-lg font-extrabold text-slate-900">{{ option.label }}</p>
                <p class="text-md leading-snug text-slate-600">
                  {{ option.description }}
                </p>
              </div>
            </Link>
          </div>
        </div>

        <div class="mt-8 border-t border-slate-300 pt-6 text-center text-xs sm:text-sm text-slate-500">
          Sudah punya akun TP-PKK Marketplace?
          <Link href="/login" class="font-semibold text-sky-600 hover:underline">Masuk</Link>
        </div>
      </div>

      <div class="flex w-full flex-col bg-linear-to-br from-sky-400 via-sky-500 to-sky-600 px-8 py-12 md:w-1/2">
        <!-- Image commented out for now -->
        <!-- <div class="flex flex-1 flex-col items-center justify-center">
          <img :src="heroIllustration" alt="Daftar TP-PKK Marketplace" class="w-56 sm:w-64 lg:w-72" />
        </div> -->

        <div class="mt-6 text-white">
          <h2 class="text-xl sm:text-2xl font-bold">Bersama TP-PKK Marketplace</h2>
          <p class="mt-3 text-md leading-relaxed text-white font-semibold">
            Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
          </p>
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
