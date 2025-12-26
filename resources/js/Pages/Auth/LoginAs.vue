<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const options = [
  {
    label: 'Pembeli',
    description: 'Belanja produk UMKM dengan mudah',
    href: '/customer/login',
    icon: 'cart',
  },
  {
    label: 'Penjual',
    description: 'Akses dashboard dan kelola toko',
    href: '/seller/login',
    icon: 'store',
  },
];
</script>

<template>

  <Head title="Login" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4 lg:p-8">
    <div class="w-full max-w-5xl">
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden lg:flex min-h-[600px]">

        <!-- Left Side - Hero -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-sky-500 to-sky-600 p-10 flex-col justify-center">
          <div class="space-y-4">
            <h2 class="text-2xl font-bold text-white">Bersama TP-PKK Marketplace</h2>
            <p class="text-sky-100 leading-relaxed">
              Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
            </p>
          </div>

          <div class="mt-10 space-y-4">
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Platform terpercaya untuk UMKM</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Transaksi aman & transparan</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <span class="text-sm text-white">Dukungan pelanggan 24/7</span>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="lg:w-1/2 p-8 sm:p-10 flex flex-col justify-center">
          <!-- Back Arrow -->
          <Link href="/" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-sky-600 mb-4 w-fit">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Kembali
          </Link>

          <!-- Title -->
          <h1 class="text-2xl font-bold text-slate-900">Masuk ke Akun</h1>
          <p class="mt-2 text-sm text-slate-500">
            Pilih jenis akun untuk login
          </p>

          <!-- Options -->
          <div class="mt-8 space-y-3">
            <Link v-for="option in options" :key="option.label" :href="option.href"
              class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 transition hover:border-sky-300 hover:bg-sky-50/50">
              <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-sky-50">
                <!-- Cart Icon -->
                <svg v-if="option.icon === 'cart'" class="h-6 w-6 text-sky-500" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="8" cy="21" r="1" />
                  <circle cx="19" cy="21" r="1" />
                  <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
                <!-- Store Icon -->
                <svg v-else class="h-6 w-6 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                  <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="font-semibold text-slate-900">{{ option.label }}</p>
                <p class="text-sm text-slate-500">{{ option.description }}</p>
              </div>
              <!-- Arrow -->
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 18l6-6-6-6" />
              </svg>
            </Link>
          </div>

          <!-- Register Link -->
          <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-sm text-slate-500">
              Belum punya akun?
              <Link href="/register-as" class="font-medium text-sky-600 hover:underline">Daftar Sekarang</Link>
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>
