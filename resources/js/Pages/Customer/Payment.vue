<script setup>
import LandingLayout from '@/Layouts/LandingLayout.vue';
import CustomerSidebarMenu from '@/components/Customer/SidebarMenu.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({
  layout: LandingLayout,
});
</script>

<template>
  <div class="bg-slate-50">

    <Head title="Pembayaran" />

    <div class="mx-auto flex max-w-screen-2xl flex-col gap-6 px-6 py-10">
      <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
        <a href="/" class="text-sky-600 hover:underline">Beranda</a>
        <span>/</span>
        <span class="text-slate-900">Pembayaran</span>
      </nav>

      <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
        <CustomerSidebarMenu active-key="pembayaran" />

        <main class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div>
                <h2 class="text-xl font-semibold text-slate-900">Metode Pembayaran</h2>
                <p class="text-sm text-slate-500">Kelola metode pembayaran yang tersimpan.</p>
              </div>
              <button
                class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600">
                Tambah Metode
              </button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
              <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">Virtual Account / Transfer</p>
                <p class="mt-1 text-sm text-slate-600">Tambah rekening untuk mempermudah pembayaran.</p>
                <button
                  class="mt-3 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                  Tambah Rekening
                </button>
              </div>
              <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">E-Wallet / QRIS</p>
                <p class="mt-1 text-sm text-slate-600">Hubungkan dompet digital favorit Anda.</p>
                <button
                  class="mt-3 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                  Tambah E-Wallet
                </button>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
</template>
