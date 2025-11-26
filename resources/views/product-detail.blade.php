@extends('layouts.app')

@section('title', 'Bubuk Cokelat Klasik - PaDi UMKM')

@section('content')
  <section class="py-6 lg:py-8">
    <div class="mx-auto max-w-6xl space-y-6">
      {{-- Breadcrumb --}}
      <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a class="text-sky-600 hover:underline" href="/">Beranda</a>
        <span>/</span>
        <a class="text-sky-600 hover:underline" href="/kategori">Makanan &amp; Minuman</a>
        <span>/</span>
        <span class="font-semibold text-slate-900">Bubuk Cokelat Klasik</span>
      </nav>

      {{-- Grid besar: kiri (gambar+info+toko+deskripsi) + kanan (atur pembelian) --}}
      <div class="grid gap-6 lg:grid-cols-[minmax(0,2.2fr)_minmax(320px,0.8fr)] items-start">
        {{-- KOLOM KIRI --}}
        <div class="space-y-4 lg:space-y-6">
          {{-- Card utama: gambar + info + biaya pengiriman --}}
          <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 lg:p-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1.4fr)] items-start">
              {{-- Kolom kiri: gambar + thumbnail --}}
              <div>
                <div class="overflow-hidden rounded-2xl border border-slate-200">
                  <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&w=2000&q=80"
                    alt="Bubuk cokelat klasik" class="h-[320px] w-full object-cover md:h-[420px]" />
                </div>

                <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5 md:grid-cols-6">
                  @foreach (range(1, 6) as $thumb)
                    <button type="button"
                      class="overflow-hidden rounded-xl border border-sky-500/0 hover:border-sky-500/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
                      <img
                        src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&w=400&q=80&sig={{ $thumb }}"
                        alt="Galeri {{ $thumb }}" class="h-16 w-full object-cover" />
                    </button>
                  @endforeach
                </div>
              </div>

              {{-- Kolom tengah: judul, harga, info produk, biaya pengiriman --}}
              <div class="space-y-4 lg:space-y-6">
                {{-- Judul & harga --}}
                <div class="border-b border-slate-200 pb-4 space-y-3">
                  <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
                    Bubuk Cokelat Klasik Original
                  </h1>

                  {{-- Harga + diskon (dummy) --}}
                  <div class="space-y-1">
                    <p class="text-2xl sm:text-3xl font-bold text-sky-600">
                      Rp15.000
                    </p>
                    <div class="flex items-center gap-2 text-xs">
                      <span
                        class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-semibold bg-sky-50 text-sky-700">
                        17%
                      </span>
                      <span class="text-slate-400 line-through">
                        Rp18.500
                      </span>
                    </div>
                  </div>

                  {{-- Terjual & rating --}}
                  <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                    <div class="flex items-center gap-1">
                      <span class="font-semibold text-slate-900">4</span>
                      <span>Terjual</span>
                    </div>
                    <span>·</span>
                    <div class="flex items-center gap-1">
                      <svg class="h-3.5 w-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
                      </svg>
                      <span class="font-semibold text-slate-900">5.0</span>
                      <span>(0 Ulasan)</span>
                    </div>
                  </div>

                  {{-- Badge --}}
                  <div class="flex flex-wrap items-center gap-2 pt-1 text-[11px] font-semibold">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">PDN</span>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-sky-700">PPh22</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">Non PKP</span>
                  </div>
                </div>

                {{-- Informasi produk --}}
                <div class="space-y-4 border-b border-slate-200 pb-4">
                  <p class="text-base font-semibold text-slate-900">Informasi Produk</p>

                  <div class="space-y-2 text-sm">
                    <div class="flex gap-6">
                      <div class="w-40 text-slate-400">Kategori</div>
                      <div class="flex-1 font-semibold text-slate-800">
                        Makanan &amp; Minuman, Bahan Kue, Cokelat Bubuk
                      </div>
                    </div>

                    <div class="flex gap-6">
                      <div class="w-40 text-slate-400">Brand</div>
                      <div class="flex-1 font-semibold text-slate-800">
                        Klasik Cokelat
                      </div>
                    </div>

                    <div class="flex gap-6">
                      <div class="w-40 text-slate-400">Min Pembelian</div>
                      <div class="flex-1 font-semibold text-slate-800">
                        1 pcs
                      </div>
                    </div>

                    <div class="flex gap-6">
                      <div class="w-40 text-slate-400">Berat Satuan</div>
                      <div class="flex-1 font-semibold text-slate-800">
                        200 gram
                      </div>
                    </div>

                    <div class="flex gap-6">
                      <div class="w-40 text-slate-400">Dimensi Ukuran</div>
                      <div class="flex-1 font-semibold text-slate-800">
                        10x10x15cm<br>
                        <span class="font-normal text-slate-600">(Berat volume: 0.25kg)</span>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Biaya Pengiriman --}}
                <div class="space-y-4">
                  <p class="text-base font-semibold text-slate-900">Biaya Pengiriman</p>

                  <div class="space-y-3 text-sm">
                    {{-- Dikirim dari --}}
                    <div class="flex items-center gap-3">
                      <span class="flex h-6 w-6 items-center justify-center text-slate-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                          <path d="M3 10.5 4.5 5h15L21 10.5" />
                          <path d="M5 11h14v8H5z" />
                          <path d="M10 15h4" />
                        </svg>
                      </span>
                      <p>
                        <span class="text-slate-400">Dikirim Dari </span>
                        <span class="font-semibold text-slate-800">Kab. Sidoarjo</span>
                      </p>
                    </div>

                    {{-- Pilih alamat --}}
                    <button type="button"
                      class="flex w-full items-center justify-between gap-3 text-slate-500 hover:text-slate-700">
                      <div class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center">
                          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6">
                            <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                            <circle cx="12" cy="9.5" r="2.3" />
                          </svg>
                        </span>
                        <span class="font-semibold">Pilih Alamat</span>
                      </div>
                      <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </button>

                    {{-- Pilih kurir --}}
                    <button type="button"
                      class="flex w-full items-center justify-between gap-3 text-slate-500 hover:text-slate-700">
                      <div class="flex items-center gap-3">
                        <span class="flex h-6 w-6 items-center justify-center">
                          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6">
                            <path d="M3 6h11v9H3z" />
                            <path d="M14 10h4l2 3v2h-6z" />
                            <circle cx="7" cy="17" r="1.4" />
                            <circle cx="17" cy="17" r="1.4" />
                          </svg>
                        </span>
                        <span class="font-semibold">Pilih Kurir</span>
                      </div>
                      <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path d="M7 4.5 12 10l-5 5.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- KARTU TOKO --}}
          <div
            class="rounded-2xl border border-slate-200 bg-white px-4 py-4 sm:px-5 sm:py-4 flex flex-wrap items-center gap-4 lg:gap-6">
            <div class="flex items-center gap-3">
              <img class="h-12 w-12 rounded-full border border-slate-200 object-cover"
                src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&w=200&q=80"
                alt="UMKM" />
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-900">Cokelat Klasik Porong</p>
              <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                <span class="rounded-full bg-emerald-50 px-3 py-0.5">UMKM</span>
                <span class="rounded-full bg-slate-100 px-3 py-0.5">Non PKP</span>
                <span class="flex items-center gap-1">
                  <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2a6 6 0 00-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 00-6-6z" />
                  </svg>
                  Kab. Sidoarjo
                </span>
              </div>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
              <span class="rounded-full bg-amber-50 px-3 py-1 flex items-center gap-1">
                <span class="inline-block h-4 w-4 rounded-full bg-amber-400"></span>
                BUMN Pengampu
              </span>
              <span class="rounded-full bg-slate-50 px-3 py-1">
                0 Transaksi Selesai
              </span>
              <span class="rounded-full bg-slate-50 px-3 py-1">
                Rating &amp; Ulasan
              </span>
            </div>

            <button type="button"
              class="ml-auto rounded-full border border-slate-200 px-5 py-1.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50">
              Ikuti Toko
            </button>
          </div>

          {{-- DESKRIPSI + TAB --}}
          <div class="rounded-2xl border border-slate-200 bg-white">
            <div class="flex gap-6 border-b border-slate-200 text-sm font-semibold text-slate-500 px-4 sm:px-5">
              <button type="button" class="border-b-2 border-sky-500 pb-3 text-slate-900">
                Deskripsi Produk
              </button>
              <button type="button" class="pb-3 hover:text-slate-900">
                Review
              </button>
            </div>

            <div class="px-4 pb-5 pt-4 sm:px-5 sm:pb-6 space-y-1 text-sm text-slate-600">
              <p>
                Es Cokelat Klasik Original khas dengan rasa cokelat banget dipadukan rasa pahit manis cokelat
                rasanya enak. Isi 1 paket berisi cup, tutup, sedotan, dan bubuk cokelat di dalamnya.
              </p>
            </div>
          </div>
        </div>

        {{-- KOLOM KANAN: ATUR PEMBELIAN --}}
        <aside class="space-y-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 space-y-5 shadow-sm">
            <div class="flex items-center justify-between">
              <p class="text-base font-semibold text-slate-900">Atur Pembelian</p>
              <p class="text-xs text-slate-500">Stok: 250</p>
            </div>

            <div class="space-y-2">
              <p class="text-sm font-semibold text-slate-400">Jumlah Pembelian</p>

              <div class="inline-flex overflow-hidden rounded-md border border-slate-200 bg-slate-50">
                <button type="button" class="px-4 py-2 text-lg text-slate-500 hover:bg-slate-100">
                  –
                </button>

                <input type="text" value="1"
                  class="w-14 border-x border-slate-200 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-800 focus:outline-none" />

                <button type="button" class="px-4 py-2 text-lg text-slate-500 hover:bg-slate-100">
                  +
                </button>
              </div>
            </div>

            <div class="space-y-1 pt-2">
              <p class="text-sm font-semibold text-slate-400">Total Harga</p>
              <p class="text-2xl font-bold text-sky-600 sm:text-3xl">
                Rp15.000
              </p>
            </div>

            <div
              class="border-t border-dashed border-slate-200 pt-3 text-xs sm:text-sm text-slate-500 flex items-center justify-between">
              <span>Rp15.000</span>
              <span class="text-slate-400">(inc. PPN)</span>
            </div>

            <div class="border-t border-slate-200 pt-3">
              <button type="button"
                class="inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:text-sky-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path d="M4 13.5 13.5 4a1.4 1.4 0 1 1 2 2L6 15.5 3 16.5z" />
                </svg>
                <span>Catatan Penjual</span>
              </button>
            </div>

            <div class="grid gap-3 pt-1 sm:grid-cols-2">
              <button type="button"
                class="rounded-xl border border-sky-500 bg-white py-2.5 text-sm font-semibold text-sky-600 hover:bg-sky-50">
                Chat Penjual
              </button>

              <button type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-500 py-2.5 text-sm font-semibold text-white hover:bg-sky-600">
                <span class="text-lg leading-none">+</span>
                <span>Keranjang</span>
              </button>
            </div>

            <button type="button"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sky-500 bg-white py-2.5 text-sm font-semibold text-sky-600 hover:bg-sky-50">
              <span>Ajukan Permintaan</span>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 10h6L8 4m2 6 2 6" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
        </aside>
      </div>

      {{-- LAINNYA DI TOKO INI - FULL WIDTH (di luar grid, selebar container) --}}
      <section class="mt-6 lg:mt-8">
        <div class="mb-4 flex items-center justify-between px-1 sm:px-0">
          <h2 class="text-lg font-semibold text-slate-900">
            Lainnya Di Toko ini
          </h2>
          <button type="button"
            class="inline-flex items-center gap-1 text-sm font-semibold text-sky-600 hover:text-sky-700">
            <span>Lihat Semua</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path d="M7.25 4.75L12.5 10l-5.25 5.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                stroke-linejoin="round" fill="none" />
            </svg>
          </button>
        </div>

        <div class="relative -mx-4 overflow-x-auto pb-4">
          <div class="flex gap-4 px-4">
            @foreach (range(1, 7) as $item)
              <article class="min-w-[210px] max-w-[230px] flex-1 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-hidden rounded-t-2xl">
                  <img
                    src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&w=800&q=80&sig={{ $item }}"
                    alt="Produk lainnya {{ $item }}" class="h-40 w-full object-cover" />
                </div>

                <div class="space-y-2 p-3">
                  <span
                    class="inline-block rounded-sm bg-sky-500 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                    UMKM
                  </span>

                  <p class="line-clamp-2 text-sm font-semibold text-slate-900">
                    R0{{ $item }}0{{ $item }} - Contoh Nama Produk Lain di Toko Ini
                  </p>

                  <div class="space-y-0.5 text-xs">
                    <p class="text-sm font-bold text-slate-900">
                      Rp{{ number_format(20000 * $item, 0, ',', '.') }}
                    </p>
                    <div class="flex items-center gap-2">
                      <span class="text-[11px] text-slate-400 line-through">
                        Rp{{ number_format(23000 * $item, 0, ',', '.') }}
                      </span>
                      <span class="text-[11px] font-semibold text-sky-600">
                        17%
                      </span>
                    </div>
                  </div>

                  <div class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                      <path
                        d="M10 2.5a5.25 5.25 0 00-5.25 5.25c0 3.714 4.338 7.458 4.83 7.88a.56.56 0 00.72 0c.492-.422 4.95-4.166 4.95-7.88A5.25 5.25 0 0010 2.5zm0 7.25a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                    <span>Kota Depok</span>
                  </div>

                  <div class="mt-2 flex flex-wrap gap-1">
                    <span
                      class="inline-block rounded-md border border-sky-100 bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                      Non PKP
                    </span>
                  </div>
                </div>
              </article>
            @endforeach
          </div>

          <div class="pointer-events-none absolute inset-y-0 right-0 hidden items-center pr-3 sm:flex">
            <span
              class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white shadow-md">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M7.25 4.75L12.5 10l-5.25 5.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                  stroke-linejoin="round" fill="none" />
              </svg>
            </span>
          </div>
        </div>
      </section>
    </div>
  </section>
@endsection
