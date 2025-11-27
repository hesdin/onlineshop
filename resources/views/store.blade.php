@extends('layouts.app')

@section('title', 'Global Natura Kreativa - TP-PKK Marketplace')

@section('content')
  @php
    $filters = [
        [
            'title' => 'Kategori',
            'options' => ['Jasa Percetakan & Media', 'Office & Stationery', 'Souvenir & Merchandise', 'Wedding'],
        ],
        [
            'title' => 'Tipe Pembayaran',
            'options' => [
                'Bisa Termin Jasa',
                'Bisa Tempo 90 Hari',
                'Bisa Tempo 120 Hari',
                'Bisa Tempo 150 Hari',
                'Bisa Tempo 180 Hari',
            ],
        ],
        ['title' => 'Stok Produk', 'options' => ['Pre Order']],
        ['title' => 'Sertifikat', 'options' => ['Produk Dalam Negeri', 'TKDN']],
    ];

    $products = collect(range(1, 20))->map(function ($i) {
        return [
            'title' => 'Gift Box Souvenir Hampers Seminar Kit Kayu #' . $i,
            'price' => 'Rp' . number_format(400000 + $i * 12000, 0, ',', '.'),
            'location' => 'Kota Bogor',
            'tag' => $i % 4 === 0 ? 'Pre Order' : 'Ready',
            'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&w=1200&q=80&sig=' . $i,
        ];
    });

    $stats = [
        ['label' => 'BUMN Pengampu', 'value' => '1'],
        ['label' => 'Transaksi Selesai', 'value' => '7'],
        ['label' => 'Rating & Ulasan', 'value' => '0'],
        ['label' => 'Waktu Respons', 'value' => 'Cepat'],
    ];
  @endphp

  <section class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:py-8">
    <!-- Breadcrumb -->
    {{-- <nav class="mb-6 flex items-center gap-2 text-xs text-neutral-500">
      <a href="/" class="font-medium text-sky-600 hover:underline">Beranda</a>
      <span>/</span>
      <span class="font-semibold text-neutral-900">Toko</span>
      <span>/</span>
      <span class="font-semibold text-neutral-900">Global Natura Kreativa</span>
    </nav> --}}

    <!-- Banner toko -->
    <div class="space-y-4">
      <!-- Gambar banner penuh -->
      <div class="overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100">
        <img
          src="https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/seller/banner_image/18122023/631a5d56aa3096cbda26050f/2edcf9ca34478d3dcc12565b4a56e9.jpg?x-oss-process=image/resize,m_fill,w_1200,h_300/quality,Q_50"
          alt="Premium Natural Corporate Gift" class="h-[220px] w-full object-cover sm:h-[260px] lg:h-[280px]" />
      </div>

      <!-- Kartu info toko (seperti di screenshot) -->
      <div
        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-white px-6 py-4 shadow-sm">
        <!-- Kiri: logo + info dasar -->
        <div class="flex min-w-0 items-center gap-4">
          <!-- Logo + badge UMKM -->
          <div class="relative">
            <div class="grid size-14 place-items-center rounded-full border border-neutral-200 bg-white">
              {{-- placeholder logo toko --}}
              <span class="text-xs font-semibold text-neutral-500">LOGO</span>
            </div>
            <span
              class="absolute -bottom-1 left-1 inline-flex items-center rounded-lg bg-sky-500 px-2.5 py-0.5 text-[10px] font-semibold text-white shadow-sm">
              UMKM
            </span>
          </div>

          <div class="min-w-0 space-y-1">
            <p class="truncate text-base font-semibold text-neutral-900 sm:text-lg">
              Global Natura Kreativa
            </p>

            <div
              class="inline-flex items-center rounded-lg bg-neutral-100 px-3 py-1 text-[11px] font-semibold text-neutral-700">
              Non PKP
            </div>

            <p class="flex items-center gap-1 text-sm text-neutral-500">
              <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                <circle cx="12" cy="11" r="3" />
              </svg>
              Kota Bogor
            </p>
          </div>
        </div>

        <!-- Kanan: 3 statistik + tombol chat -->
        <div class="flex flex-1 flex-wrap items-center justify-end gap-6">
          <!-- BUMN Pengampu -->
          <div class="flex items-center gap-2 text-sm">
            <div class="grid size-10 place-items-center rounded-full bg-sky-50">
              {{-- ikon BUMN pengampu (taspen) --}}
              <span class="text-[10px] font-semibold text-sky-600">BUMN</span>
            </div>
            <div class="text-left">
              <p class="text-xs text-neutral-500">BUMN Pengampu</p>
              <p class="text-sm font-semibold text-neutral-800">Taspen</p>
            </div>
          </div>

          <!-- Transaksi Selesai -->
          <div class="flex items-center gap-2 text-sm">
            <div class="grid size-10 place-items-center rounded-full bg-sky-50">
              <svg class="size-5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="4" y="4" width="16" height="16" rx="2" />
                <path d="M8 10h8M8 14h4" />
              </svg>
            </div>
            <div class="text-left">
              <p class="text-xs text-neutral-500">Transaksi Selesai</p>
              <p class="text-sm font-semibold text-neutral-800">7</p>
            </div>
          </div>

          <!-- Rating & Ulasan -->
          <div class="flex items-center gap-2 text-sm">
            <div class="grid size-10 place-items-center rounded-full bg-amber-50">
              <svg class="size-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 2.5 12.4 7l5 .7-3.7 3.6.9 5-4.6-2.4L5.4 16l.9-5L2.6 7.7l5-.7z" />
              </svg>
            </div>
            <div class="text-left">
              <p class="flex items-center gap-1 text-sm font-semibold text-neutral-800">
                0
              </p>
              <p class="text-xs text-neutral-500">Rating &amp; Ulasan</p>
            </div>
          </div>

          <!-- Tombol Chat Penjual -->
          <button type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-sky-500 px-5 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M4 6h16v9H7l-3 3V6Z" />
            </svg>
            Chat Penjual
          </button>
        </div>
      </div>
    </div>


    <!-- Konten utama -->
    <div class="mt-6 grid gap-6 lg:grid-cols-[296px_minmax(0,1fr)]">
      <!-- Sidebar Filter -->
      <aside class="space-y-4">
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm">
          <div class="flex items-center justify-between text-sm font-semibold text-neutral-900">
            <span>Filter</span>
            <button class="text-xs font-semibold text-sky-600">Reset</button>
          </div>

          <div class="mt-4 space-y-4">
            <!-- Rentang Harga -->
            <div class="space-y-3 rounded-xl border border-neutral-100 bg-neutral-50/60 p-4">
              <p class="text-sm font-semibold text-neutral-900">Rentang Harga</p>

              <div class="space-y-2 text-xs">
                <label class="block text-neutral-500">Harga Terendah</label>
                <input type="text" inputmode="numeric" placeholder="Rp 0"
                  class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
              </div>

              <div class="space-y-2 text-xs">
                <label class="block text-neutral-500">Harga Tertinggi</label>
                <input type="text" inputmode="numeric" placeholder="Rp 500.000"
                  class="w-full rounded-lg border border-neutral-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
              </div>

              <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500" />
                Harga Diskon
              </label>
            </div>

            <!-- Group Filter -->
            @foreach ($filters as $filter)
              <details class="group rounded-xl border border-neutral-100 bg-neutral-50/60"
                @if ($loop->first) open @endif>
                <summary
                  class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-neutral-800">
                  {{ $filter['title'] }}
                  <svg class="size-4 text-neutral-400 transition group-open:rotate-180" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </summary>
                <div class="space-y-2 border-t border-neutral-100 px-4 py-3 text-xs text-neutral-700">
                  @foreach ($filter['options'] as $option)
                    <label class="flex cursor-pointer items-center gap-2">
                      <input type="checkbox" class="size-4 rounded border-neutral-300 text-sky-500 focus:ring-sky-500" />
                      <span>{{ $option }}</span>
                    </label>
                  @endforeach
                </div>
              </details>
            @endforeach

            <!-- Callout bantuan -->
            <div class="rounded-xl bg-neutral-900 p-5 text-white shadow-sm">
              <p class="text-sm font-semibold">Butuh bantuan sourcing?</p>
              <p class="mt-2 text-xs/5 opacity-80">
                Tim procurement siap bantu kurasi souvenir terbaik sesuai kebutuhan instansi Anda.
              </p>
              <button class="mt-4 w-full rounded-xl bg-white/15 py-2 text-sm font-semibold text-white hover:bg-white/25">
                Hubungi Konsultan
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Katalog -->
      <div class="space-y-4">
        <!-- Toolbar pencarian & urutan -->
        <div class="flex flex-wrap items-center gap-4">
          <!-- Search -->
          <div class="min-w-0 flex-1">
            <div class="flex items-center rounded-xl border border-neutral-200 bg-white px-4 py-2.5">
              <input type="text" placeholder="Cari produk toko ini"
                class="w-full bg-transparent text-sm text-neutral-700 placeholder:text-neutral-400 outline-none" />

              <span class="ml-2 inline-flex items-center justify-center">
                <svg class="size-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <circle cx="11" cy="11" r="7" />
                  <path d="m16 16 4 4" />
                </svg>
              </span>
            </div>
          </div>

          <!-- Tombol Urutkan -->
          <div class="relative inline-flex">
            <select
              class="appearance-none rounded-xl border border-neutral-200 bg-white px-5 py-2 pr-9 text-sm font-medium text-neutral-700 outline-none">
              <option value="">Urutkan</option>
              <option value="harga_desc">Harga Tertinggi</option>
              <option value="harga_asc">Harga Terendah</option>
              <option value="ulasan">Ulasan</option>
            </select>

            <span class="pointer-events-none absolute inset-y-0 right-3 inline-flex items-center">
              <svg class="size-4 text-neutral-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="m6 9 6 6 6-6" />
              </svg>
            </span>
          </div>

        </div>


        <p class="text-sm text-neutral-600">
          Menampilkan {{ $products->count() }} produk dari
          <span class="font-semibold text-neutral-900">Global Natura Kreativa</span>
        </p>

        <!-- Grid produk -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          @foreach ($products as $product)
            <article
              class="flex h-full flex-col overflow-hidden rounded-lg border border-neutral-100 bg-white shadow-sm">
              <div class="relative">
                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="h-40 w-full object-cover" />
                <span
                  class="absolute left-2 top-2 rounded-full bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold text-white shadow-sm">
                  UMKM
                </span>
                @if ($product['tag'] === 'Pre Order')
                  <span
                    class="absolute right-2 top-2 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 shadow-sm">
                    Pre Order
                  </span>
                @endif
              </div>
              <div class="flex flex-1 flex-col gap-2 p-3">
                <h3 class="line-clamp-2 text-sm font-semibold leading-tight text-neutral-900">
                  {{ $product['title'] }}
                </h3>
                <p class="text-sm font-bold text-neutral-900">{{ $product['price'] }}</p>

                <p class="mt-0.5 flex items-center gap-1 text-xs text-neutral-500">
                  <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                    <circle cx="12" cy="11" r="3" />
                  </svg>
                  {{ $product['location'] }}
                </p>

                <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-neutral-600">
                  <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-600">PDN</span>
                  <span class="rounded-md bg-neutral-100 px-2 py-0.5 font-semibold text-neutral-700">Non PKP</span>
                  <span class="rounded-md bg-sky-50 px-2 py-0.5 font-semibold text-sky-700">
                    {{ $product['tag'] }}
                  </span>
                </div>
              </div>
            </article>
          @endforeach
        </div>

        <!-- Pagination -->
        <div
          class="flex flex-wrap items-center gap-3 rounded-xl border border-neutral-200 bg-white px-5 py-4 text-sm text-neutral-600">
          <p class="text-xs text-neutral-500">Menampilkan 1-{{ $products->count() }} dari {{ $products->count() }}
            produk</p>

          <div class="ml-auto flex items-center gap-2">
            <button
              class="rounded-full border border-neutral-200 px-3 py-1 text-xs hover:bg-neutral-50">Sebelumnya</button>

            <div class="flex items-center gap-1 text-xs">
              @foreach ([1, 2, 3, 4, 5] as $page)
                <button
                  class="size-7 rounded-full {{ $page === 1 ? 'bg-sky-500 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
                  {{ $page }}
                </button>
              @endforeach
            </div>

            <button
              class="rounded-full border border-neutral-200 px-3 py-1 text-xs hover:bg-neutral-50">Selanjutnya</button>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
