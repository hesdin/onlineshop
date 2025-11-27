@php($activeMenu = 'profil')
@extends('layouts.dashboard')

@section('title', 'Dashboard - Profil')

@section('dashboard-breadcrumb')
  <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
    <a href="/" class="text-sky-600 hover:underline">Beranda</a>
    <span>/</span>
    <span>Ubah Profil</span>
    <span>/</span>
    <span class="text-slate-900">Profil</span>
  </nav>
@endsection

@section('dashboard-content')
  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <header class="mb-6">
      <h2 class="text-xl font-semibold text-slate-900">Informasi Umum</h2>
    </header>

    <div
      class="flex flex-col items-center gap-6 rounded-lg border border-dashed border-slate-200 bg-slate-50/70 p-6 sm:flex-row sm:items-start sm:gap-10">
      <div class="flex flex-col items-center gap-3">
        <div class="grid h-28 w-28 place-items-center rounded-full border-2 border-sky-100 bg-white">
          <div class="grid h-24 w-24 place-items-center rounded-full bg-slate-100">
            <svg class="h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="1.8">
              <circle cx="12" cy="8" r="4" />
              <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
            </svg>
          </div>
        </div>
        <label
          class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-sky-500 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
          <input type="file" class="hidden" />
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m7 10 5-5 5 5" />
            <path d="M12 5v14" />
          </svg>
          Pilih Foto
        </label>
        <p class="text-center text-[11px] text-slate-500">Format gambar .jpg .jpeg .png dan ukuran file
          maksimum 300KB</p>
      </div>

      <div class="grid flex-1 gap-4">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Nama</label>
          <input type="text" value="Hesdin Mukhsin"
            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Kode Referal</label>
          <input type="text" placeholder="Masukkan Kode Referal"
            class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-sky-400 focus:ring-2 focus:ring-sky-100" />
        </div>
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Jabatan</label>
          <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
            -
          </div>
        </div>
      </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
      <button
        class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Batal
      </button>
      <button
        class="rounded-lg bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600">
        Simpan
      </button>
    </div>
  </section>

  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-slate-900">Daftar Perangkat Anda</h3>
    <p class="mt-2 text-sm text-slate-600">
      Daftar ini menampilkan berbagai perangkat yang sedang menggunakan akun Anda.
      Apabila ada aktivitas yang tidak dikenal, segera tekan Logout Semua Perangkat dan atur ulang kata sandi
      akun Anda.
    </p>
    <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800">
      Perangkat Sedang Dipakai
    </div>
  </section>

  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-slate-900">Email dan Telepon</h3>
    <div class="mt-4 space-y-4 text-sm">
      <div class="flex flex-wrap items-center gap-2">
        <span class="min-w-[80px] text-slate-500">Email</span>
        <span class="font-semibold text-slate-900">hesdin01@gmail.com</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <span class="min-w-[80px] text-slate-500">Telepon</span>
        <span class="font-semibold text-slate-900">+6285398737159</span>
        <span
          class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-600">
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m5 13 4 4L19 7" />
          </svg>
          Terverifikasi
        </span>
        <a href="#" class="text-sm font-semibold text-sky-600 hover:underline">Ubah</a>
      </div>
    </div>
  </section>

  <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-slate-900">Kata Sandi</h3>
      <div class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
        Terakhir diubah 01 Maret 2022
      </div>
    </div>

    <div class="mt-4 grid gap-4">
      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-600">Kata Sandi Lama</label>
        <div
          class="flex items-center rounded-lg border border-slate-200 bg-white pr-3 focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
          <input type="password" placeholder="Kata sandi lama"
            class="w-full rounded-lg border-none bg-transparent px-4 py-3 text-sm text-slate-800 outline-none" />
          <button type="button" class="text-slate-400 hover:text-slate-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </button>
        </div>
      </div>

      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-600">Kata Sandi Baru</label>
        <div
          class="flex items-center rounded-lg border border-slate-200 bg-white pr-3 focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
          <input type="password" placeholder="Kata sandi baru"
            class="w-full rounded-lg border-none bg-transparent px-4 py-3 text-sm text-slate-800 outline-none" />
          <button type="button" class="text-slate-400 hover:text-slate-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </button>
        </div>
      </div>

      <div class="space-y-1">
        <label class="text-xs font-semibold text-slate-600">Konfirmasi Kata Sandi Baru</label>
        <div
          class="flex items-center rounded-lg border border-slate-200 bg-white pr-3 focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
          <input type="password" placeholder="Ketik ulang kata sandi baru"
            class="w-full rounded-lg border-none bg-transparent px-4 py-3 text-sm text-slate-800 outline-none" />
          <button type="button" class="text-slate-400 hover:text-slate-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div
      class="mt-4 grid gap-2 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-700 md:grid-cols-2">
      <div class="space-y-1">
        <p class="font-semibold text-slate-900">Kata sandi harus memiliki:</p>
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
          Minimal 8 karakter
        </div>
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
          Huruf besar
        </div>
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
          Huruf kecil
        </div>
      </div>
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
          Angka
        </div>
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✓</span>
          Simbol (contoh: @, #, $, &amp;, ! atau simbol lainnya.)
        </div>
        <div class="flex items-center gap-2">
          <span
            class="grid h-5 w-5 place-items-center rounded-full border border-slate-200 bg-white text-[10px] font-bold text-slate-600">✕</span>
          Tidak boleh sama dengan kata sandi terakhir
        </div>
      </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
      <button
        class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
        Batal
      </button>
      <button
        class="rounded-lg bg-sky-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-600">
        Simpan
      </button>
    </div>
  </section>
@endsection
