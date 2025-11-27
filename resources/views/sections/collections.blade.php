@php
  $collections = [
      ['title' => 'SUPERDEAL Road to PaDi Business Forum', 'color' => 'from-sky-600 to-sky-500'],
      ['title' => 'Koleksi PAR4DE Jasa Percetakan', 'color' => 'from-amber-500 to-orange-400'],
      ['title' => 'Koleksi Andalan Perjalanan Bisnis', 'color' => 'from-emerald-500 to-teal-400'],
      ['title' => 'Koleksi Perawatan Teknologi', 'color' => 'from-indigo-500 to-blue-500'],
      ['title' => 'Koleksi Produktif di Kantor', 'color' => 'from-purple-500 to-fuchsia-500'],
      ['title' => 'Koleksi Hampers Premium', 'color' => 'from-pink-500 to-rose-400'],
  ];

  $products = collect([
      [
          'title' => 'Stop Kontak Industri 4 Lubang',
          'vendor' => 'CENTRIN AFATEC SUPPLIES',
          'price' => 'Rp680.000',
          'badge' => 'UMKM',
          'location' => 'Jakarta Pusat',
          'sold' => 'Terjual 8',
          'tags' => ['PKP'],
          'image' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=640&q=80',
      ],
      [
          'title' => 'Produksi Kalender 2025 PT Jasamarga',
          'vendor' => 'TIARA IMPRESA GRAFIKA',
          'price' => 'Rp190.612.250',
          'badge' => 'UMKM',
          'location' => 'Kota Depok',
          'sold' => 'Terjual 3',
          'tags' => ['PDN', 'PKP'],
          'image' => 'https://images.unsplash.com/photo-1473186578172-c141e6798cf4?auto=format&fit=crop&w=640&q=80',
      ],
      [
          'title' => 'Dokumentasi Temu Pelanggan',
          'vendor' => 'TIARA IMPRESA GRAFIKA',
          'price' => 'Rp52.087.860',
          'badge' => 'UMKM',
          'location' => 'Kota Depok',
          'sold' => 'Terjual 1',
          'tags' => ['PDN', 'PKP'],
          'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=640&q=80',
      ],
      [
          'title' => 'BROTHER Printer MFC T920DW',
          'vendor' => 'CENTRIN AFATEC SUPPLIES',
          'price' => 'Rp4.900.000',
          'badge' => 'UMKM',
          'location' => 'Jakarta Pusat',
          'sold' => 'Terjual 12',
          'tags' => ['PKP'],
          'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=640&q=80',
      ],
      [
          'title' => 'UPS Kantor 1200VA',
          'vendor' => 'POWERSAFE INDONESIA',
          'price' => 'Rp1.850.000',
          'badge' => 'UMKM',
          'location' => 'Kota Bandung',
          'sold' => 'Terjual 6',
          'tags' => ['PKP'],
          'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=640&q=80',
      ],
      [
          'title' => 'Headset Wireless Meeting',
          'vendor' => 'AUDIOPRO PARTNER',
          'price' => 'Rp1.250.000',
          'badge' => 'UMKM',
          'location' => 'Jakarta Selatan',
          'sold' => 'Terjual 9',
          'tags' => ['PDN', 'PKP'],
          'image' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=640&q=80',
      ],
  ]);
@endphp

