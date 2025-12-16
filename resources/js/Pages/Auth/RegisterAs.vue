<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CustomerCareButton from '@/components/CustomerCareButton.vue';
import heroIllustration from '@/../images/illustrations/online-shopping.svg';

const options = [
  {
    label: 'Pembeli Retail',
    description: 'Dapatkan diskon dan promo menarik setiap hari',
    href: '/register?role=retail',
    bg: 'bg-emerald-50 text-emerald-600',
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
            <div
              class="flex h-10 min-w-28 items-center justify-center rounded-xl border border-slate-200 px-3 text-[11px] font-bold text-sky-600">
              TP-PKK Marketplace
            </div>
          </div>

          <div class="mt-8 space-y-4">
            <Link v-for="option in options" :key="option.label" :href="option.href"
              class="flex items-center gap-4 rounded-lg bg-slate-50 px-5 py-4 transition hover:bg-slate-100">
              <span :class="['flex h-14 w-14 flex-none items-center justify-center rounded-lg', option.bg]">
                <svg v-if="option.icon === 'cart'" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.7">
                  <path d="M3 8h18l-1.5 10H4.5L3 8Z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M9 11.5v2.5M15 11.5v2.5" stroke-linecap="round" />
                </svg>
                <svg v-else class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M4 10V7l2-3h12l2 3v3" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M5 10h14v8H5z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10 14h4" stroke-linecap="round" />
                </svg>
              </span>
              <div class="space-y-1">
                <p class="text-base font-semibold text-slate-900">{{ option.label }}</p>
                <p class="text-sm leading-snug text-slate-600">
                  {{ option.description }}
                </p>
              </div>
            </Link>
          </div>
        </div>

        <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs sm:text-sm text-slate-500">
          Sudah punya akun TP-PKK Marketplace?
          <Link href="/login" class="font-semibold text-sky-600 hover:underline">Masuk</Link>
        </div>
      </div>

      <div class="flex w-full flex-col bg-linear-to-br from-sky-400 via-sky-500 to-sky-600 px-8 py-12 md:w-1/2">
        <div class="flex flex-1 flex-col items-center justify-center">
          <img :src="heroIllustration" alt="Daftar TP-PKK Marketplace" class="w-56 sm:w-64 lg:w-72" />
        </div>

        <div class="mt-6 text-center text-white">
          <h2 class="text-xl sm:text-2xl font-bold">Bersama TP-PKK Marketplace</h2>
          <p class="mt-3 text-sm leading-relaxed text-white/90">
            Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
          </p>
        </div>
      </div>
    </div>

    <CustomerCareButton />
  </section>
</template>
