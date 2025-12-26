<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

  <Head title="Password Berhasil Diubah" />

  <section class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 sm:p-10">

        <!-- Success Icon -->
        <div class="flex justify-center mb-6">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
            <svg class="h-8 w-8 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-slate-900 text-center">Kata Sandi Berhasil Diubah</h1>
        <p class="mt-2 text-sm text-slate-500 text-center">
          Kata sandi Anda telah berhasil diperbarui. Klik tombol di bawah untuk melanjutkan.
        </p>

        <!-- Continue Button -->
        <Link href="/login"
          class="mt-8 w-full flex items-center justify-center gap-2 rounded-lg bg-sky-500 py-3 text-sm font-semibold text-white transition hover:bg-sky-600">
          Lanjutkan
        </Link>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
          <Link href="/login" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-sky-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Kembali ke halaman login
          </Link>
        </div>

      </div>
    </div>
  </section>
</template>