@foreach ($collections as $collection)
  <section x-data="collectionSlider()" x-init="init()">
    <div
      class="overflow-hidden rounded-lg bg-linear-to-r {{ $collection['color'] }} p-6 text-white shadow-xl ring-1 ring-black/5">
      <header class="flex flex-wrap items-center gap-3">
        <h3 class="text-2xl font-bold">{{ $collection['title'] }}</h3>
        <a href="{{ route('collection.superdeal') }}"
          class="ml-auto inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-sky-700 shadow transition hover:-translate-y-0.5 hover:shadow-md">
          Lihat Semua
        </a>
      </header>

      <div class="mt-5 flex flex-col gap-4 lg:flex-row lg:items-start">
        <div class="max-w-sm overflow-hidden rounded-lg ring-1 ring-white/20 backdrop-blur-sm lg:min-h-full">
          <img
            src="https://smb-padiumkm-images-public-prod.oss-ap-southeast-5.aliyuncs.com/product-collection/image_section_banner/24112025/superdeal-road-to-padi-business-forum-and-showcase/ed27e7533e0ae63bb045d2520334d1.jpg"
            alt="Koleksi pilihan {{ $collection['title'] }}" class="h-full w-full object-cover" loading="lazy">
        </div>

        <div class="relative flex-1 overflow-hidden">
          <div x-ref="scroller"
            class="grid min-w-full grid-flow-col auto-cols-[75%] gap-4 overflow-x-auto pb-3 pl-1 pr-6 sm:auto-cols-[60%] md:auto-cols-[50%] lg:auto-cols-[var(--card-width-lg)]"
            style="--card-width-lg: calc((100% - 48px) / 4); scroll-padding-inline: 1.5rem;">
            @foreach ($products as $product)
              <article
                class="product-card flex h-full flex-col overflow-hidden rounded-lg border border-white/40 bg-white p-4 text-slate-900 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="-mx-4 -mt-4">
                  <div class="relative h-40 overflow-hidden bg-slate-100">
                    <div
                      class="absolute left-2 top-2 rounded-md bg-sky-500 px-2 py-1 text-[11px] font-semibold uppercase text-white shadow">
                      {{ $product['badge'] }}
                    </div>
                    <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy"
                      class="h-full w-full object-cover">
                  </div>
                </div>

                {{-- mt-4 -> mt-3, gap-3 -> gap-1.5 untuk jarak vertikal lebih kecil --}}
                <div class="mt-3 flex flex-1 flex-col gap-1.5">
                  <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-600">
                    <span
                      class="inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50/60 px-1.5 py-0.5 text-[11px] text-sky-700">
                      <span class="inline-flex h-4 w-4 items-center justify-center">
                        {{-- SVG logo --}}
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path opacity="0.4"
                            d="M10.9349 6.19499V9.18999C10.9349 10.57 9.81494 11.69 8.43494 11.69H4.06494C2.68494 11.69 1.56494 10.57 1.56494 9.18999V6.22999C1.94494 6.63999 2.48494 6.87499 3.06994 6.87499C3.69994 6.87499 4.30494 6.55999 4.68494 6.05499C5.02494 6.55999 5.60494 6.87499 6.24994 6.87499C6.88994 6.87499 7.45994 6.57499 7.80494 6.07499C8.18994 6.56999 8.78494 6.87499 9.40494 6.87499C10.0099 6.87499 10.5599 6.62999 10.9349 6.19499Z"
                            fill="#0092AC" />
                          <path
                            d="M7.74517 1.125H4.74517L4.37517 4.805C4.34517 5.145 4.39517 5.465 4.52017 5.755C4.81017 6.435 5.49017 6.875 6.25017 6.875C7.02017 6.875 7.68517 6.445 7.98517 5.76C8.07517 5.545 8.13017 5.295 8.13517 5.04V4.945L7.74517 1.125Z"
                            fill="#0092AC" />
                          <path opacity="0.6"
                            d="M11.4299 4.635L11.2849 3.25C11.0749 1.74 10.3899 1.125 8.92488 1.125H7.00488L7.37488 4.875C7.37988 4.925 7.38488 4.98 7.38488 5.075C7.41488 5.335 7.49488 5.575 7.61488 5.79C7.97488 6.45 8.67488 6.875 9.40488 6.875C10.0699 6.875 10.6699 6.58 11.0449 6.06C11.3449 5.66 11.4799 5.155 11.4299 4.635Z"
                            fill="#0092AC" />
                          <path opacity="0.6"
                            d="M3.54483 1.125C2.07483 1.125 1.39482 1.74 1.17982 3.265L1.04482 4.64C0.994825 5.175 1.13983 5.695 1.45483 6.1C1.83483 6.595 2.41982 6.875 3.06982 6.875C3.79982 6.875 4.49983 6.45 4.85483 5.8C4.98483 5.575 5.06982 5.315 5.09482 5.045L5.48483 1.13H3.54483V1.125Z"
                            fill="#0092AC" />
                          <path
                            d="M5.92506 8.83C5.29006 8.895 4.81006 9.435 4.81006 10.075V11.69H7.68506V10.25C7.69006 9.205 7.07506 8.71 5.92506 8.83Z"
                            fill="#0092AC" />
                        </svg>
                      </span>
                      <span class="max-w-[120px] truncate text-[8px]">
                        {{ $product['vendor'] }}
                      </span>
                    </span>
                  </div>

                  {{-- h5 tanpa extra margin, biar mepet dengan vendor --}}
                  <h5 class="text-[13px] leading-snug text-slate-900 line-clamp-2">
                    {{ $product['title'] }}
                  </h5>

                  {{-- sedikit dikecilkan dan dirapatkan --}}
                  <p class="text-sm font-bold text-slate-900">
                    {{ $product['price'] }}
                  </p>

                  <div class="flex items-center gap-1 text-[11px] text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-sky-600" viewBox="0 0 20 20"
                      fill="currentColor">
                      <path
                        d="M10 2a6 6 0 0 0-6 6c0 4.418 6 10 6 10s6-5.582 6-10a6 6 0 0 0-6-6Zm0 8.25a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Z" />
                    </svg>
                    <span>{{ $product['location'] }}</span>
                  </div>

                  <div class="text-[11px] text-amber-600">
                    {{ $product['sold'] }}
                  </div>

                  {{-- mt-auto tetap, tapi chip tag dibuat lebih kecil --}}
                  <div class="mt-auto flex flex-wrap gap-1.5">
                    @foreach ($product['tags'] as $tag)
                      <span
                        class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">
                        {{ $tag }}
                      </span>
                    @endforeach
                  </div>
                </div>
              </article>
            @endforeach

          </div>
          <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between px-1">
            <button type="button" @click="prev()"
              class="pointer-events-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow transition hover:scale-105 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
              </svg>
            </button>
            <button type="button" @click="next()"
              class="pointer-events-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow transition hover:scale-105 hover:shadow-md">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="m9 6 6 6-6 6" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
@endforeach
