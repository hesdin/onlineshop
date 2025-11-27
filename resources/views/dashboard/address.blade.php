@php($activeMenu = 'alamat')
@extends('layouts.dashboard')

@section('title', 'Dashboard - Alamat Pengiriman')

@section('dashboard-breadcrumb')
  <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
    <a href="/" class="text-sky-600 hover:underline">Beranda</a>
    <span>/</span>
    <span class="text-slate-900">Alamat Pengiriman</span>
  </nav>
@endsection

@section('dashboard-content')
  <section x-data="{ showModal: false }" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h2 class="text-xl font-semibold text-slate-900">Alamat Pengiriman</h2>
      <button @click="showModal = true"
        class="rounded-lg border border-sky-500 px-5 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
        Tambah Alamat
      </button>
    </div>

    <div class="rounded-lg border border-sky-400/80 bg-sky-50/60 p-5">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="space-y-1 text-sm text-slate-700">
          <p class="font-semibold text-slate-900">Nama Alamat</p>
          <p class="text-base font-semibold text-slate-900">Kos Hesdin</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex items-center rounded-full bg-sky-500 px-3 py-1 text-xs font-semibold text-white">
            Terpilih
          </span>
          <a href="#" class="text-sm font-semibold text-sky-600 hover:underline">Ubah Alamat</a>
        </div>
      </div>

      <div class="mt-4 grid gap-2 text-sm text-slate-700 sm:grid-cols-[140px_1fr] sm:items-start">
        <p class="font-semibold text-slate-900">Alamat</p>
        <div class="space-y-1">
          <p class="font-semibold text-slate-900">Hesdin</p>
          <p class="font-semibold text-slate-900">Hartaco Jaya</p>
          <p>Panakkukang, Kota Makassar, Sulawesi Selatan, 90231</p>
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-3 text-sm font-semibold text-slate-600">
      <button type="button"
        class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 hover:bg-slate-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="m15 18-6-6 6-6" />
        </svg>
      </button>
      <span class="grid h-8 min-w-[32px] place-items-center rounded-lg bg-sky-500 px-2 text-white">1</span>
      <button type="button"
        class="grid h-8 w-8 place-items-center rounded-full border border-slate-200 hover:bg-slate-50">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="m9 6 6 6-6 6" />
        </svg>
      </button>
    </div>

    {{-- Modal Tambah Alamat --}}
    <div x-cloak x-show="showModal" x-transition.opacity.duration.150ms class="fixed inset-0 z-50">
      <div @click="showModal = false" class="absolute inset-0 bg-black/40"></div>
      <div class="relative z-10 flex min-h-full items-start justify-center px-4 py-10">
        <div x-show="showModal" x-transition.scale.origin.top.duration.150ms
          class="w-full max-w-4xl rounded-lg bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-xl font-semibold text-slate-900">Tambah Alamat</h3>
            <button @click="showModal = false"
              class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 6 12 12M18 6 6 18" />
              </svg>
            </button>
          </div>

          <div class="space-y-4 px-6 py-5">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-800">Label Alamat</label>
                <input type="text" placeholder="Rumah, Apartemen, Kantor"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-sky-400 focus:bg-white focus:ring-2 focus:ring-sky-100" />
              </div>
              <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-800">Nama Penerima</label>
                <input type="text" placeholder="Nama Penerima"
                  class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
              </div>
              <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-800">No Telepon</label>
                <div
                  class="flex rounded-lg border border-slate-200 focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
                  <span class="flex items-center px-3 text-sm text-slate-500">+62</span>
                  <input type="text" placeholder="8123456789"
                    class="w-full rounded-r-lg px-3 py-2.5 text-sm text-slate-800 outline-none" />
                </div>
              </div>
              <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-800">Kecamatan, Kota &amp; Provinsi</label>
                <div
                  class="flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
                  <input type="text" placeholder="Kecamatan, Kota & Provinsi"
                    class="w-full bg-transparent outline-none" />
                  <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </div>
              </div>
            </div>

            <div class="space-y-1">
              <label class="text-sm font-semibold text-slate-800">Pilih Alamat</label>
              <div
                class="flex items-center gap-3 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-600 focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
                <input type="text" placeholder="Cari atau pilih alamat" class="w-full bg-transparent outline-none" />
                <button type="button" class="inline-flex items-center gap-2 text-slate-500 hover:text-sky-600">
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20s8-4 8-10a8 8 0 1 0-16 0c0 6 8 10 8 10Z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                  Tentukan koordinat
                </button>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <div class="space-y-1 sm:col-span-1">
                <label class="text-sm font-semibold text-slate-800">Kode Pos</label>
                <div
                  class="flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
                  <input type="text" placeholder="Pilih kode pos" class="w-full bg-transparent outline-none" />
                  <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </div>
              </div>
              <div class="space-y-1 sm:col-span-2">
                <label class="text-sm font-semibold text-slate-800">Alamat Lengkap</label>
                <textarea placeholder="RT 02 RW 01"
                  class="min-h-[90px] w-full rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-100"></textarea>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-6 py-4">
            <button @click="showModal = false"
              class="rounded-lg border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
              Cancel
            </button>
            <button class="rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-white hover:bg-sky-600">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
