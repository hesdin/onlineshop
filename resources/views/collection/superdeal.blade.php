@extends('layouts.app')

@section('title', 'SUPERDEAL Road to PaDi Business Forum and Showcase')

@section('content')
  @php
    $filters = [
        [
            'title' => 'Kategori',
            'options' => [
                'Mainan & Hobi',
                'Puzzle',
                'Olahraga',
                'Teknologi',
                'Perawatan Rumah',
                'Aksesori Komputer & Laptop',
                'Komputer & Laptop',
                'Software',
                'Lihat Semua',
            ],
        ],
        [
            'title' => 'Tipe Penjual',
            'options' => ['Premium', 'UMKM', 'Vendor Besar', 'Koperasi', 'PKP', 'Non PKP'],
        ],
        [
            'title' => 'Lokasi',
            'options' => ['DKI Jakarta', 'Jawa Tengah', 'Jawa Timur', 'Jawa Barat', 'Banten', 'Lihat Semua'],
        ],
        ['title' => 'Rentang Harga', 'type' => 'price'],
        ['title' => 'Stok Produk', 'options' => ['Pre Order']],
        ['title' => 'Rating', 'options' => ['4 Ke atas']],
        ['title' => 'Sertifikat', 'options' => ['CSMS', 'Produk Dalam Negeri', 'TKDN']],
    ];

    $products = collect([
        [
            'title' => 'Global Natura Kreasi Souvenir — Paket Dukit Nama Gift Box Souvenir Hampers',
            'vendor' => 'Global Natura Kreasi Souvenir',
            'price' => 'Rp667.000',
            'badge' => 'UMKM',
            'location' => 'Kota Bogor',
            'sold' => 'Terjual 9',
            'status' => 'Pre Order',
            'tags' => ['PDN', 'Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&w=640&q=80&sig=101',
        ],
        [
            'title' => 'Stop Kontak Industri 4 Lubang',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp680.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 8',
            'status' => null,
            'tags' => ['PKP'],
            'image' => 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&w=640&q=80&sig=102',
        ],
        [
            'title' => 'Produksi Kalender 2025 PT Jasamarga',
            'vendor' => 'TIARA IMPRESA GRAFIKA',
            'price' => 'Rp190.612.250',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 3',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&w=640&q=80&sig=103',
        ],
        [
            'title' => 'Dokumentasi Temu Pelanggan',
            'vendor' => 'TIARA IMPRESA GRAFIKA',
            'price' => 'Rp52.087.860',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 1',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&w=640&q=80&sig=104',
        ],
        [
            'title' => 'BROTHER Printer MFC T920DW',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp4.900.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 12',
            'status' => null,
            'tags' => ['PKP'],
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&w=640&q=80&sig=105',
        ],
        [
            'title' => 'Samsung Smart TV UHD 65 Inch Crystal',
            'vendor' => 'SAMSUNG',
            'price' => 'Rp18.030.000',
            'badge' => 'UMKM',
            'location' => 'Kota Tangerang Selatan',
            'sold' => 'Terjual 2',
            'status' => 'Diskon 50%',
            'tags' => ['TKDN 22%', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&w=640&q=80&sig=106',
        ],
        [
            'title' => 'Jasa Event Management HUT ke-60',
            'vendor' => 'TIARA IMPRESA GRAFIKA',
            'price' => 'Rp16.015.000',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 3',
            'status' => null,
            'tags' => ['Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&w=640&q=80&sig=107',
        ],
        [
            'title' => 'Recycled JICO Used Cooking Oil (Jamu) Kemasan Jerigen',
            'vendor' => 'PT. Tridaya Library Indonesia',
            'price' => 'Rp108.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Selatan',
            'sold' => 'Terjual 12',
            'status' => 'Pre Order',
            'tags' => ['Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&w=640&q=80&sig=108',
        ],
        [
            'title' => 'Container Box Shipping Storage Multi Fungsi (Rigid Drum)',
            'vendor' => 'KARUNIA FURNITURE MULTI USAHA',
            'price' => 'Rp3.165.000',
            'badge' => 'UMKM',
            'location' => 'Kota Tangerang Selatan',
            'sold' => 'Terjual 4',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1492724441997-5dc865305da7?auto=format&w=640&q=80&sig=109',
        ],
        [
            'title' => 'Lemari Arsip Kantor Plat Tulus Sliding',
            'vendor' => 'KARUNIA FURNITURE MULTI USAHA',
            'price' => 'Rp2.450.000',
            'badge' => 'UMKM',
            'location' => 'Kota Tangerang Selatan',
            'sold' => 'Terjual 11',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&w=640&q=80&sig=110',
        ],
        [
            'title' => 'Tas Utility Pro Foldable Shipping Bag',
            'vendor' => 'PT. Tridaya Library Indonesia',
            'price' => 'Rp180.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Barat',
            'sold' => 'Terjual 10',
            'status' => 'Pre Order',
            'tags' => ['Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1526171115624-4c64cd57b0c6?auto=format&w=640&q=80&sig=111',
        ],
        [
            'title' => 'Samsung Smart TV Crystal UHD 50 Inch',
            'vendor' => 'SAMSUNG',
            'price' => 'Rp7.803.000',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 1',
            'status' => 'Diskon 20%',
            'tags' => ['TKDN 22%', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&w=640&q=80&sig=112',
        ],
        [
            'title' => 'Bantex Junco Box File 4011 9,5 cm',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp3.850.000',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 1',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&w=640&q=80&sig=113',
        ],
        [
            'title' => 'Kursi Hidup Kantor Model Siff K3J',
            'vendor' => 'KARUNIA FURNITURE MULTI USAHA',
            'price' => 'Rp735.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 2',
            'status' => null,
            'tags' => ['PKP'],
            'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&w=640&q=80&sig=114',
        ],
        [
            'title' => 'Kabel HDMI 2 Meter Vention Kualitas Premium',
            'vendor' => 'VENTION',
            'price' => 'Rp66.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 25',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&w=640&q=80&sig=115',
        ],
        [
            'title' => 'Printer HP DeskJet 2332 AIO',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp1.235.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 7',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&w=640&q=80&sig=116',
        ],
        [
            'title' => 'Tumbler Stainless Steel Botol dengan Bamboo Cap',
            'vendor' => 'Global Natura Kreasi Souvenir',
            'price' => 'Rp93.000',
            'badge' => 'UMKM',
            'location' => 'Kota Depok',
            'sold' => 'Terjual 4',
            'status' => 'Ready',
            'tags' => ['PDN', 'Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&w=640&q=80&sig=117',
        ],
        [
            'title' => 'Lenovo ThinkPad E14 Gen 5 (Intel)',
            'vendor' => 'SAMSUNG',
            'price' => 'Rp11.859.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 2',
            'status' => null,
            'tags' => ['TKDN 19,18%', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&w=640&q=80&sig=118',
        ],
        [
            'title' => 'Dell Point Screen 0,5 Inch',
            'vendor' => 'DELL',
            'price' => 'Rp175.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Pusat',
            'sold' => 'Terjual 3',
            'status' => null,
            'tags' => ['PDN', 'PKP'],
            'image' => 'https://images.unsplash.com/photo-1470246973918-29a93221c455?auto=format&w=640&q=80&sig=119',
        ],
        [
            'title' => 'Canvas Multi Pocket Tote',
            'vendor' => 'PT. Tridaya Library Indonesia',
            'price' => 'Rp137.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Selatan',
            'sold' => 'Terjual 5',
            'status' => 'Pre Order',
            'tags' => ['Non PKP'],
            'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&w=640&q=80&sig=120',
        ],
        [
            'title' => 'Mini Speaker Portable Wireless',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp285.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Utara',
            'sold' => 'Terjual 14',
            'status' => null,
            'tags' => ['PKP'],
            'image' => 'https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&w=640&q=80&sig=121',
        ],
        [
            'title' => 'HDD External 2TB Tough Drive',
            'vendor' => 'CENTRIN AFATEC SUPPLIES',
            'price' => 'Rp1.299.000',
            'badge' => 'UMKM',
            'location' => 'Jakarta Selatan',
            'sold' => 'Terjual 6',
            'status' => null,
            'tags' => ['PKP'],
            'image' => 'https://images.unsplash.com/photo-1518773553398-650c184e0bb3?auto=format&w=640&q=80&sig=122',
        ],
    ]);
  @endphp

  <section class="space-y-6">
    <nav class="flex items-center gap-2 text-xs text-slate-500">
      <a class="text-sky-600" href="/">Beranda</a>
      <span>/</span>
      <span class="font-semibold text-slate-900">SUPERDEAL Road to PaDi Business Forum and Showcase</span>
    </nav>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <img
        src="https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/product-collection/image_section_banner/24112025/superdeal-road-to-padi-business-forum-and-showcase/ed27e7533e0ae63bb045d2520334d1.jpg"
        alt="SUPERDEAL Road to PaDi Business Forum and Showcase" class="h-64 w-full object-cover md:h-72 lg:h-80" />
    </div>

    <div class="flex flex-wrap items-start gap-4">
      <div class="space-y-1">
        <p class="text-xs font-semibold uppercase text-slate-500">Koleksi</p>
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">
          SUPERDEAL Road to PaDi Business Forum and Showcase
        </h1>
      </div>
      <a href="#" class="ml-auto inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:underline">
        Syarat &amp; Ketentuan
      </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
      <aside class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex items-center justify-between text-sm font-semibold text-slate-900">
            <span>Filter</span>
            <button class="text-xs font-semibold text-sky-600">Reset</button>
          </div>

          <div class="mt-4 space-y-3">
            @foreach ($filters as $filter)
              @if (($filter['type'] ?? null) === 'price')
                <div class="space-y-3 rounded-lg border border-slate-100 bg-slate-50/60 p-4">
                  <p class="text-sm font-semibold text-slate-900">Rentang Harga</p>
                  <div class="space-y-2 text-xs">
                    <label class="block text-slate-500">Harga Terendah</label>
                    <input type="text" placeholder="Rp 0"
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                  </div>
                  <div class="space-y-2 text-xs">
                    <label class="block text-slate-500">Harga Tertinggi</label>
                    <input type="text" placeholder="Rp 500.000"
                      class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-500" />
                  </div>
                  <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-500 focus:ring-sky-500" />
                    Harga Diskon
                  </label>
                </div>
              @else
                <details class="group rounded-lg border border-slate-100 bg-slate-50/60" {{ $loop->first ? 'open' : '' }}>
                  <summary
                    class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-semibold text-slate-800">
                    {{ $filter['title'] }}
                    <svg class="h-4 w-4 text-slate-400 transition group-open:rotate-180" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2">
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
              @endif
            @endforeach

            <div class="rounded-lg bg-slate-900 p-5 text-white">
              <p class="text-sm font-semibold">Butuh bantuan sourcing?</p>
              <p class="mt-2 text-xs text-white/80">
                Tim procurement siap bantu kurasi produk terbaik sesuai kebutuhan instansi Anda.
              </p>
              <button
                class="mt-4 w-full rounded-lg bg-white/15 py-2 text-sm font-semibold text-white transition hover:bg-white/25">
                Hubungi Konsultan
              </button>
            </div>
          </div>
        </div>
      </aside>

      <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-4">
          <div class="min-w-0 flex-1">
            <div class="flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2.5">
              <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <circle cx="11" cy="11" r="7" />
                <path d="m16 16 4 4" />
              </svg>
              <input type="text" placeholder="Cari di dalam koleksi"
                class="ml-3 w-full bg-transparent text-sm text-slate-700 placeholder:text-slate-400 outline-none" />
            </div>
          </div>

          <div class="relative inline-flex min-w-[180px]">
            <select
              class="w-full appearance-none rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm font-medium text-slate-700 outline-none">
              <option>Toko</option>
              <option>Semua Toko</option>
              <option>Global Natura Kreasi Souvenir</option>
              <option>CENTRIN AFATEC SUPPLIES</option>
              <option>TIARA IMPRESA GRAFIKA</option>
              <option>PT. Tridaya Library Indonesia</option>
              <option>KARUNIA FURNITURE MULTI USAHA</option>
              <option>SAMSUNG</option>
              <option>VENTION</option>
              <option>DELL</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 inline-flex items-center">
              <svg class="h-4 w-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="m6 9 6 6 6-6" />
              </svg>
            </span>
          </div>

          <div class="relative inline-flex min-w-[150px]">
            <select
              class="w-full appearance-none rounded-lg border border-slate-200 bg-white px-4 py-2 pr-10 text-sm font-medium text-slate-700 outline-none">
              <option>Urutkan</option>
              <option>Harga Terendah</option>
              <option>Harga Tertinggi</option>
              <option>Ulasan</option>
              <option>Terlaris</option>
            </select>
            <span class="pointer-events-none absolute inset-y-0 right-3 inline-flex items-center">
              <svg class="h-4 w-4 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="m6 9 6 6 6-6" />
              </svg>
            </span>
          </div>
        </div>

        <p class="text-sm text-slate-600">
          Menampilkan {{ $products->count() }} produk dari koleksi
          <span class="font-semibold text-slate-900">SUPERDEAL Road to PaDi Business Forum and Showcase</span>
        </p>

        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
          @foreach ($products as $product)
            <article class="flex h-full flex-col overflow-hidden rounded-lg border border-slate-100 bg-white shadow-sm">
              <div class="relative">
                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" class="h-44 w-full object-cover" />
                <span
                  class="absolute left-2 top-2 rounded-full bg-sky-600 px-2.5 py-0.5 text-[11px] font-semibold uppercase text-white shadow-sm">
                  {{ $product['badge'] }}
                </span>
                @if (!empty($product['status']))
                  <span
                    class="absolute right-2 top-2 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 shadow-sm">
                    {{ $product['status'] }}
                  </span>
                @endif
              </div>

              <div class="flex flex-1 flex-col gap-2 p-3">
                <span
                  class="inline-flex w-fit items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                  <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                  {{ $product['vendor'] }}
                </span>

                <h3 class="line-clamp-2 text-sm font-semibold leading-tight text-slate-900">
                  {{ $product['title'] }}
                </h3>

                <p class="text-sm font-bold text-slate-900">{{ $product['price'] }}</p>

                <p class="flex items-center gap-1 text-xs text-slate-500">
                  <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.657 16.657 13.414 20.9a1 1 0 0 1-1.414 0L6.343 15.243a8 8 0 1 1 11.314 1.414Z" />
                    <circle cx="12" cy="11" r="3" />
                  </svg>
                  {{ $product['location'] }}
                </p>

                @if (!empty($product['sold']))
                  <p class="text-[11px] font-semibold text-amber-600">
                    {{ $product['sold'] }}
                  </p>
                @endif

                <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] text-slate-600">
                  @foreach ($product['tags'] as $tag)
                    <span
                      class="rounded-md bg-slate-100 px-2 py-0.5 font-semibold text-slate-700">{{ $tag }}</span>
                  @endforeach
                </div>
              </div>
            </article>
          @endforeach
        </div>

        <div
          class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-white px-5 py-4 text-sm text-slate-600">
          <p class="text-xs text-slate-500">Menampilkan 1-{{ $products->count() }} dari {{ $products->count() }} produk
          </p>
          <div class="ml-auto flex items-center gap-2">
            <button class="rounded-full border border-slate-200 px-3 py-1 text-xs hover:bg-slate-50">Sebelumnya</button>
            <div class="flex items-center gap-1 text-xs">
              @foreach ([1, 2, 3, 4, 5] as $page)
                <button
                  class="h-7 w-7 rounded-full {{ $page === 1 ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                  {{ $page }}
                </button>
              @endforeach
            </div>
            <button class="rounded-full border border-slate-200 px-3 py-1 text-xs hover:bg-slate-50">Selanjutnya</button>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
