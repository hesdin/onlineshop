@extends('layouts.app')

@section('title', 'Kategori Makanan & Minuman - TP-PKK Marketplace')

@section('content')
  <section class="space-y-6">
    <nav class="flex items-center gap-2 text-xs text-slate-500">
      <a class="text-sky-600" href="/">Beranda</a>
      <span>/</span>
      <span>Produk</span>
      <span>/</span>
      <span class="text-slate-900 font-semibold">Makanan & Minuman</span>
    </nav>

    <div class="grid grid-cols-1 gap-6 items-start md:grid-cols-12">
      <aside class="space-y-4 md:col-span-3">
        <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
          <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
            <span>Filter</span>
            <button class="text-xs font-semibold text-sky-600">Reset</button>
          </div>
          @php
            $filters = [
                ['title' => 'Kategori', 'options' => ['Makanan Kering', 'Minuman', 'Frozen Food', 'Snack']],
                ['title' => 'Tipe Penjual', 'options' => ['UMKM', 'Vendor Premium', 'H2H']],
                ['title' => 'Lokasi', 'options' => ['DKI Jakarta', 'Jawa Barat', 'Jawa Timur', 'Kalimantan']],
                [
                    'title' => 'Rentang Harga',
                    'options' => ['< Rp500 ribu', 'Rp500 rb - Rp1 jt', 'Rp1 jt - Rp5 jt', '> Rp5 jt'],
                ],
                ['title' => 'Tipe Pembayaran', 'options' => ['Transfer', 'Termin', 'E-Wallet']],
                ['title' => 'Stok Produk', 'options' => ['Ready Stock', 'Pre Order']],
                ['title' => 'Rating', 'options' => ['4+ Bintang', '3+ Bintang']],
                ['title' => 'Sertifikat', 'options' => ['Halal MUI', 'BPOM', 'ISO']],
            ];
          @endphp
          <div class="mt-4 space-y-3">
            @foreach ($filters as $filter)
              <details class="group rounded-xl border border-slate-100 bg-slate-50/60" {{ $loop->first ? 'open' : '' }}>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                  {{ $filter['title'] }}
                  <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-slate-100 px-4 py-3 text-xs text-slate-600">
                  @foreach ($filter['options'] as $option)
                    <label class="flex cursor-pointer items-center gap-2">
                      <input class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" type="checkbox" />
                      <span>{{ $option }}</span>
                    </label>
                  @endforeach
                </div>
              </details>
            @endforeach
          </div>
        </div>

        <div class="rounded-xl bg-slate-900 p-5 text-white shadow-sm">
          <p class="text-sm font-semibold">Butuh bantuan sourcing?</p>
          <p class="mt-2 text-xs text-white/80">Tim procurement PaDi siap bantu kurasi vendor terbaik sesuai kebutuhan
            instansi Anda.</p>
          <button
            class="mt-4 w-full rounded-xl bg-white/15 py-2 text-sm font-semibold text-white transition hover:bg-white/30">Hubungi
            Konsultan</button>
        </div>
      </aside>

      <div class="space-y-4 md:col-span-9">
        <div class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm">
          <div class="flex gap-4 text-slate-500 font-semibold">
            <button class="border-b-2 border-sky-500 pb-2 text-slate-900">Produk</button>
            <button class="pb-2 hover:text-slate-900">Vendor</button>
            <button class="pb-2 hover:text-slate-900">Layanan</button>
          </div>
          <div class="ml-auto flex flex-wrap items-center gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1">
              <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                <circle cx="12" cy="11" r="3" />
              </svg>
              <span>Alamat Pengiriman</span>
              <span class="font-semibold text-slate-800">Belum Tersedia</span>
            </div>
            <select class="rounded-lg border border-slate-200 px-3 py-1 text-xs">
              <option>Urutkan</option>
              <option>Terbaru</option>
              <option>Harga Terendah</option>
              <option>Harga Tertinggi</option>
              <option>Populer</option>
            </select>
          </div>
        </div>

        <p class="text-sm text-slate-600">Menampilkan 1 - 50 produk dari total 121,277 <span
            class="font-semibold text-slate-900">Makanan dan Minuman</span></p>

        @php
          $products = collect(range(1, 24))->map(function ($i) {
              return [
                  'title' => 'Kurasi paket kudapan #' . $i,
                  'vendor' => 'UMKM Kuliner ' . $i,
                  'price' => 'Rp' . number_format(250000 + $i * 15000, 0, ',', '.'),
                  'location' => $i % 2 === 0 ? 'Bandung' : 'Jakarta',
                  'status' => $i % 3 === 0 ? 'Promo' : 'Ready',
                  'image' =>
                      'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&w=600&q=80&sig=' . $i,
              ];
          });
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
          @foreach ($products as $product)
            <article class="flex h-full flex-col overflow-hidden rounded-md border border-slate-100 bg-white shadow-sm">
              <div class="relative">
                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="h-40 w-full object-cover" />
                <span
                  class="absolute left-2 top-2 rounded-lg bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                  UMKM
                </span>
              </div>
              <div class="flex flex-1 flex-col gap-2 p-3">
                <span
                  class="inline-flex w-fit items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                  <span class="h-1.5 w-1.5 rounded-lg bg-sky-500"></span>
                  {{ $product['vendor'] }}
                </span>
                <h3 class="text-sm font-semibold text-slate-900 leading-tight">{{ $product['title'] }}</h3>
                <p class="text-sm font-bold text-slate-900">{{ $product['price'] }}</p>
                <p class="flex items-center gap-1 text-xs text-slate-500">
                  <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                    <circle cx="12" cy="11" r="3" />
                  </svg>
                  {{ $product['location'] }}
                </p>
                <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-slate-600">
                  <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-600">PDN</span>
                  <span class="rounded-md bg-slate-100 px-2 py-0.5 font-semibold text-slate-700">Non PKP</span>
                </div>
              </div>
            </article>
          @endforeach
        </div>

        <div
          class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
          <p class="text-xs text-slate-500">Menampilkan 1-24 dari 256 produk</p>
          <div class="ml-auto flex items-center gap-2">
            <button class="rounded-full border border-slate-200 px-3 py-1 text-xs">Sebelumnya</button>
            <div class="flex items-center gap-1 text-xs">
              @foreach ([1, 2, 3, 4, 5] as $page)
                <button
                  class="h-7 w-7 rounded-full {{ $page === 1 ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-600' }}">
                  {{ $page }}
                </button>
              @endforeach
            </div>
            <button class="rounded-full border border-slate-200 px-3 py-1 text-xs">Selanjutnya</button>
          </div>
        </div>

        <article class="space-y-4 rounded-xl bg-white p-6 shadow">
          <h2 class="text-2xl font-bold text-slate-900">Jual Ragam Pengadaan Makanan dan Minuman Hanya di TP-PKK
            Marketplace</h2>
          <p class="text-sm text-slate-600">Solusi kurasi bagi instansi yang ingin kolaborasi dengan pelaku UMKM, kategori
            makanan dan minuman di PaDi
            menghadirkan pilihan hampers, paket konsumsi, hingga produk kesehatan yang siap dikirimkan ke seluruh
            Indonesia.</p>
          <div class="space-y-4 text-sm text-slate-600">
            <div>
              <h3 class="text-base font-semibold text-slate-900">Sambut Harimu dengan Produk Bergizi</h3>
              <p>Penuhi kebutuhan pantry kantor atau acara perusahaan dengan kudapan sehat, kopi botolan, hingga frozen
                food dari vendor
                terpercaya. Setiap produk telah melewati proses verifikasi agar kualitasnya terjaga.</p>
            </div>
            <div>
              <h3 class="text-base font-semibold text-slate-900">Rasakan Kurasi Hampers Premium</h3>
              <p>Paket parsel dan hampers eksklusif siap dikustomisasi sesuai tema perusahaan. Tim vendor UMKM tersebar di
                berbagai kota,
                memudahkan distribusi massal dengan biaya terjangkau.</p>
            </div>
            <div>
              <h3 class="text-base font-semibold text-slate-900">Bagaimana Cara Menjadi Pemasok?</h3>
              <p>Jika Anda pelaku UMKM kategori makanan dan minuman, segera daftar sebagai mitra TP-PKK Marketplace untuk
                menikmati akses tender dan
                pembeli BUMN. Tim kami siap membantu proses onboarding dan sertifikasi.</p>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
@endsection
