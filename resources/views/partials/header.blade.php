<header class="sticky top-0 isolate border-b border-slate-200 bg-white" style="z-index: 9999;">
  {{-- Top bar --}}
  <div class="border-b border-slate-200 bg-slate-50">
    <div class="container mx-auto flex items-center justify-between px-4 py-2 text-xs text-slate-500">
      <nav class="flex items-center gap-4">
        <a href="#" class="hover:text-slate-700">Mitra TP-PKK Marketplace</a>
        <a href="#" class="hover:text-slate-700">Menjadi Penjual</a>
        <a href="#" class="hover:text-slate-700">Info</a>
        <a href="#" class="hover:text-slate-700">Pusat Bantuan</a>
      </nav>

      <div class="flex items-center gap-3">
        {{-- Toggle bahasa dummy --}}
        <div class="flex overflow-hidden rounded-lg border border-slate-200 text-[11px] font-semibold">
          <button class="bg-sky-500 px-3 py-1 text-white">IND</button>
          <button class="bg-slate-100 px-3 py-1 text-slate-500">ENG</button>
        </div>
        {{-- Placeholder logo kecil di kanan --}}
        <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
          Logo BUMN / Lainnya
        </span>
      </div>
    </div>
  </div>

  @php
    // Dummy data to render the mega menu content
    $megaMenuData = [
        [
            'label' => 'Jasa Mandor & Tenaga Kerja Lainnya',
            'columns' => [
                ['Mandor Bangunan', 'Tenaga Kebersihan', 'Teknisi Lapangan', 'Petugas Keamanan'],
                ['Jasa Bongkar Pasang', 'Kuli Angkut', 'Tenaga Harian Lepas'],
            ],
        ],
        [
            'label' => 'Mainan & Hobi',
            'columns' => [
                ['Mainan Edukasi', 'Action Figure', 'Board Game', 'RC & Drone'],
                ['Kerajinan Tangan', 'Alat Musik Hobi', 'Koleksi Miniatur'],
            ],
        ],
        [
            'label' => 'Olahraga',
            'columns' => [
                ['Aksesoris Olahraga', 'Golf', 'Pakaian Olahraga Pria', 'Sepak Bola & Futsal', 'Voli'],
                ['Badminton', 'Gym & Fitness', 'Pakaian Olahraga Wanita', 'Sepatu Roda & Skateboard', 'Yoga & Pilates'],
                ['Baseball', 'Hiking & Camping', 'Panahan', 'Sepeda'],
                ['Basket', 'Olahraga Air', 'Perlengkapan Lari', 'Tenis meja'],
            ],
        ],
        [
            'label' => 'Pengadaan & Sewa Kendaraan',
            'columns' => [
                ['Sewa Mobil', 'Sewa Truk', 'Sewa Motor', 'Sewa Bus Pariwisata'],
                ['Sewa Alat Berat', 'Sewa Pick Up', 'Jasa Pengemudi'],
            ],
        ],
        [
            'label' => 'Jasa Advertising',
            'columns' => [
                ['Percetakan', 'Spanduk & Banner', 'Billboard', 'Digital Ads'],
                ['Production Video', 'Foto Produk', 'Voice Over'],
            ],
        ],
        [
            'label' => 'Jasa Perawatan Elektronik & IT',
            'columns' => [
                ['Service Laptop', 'Service HP', 'Service TV', 'Service AC'],
                ['Network & Server', 'Instalasi CCTV', 'Jasa Backup Data'],
            ],
        ],
        [
            'label' => 'Otomotif',
            'columns' => [
                ['Sparepart Mobil', 'Aksesori Mobil', 'Ban & Velg', 'Oli & Fluida'],
                ['Sparepart Motor', 'Aksesori Motor', 'Helm & Riding Gear'],
            ],
        ],
        [
            'label' => 'Perawatan Tubuh',
            'columns' => [
                ['Perawatan Rambut', 'Perawatan Wajah', 'Perawatan Tubuh', 'Spa & Massage'],
                ['Alat Kesehatan Ringan', 'Vitamin & Suplemen'],
            ],
        ],
        [
            'label' => 'Jasa Ekspedisi & Pengepakan',
            'columns' => [
                ['Pengiriman Darat', 'Pengiriman Laut', 'Pengiriman Udara'],
                ['Pengepakan Kayu', 'Asuransi Barang', 'Trucking Lokal'],
            ],
        ],
        [
            'label' => 'Komputer & Laptop',
            'columns' => [
                ['Laptop Bisnis', 'Desktop', 'Monitor', 'Proyektor'],
                ['Keyboard & Mouse', 'Printer & Scanner', 'Software & Lisensi'],
            ],
        ],
        [
            'label' => 'Pertanian & Peternakan',
            'columns' => [
                ['Pupuk & Nutrisi', 'Alat Pertanian', 'Benih & Bibit'],
                ['Pakan Ternak', 'Obat & Vitamin Ternak', 'Kandang & Peralatan'],
            ],
        ],
    ];
    $defaultMegaMenuIndex = 2; // Olahraga default
  @endphp

  {{-- Main bar --}}
  <div class="bg-white">
    <div class="container relative mx-auto flex items-center gap-4 px-4 py-3" x-data="{
        categories: @js($megaMenuData),
        activeIndex: {{ $defaultMegaMenuIndex }},
        menuOpen: false,
        cartOpen: false,
        menuHoverTimeout: null,
        profileOpen: false,
        profileHoverTimeout: null,
        notificationsOpen: false,
        notificationsHoverTimeout: null,
        openMenu() {
            clearTimeout(this.menuHoverTimeout);
            this.menuOpen = true;
        },
        closeMenuWithDelay() {
            clearTimeout(this.menuHoverTimeout);
            this.menuHoverTimeout = setTimeout(() => {
                this.menuOpen = false;
            }, 120);
        },
        openProfile() {
            clearTimeout(this.profileHoverTimeout);
            this.profileOpen = true;
        },
        closeProfileWithDelay() {
            clearTimeout(this.profileHoverTimeout);
            this.profileHoverTimeout = setTimeout(() => {
                this.profileOpen = false;
            }, 120);
        },
        openNotifications() {
            clearTimeout(this.notificationsHoverTimeout);
            this.notificationsOpen = true;
        },
        closeNotificationsWithDelay() {
            clearTimeout(this.notificationsHoverTimeout);
            this.notificationsHoverTimeout = setTimeout(() => {
                this.notificationsOpen = false;
            }, 120);
        },
        notifications: [{
            date: '25 November 2025',
            title: 'Pesananmu Menunggu Pembayaran',
            description: 'Pesananmu berhasil dibuat, silahkan menyelesaikan pembayaran.',
        }, ],
        cartItems: [
            { name: 'Dove Body Wash Deeply Nourishing Botol 400ml', qty: 1, price: 'Rp86.800', img: 'https://via.placeholder.com/64' },
            { name: 'Cetak Flyer Custom ukuran A5 | 2 Sisi', qty: 1, price: 'Rp1.500', img: 'https://via.placeholder.com/64' },
        ],
    }">
      {{-- Logo --}}
      <div class="flex items-center gap-2">
        {{-- ganti src dengan logo kamu kalau ada --}}
        <div class="flex h-10 w-24 items-center justify-center rounded-md bg-sky-50 text-xs font-bold text-sky-600 p-2">
          TP-PKK Marketplace
        </div>
      </div>

      {{-- Kategori --}}
      <div class="relative" style="z-index: 10000;">
        <button
          class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          @mouseenter="openMenu()" @mouseleave="closeMenuWithDelay()" @focus="openMenu()">
          <span class="grid grid-cols-2 gap-0.5">
            @for ($i = 0; $i < 4; $i++)
              <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
            @endfor
          </span>
          <span>Kategori</span>
        </button>
      </div>

      {{-- Search --}}
      <div class="flex-1">
        <div class="flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2.5">
          <input class="w-full bg-transparent text-sm text-slate-700 placeholder:text-slate-400 outline-none"
            placeholder="Cari produk, jasa, atau vendor" />
          <button type="button" aria-label="Cari"
            class="ml-3 cursor-pointer text-slate-400 transition hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="7" />
              <path d="m16 16 4 4" />
            </svg>
          </button>
        </div>
      </div>

      {{-- Kanan: tergantung parameter ?auth=1 --}}
      @if (request()->boolean('auth'))
        {{-- MODE "SUDAH LOGIN" (dummy, berdasar parameter) --}}
        <div class="flex items-center gap-4">
          {{-- Bell --}}
          <div class="relative" @mouseenter="openNotifications()" @mouseleave="closeNotificationsWithDelay()"
            @focusin="openNotifications()" @focusout="closeNotificationsWithDelay()">
            <button class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600 focus:outline-none"
              type="button" aria-haspopup="true" :aria-expanded="notificationsOpen">

              <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M12.02 21.0299C9.68999 21.0299 7.35999 20.6599 5.14999 19.9199C4.30999 19.6299 3.66999 19.0399 3.38999 18.2699C3.09999 17.4999 3.19999 16.6499 3.65999 15.8899L4.80999 13.9799C5.04999 13.5799 5.26999 12.7799 5.26999 12.3099V9.41992C5.26999 5.69992 8.29999 2.66992 12.02 2.66992C15.74 2.66992 18.77 5.69992 18.77 9.41992V12.3099C18.77 12.7699 18.99 13.5799 19.23 13.9899L20.37 15.8899C20.8 16.6099 20.88 17.4799 20.59 18.2699C20.3 19.0599 19.67 19.6599 18.88 19.9199C16.68 20.6599 14.35 21.0299 12.02 21.0299ZM12.02 4.16992C9.12999 4.16992 6.76999 6.51992 6.76999 9.41992V12.3099C6.76999 13.0399 6.46999 14.1199 6.09999 14.7499L4.94999 16.6599C4.72999 17.0299 4.66999 17.4199 4.79999 17.7499C4.91999 18.0899 5.21999 18.3499 5.62999 18.4899C9.80999 19.8899 14.24 19.8899 18.42 18.4899C18.78 18.3699 19.06 18.0999 19.19 17.7399C19.32 17.3799 19.29 16.9899 19.09 16.6599L17.94 14.7499C17.56 14.0999 17.27 13.0299 17.27 12.2999V9.41992C17.27 6.51992 14.92 4.16992 12.02 4.16992Z"
                  fill="#686E76" />
                <path
                  d="M13.88 4.43993C13.81 4.43993 13.74 4.42993 13.67 4.40993C13.38 4.32993 13.1 4.26993 12.83 4.22993C11.98 4.11993 11.16 4.17993 10.39 4.40993C10.11 4.49993 9.80999 4.40993 9.61999 4.19993C9.42999 3.98993 9.36999 3.68993 9.47999 3.41993C9.88999 2.36993 10.89 1.67993 12.03 1.67993C13.17 1.67993 14.17 2.35993 14.58 3.41993C14.68 3.68993 14.63 3.98993 14.44 4.19993C14.29 4.35993 14.08 4.43993 13.88 4.43993Z"
                  fill="#686E76" />
                <path
                  d="M12.02 23.3101C11.03 23.3101 10.07 22.9101 9.36999 22.2101C8.66999 21.5101 8.26999 20.5501 8.26999 19.5601H9.76999C9.76999 20.1501 10.01 20.7301 10.43 21.1501C10.85 21.5701 11.43 21.8101 12.02 21.8101C13.26 21.8101 14.27 20.8001 14.27 19.5601H15.77C15.77 21.6301 14.09 23.3101 12.02 23.3101Z"
                  fill="#686E76" />
              </svg>
            </button>

            <div
              class="absolute right-0 top-full mt-2 w-[380px] max-w-[90vw] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70"
              x-show="notificationsOpen" x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 translate-y-1" @keydown.escape.window="notificationsOpen = false"
              x-cloak>
              <div class="px-5 pt-5 pb-3 border-b border-slate-100">
                <p class="text-xl font-semibold text-slate-900">Notifikasi</p>
              </div>
              <div class="divide-y divide-slate-100">
                <template x-for="(notif, nIdx) in notifications" :key="`notif-${nIdx}`">
                  <div class="px-5 py-3">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                      <div class="grid h-6 w-6 place-items-center rounded-full bg-cyan-100 text-cyan-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <rect x="3" y="5" width="18" height="14" rx="2" />
                          <path d="M3 10h18" />
                        </svg>
                      </div>
                      <span x-text="notif.date"></span>
                    </div>
                    <p class="mt-3 text-[15px] font-semibold text-slate-700" x-text="notif.title"></p>
                    <p class="mt-1 text-sm text-slate-600" x-text="notif.description"></p>
                  </div>
                </template>
              </div>
              <div class="px-5 py-3 bg-slate-50">
                <a href="#" class="text-sm font-semibold text-cyan-700 hover:text-cyan-800">Lihat Semua
                  Notifikasi</a>
              </div>
            </div>
          </div>

          {{-- Cart + badge --}}
          <div class="relative" @mouseenter="cartOpen = true" @mouseleave="cartOpen = false">
            <button class="relative rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
              <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
                xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M18.19 18.25H7.53999C6.54999 18.25 5.59999 17.83 4.92999 17.1C4.25999 16.37 3.92 15.39 4 14.4L4.83 4.44C4.86 4.13 4.74999 3.83001 4.53999 3.60001C4.32999 3.37001 4.04 3.25 3.73 3.25H2C1.59 3.25 1.25 2.91 1.25 2.5C1.25 2.09 1.59 1.75 2 1.75H3.74001C4.47001 1.75 5.15999 2.06 5.64999 2.59C5.91999 2.89 6.12 3.24 6.23 3.63H18.72C19.73 3.63 20.66 4.03 21.34 4.75C22.01 5.48 22.35 6.43 22.27 7.44L21.73 14.94C21.62 16.77 20.02 18.25 18.19 18.25ZM6.28 5.12L5.5 14.52C5.45 15.1 5.64 15.65 6.03 16.08C6.42 16.51 6.95999 16.74 7.53999 16.74H18.19C19.23 16.74 20.17 15.86 20.25 14.82L20.79 7.32001C20.83 6.73001 20.64 6.17001 20.25 5.76001C19.86 5.34001 19.32 5.10999 18.73 5.10999H6.28V5.12Z"
                  fill="#686E76" />
                <path
                  d="M16.25 23.25C15.15 23.25 14.25 22.35 14.25 21.25C14.25 20.15 15.15 19.25 16.25 19.25C17.35 19.25 18.25 20.15 18.25 21.25C18.25 22.35 17.35 23.25 16.25 23.25ZM16.25 20.75C15.97 20.75 15.75 20.97 15.75 21.25C15.75 21.53 15.97 21.75 16.25 21.75C16.53 21.75 16.75 21.53 16.75 21.25C16.75 20.97 16.53 20.75 16.25 20.75Z"
                  fill="#686E76" />
                <path
                  d="M8.25 23.25C7.15 23.25 6.25 22.35 6.25 21.25C6.25 20.15 7.15 19.25 8.25 19.25C9.35 19.25 10.25 20.15 10.25 21.25C10.25 22.35 9.35 23.25 8.25 23.25ZM8.25 20.75C7.97 20.75 7.75 20.97 7.75 21.25C7.75 21.53 7.97 21.75 8.25 21.75C8.53 21.75 8.75 21.53 8.75 21.25C8.75 20.97 8.53 20.75 8.25 20.75Z"
                  fill="#686E76" />
                <path
                  d="M21 9.25H9C8.59 9.25 8.25 8.91 8.25 8.5C8.25 8.09 8.59 7.75 9 7.75H21C21.41 7.75 21.75 8.09 21.75 8.5C21.75 8.91 21.41 9.25 21 9.25Z"
                  fill="#686E76" />
              </svg>

              <span
                class="absolute -right-0.5 -top-0.5 grid h-4 min-w-[1rem] place-items-center rounded-full bg-red-500 px-0.5 text-[10px] font-semibold text-white">
                2
              </span>
            </button>

            {{-- Cart dropdown --}}
            <div
              class="absolute right-0 top-full mt-2 w-[460px] max-w-[90vw] rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-200/60"
              x-show="cartOpen" x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-100"
              x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
              x-cloak>
              <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                <p class="text-xl font-semibold text-slate-900">Keranjang</p>
                <a href="#" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Selengkapnya</a>
              </div>
              <div class="divide-y divide-slate-100">
                <template x-for="(item, idx) in cartItems" :key="`cart-${idx}`">
                  <div class="flex gap-3 px-4 py-3">
                    <img class="h-12 w-12 flex-shrink-0 rounded-lg bg-slate-100 object-cover" :src="item.img"
                      alt="" />
                    <div class="flex-1">
                      <p class="text-sm font-semibold text-slate-900" x-text="item.name"></p>
                      <p class="text-xs text-slate-500" x-text="`${item.qty} Barang`"></p>
                    </div>
                    <div class="text-sm font-semibold text-slate-900" x-text="item.price"></div>
                  </div>
                </template>
              </div>
            </div>
          </div>

          {{-- Chat --}}
          <button class="rounded-full p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
            <svg width="24" height="25" viewBox="0 0 24 25" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M12 23.31C11.31 23.31 10.66 22.96 10.2 22.35L8.7 20.35C8.67 20.31 8.55 20.26 8.5 20.25H8C3.83 20.25 1.25 19.12 1.25 13.5V8.5C1.25 4.08 3.58 1.75 8 1.75H16C20.42 1.75 22.75 4.08 22.75 8.5V13.5C22.75 17.92 20.42 20.25 16 20.25H15.5C15.42 20.25 15.35 20.29 15.3 20.35L13.8 22.35C13.34 22.96 12.69 23.31 12 23.31ZM8 3.25C4.42 3.25 2.75 4.92 2.75 8.5V13.5C2.75 18.02 4.3 18.75 8 18.75H8.5C9.01 18.75 9.59 19.04 9.9 19.45L11.4 21.45C11.75 21.91 12.25 21.91 12.6 21.45L14.1 19.45C14.43 19.01 14.95 18.75 15.5 18.75H16C19.58 18.75 21.25 17.08 21.25 13.5V8.5C21.25 4.92 19.58 3.25 16 3.25H8Z"
                fill="#686E76" />
              <path
                d="M17 9.25H7C6.59 9.25 6.25 8.91 6.25 8.5C6.25 8.09 6.59 7.75 7 7.75H17C17.41 7.75 17.75 8.09 17.75 8.5C17.75 8.91 17.41 9.25 17 9.25Z"
                fill="#686E76" />
              <path
                d="M13 14.25H7C6.59 14.25 6.25 13.91 6.25 13.5C6.25 13.09 6.59 12.75 7 12.75H13C13.41 12.75 13.75 13.09 13.75 13.5C13.75 13.91 13.41 14.25 13 14.25Z"
                fill="#686E76" />
            </svg>

          </button>

          {{-- Profil user --}}
          <div class="relative" @mouseenter="openProfile()" @mouseleave="closeProfileWithDelay()"
            @focusin="openProfile()" @focusout="closeProfileWithDelay()">
            <button type="button"
              class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-1.5 text-left transition hover:bg-slate-100 focus:outline-none"
              aria-haspopup="true" :aria-expanded="profileOpen">
              <div class="grid h-8 w-8 place-items-center rounded-full bg-slate-300">
                <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <circle cx="12" cy="8" r="3" />
                  <path d="M6 20c0-3.3 2.7-6 6-6s6 2.7 6 6" />
                </svg>
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-slate-800">Hesdin Mukhsin</p>
                <p class="text-[11px] text-slate-500">Buyer Retail</p>
              </div>
            </button>

            <div
              class="absolute right-0 top-full mt-2 w-[360px] max-w-[90vw] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl shadow-slate-200/70"
              x-show="profileOpen" x-transition:enter="transition ease-out duration-150"
              x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-100"
              x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
              @keydown.escape.window="profileOpen = false" x-cloak>
              <div class="flex items-start gap-3 px-5 pt-5 pb-4">
                <div class="grid h-12 w-12 place-items-center rounded-full bg-slate-200">
                  <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M5 21c0-4 3-7 7-7s7 3 7 7" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="text-base font-semibold text-slate-900">Hesdin Mukhsin</p>
                      <p class="text-sm text-slate-500">Buyer Retail</p>
                    </div>
                    <a href="#"
                      class="inline-flex items-center rounded-lg bg-cyan-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                      Lihat Profile
                    </a>
                  </div>
                </div>
              </div>

              <div class="px-5 pb-4">
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                  <div class="grid h-12 w-12 place-items-center rounded-lg bg-cyan-100 text-cyan-700">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 8a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v9a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3Z" />
                      <path d="M4 10h16" />
                      <path d="M11 14h5" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-1 text-sm font-semibold text-slate-700">
                      <span>Saldo Refund</span>
                      <span class="text-slate-400">?</span>
                    </div>
                    <p class="text-lg font-bold text-cyan-700">Rp0</p>
                  </div>
                  <button type="button"
                    class="rounded-md bg-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-500 shadow-sm transition hover:bg-slate-300">
                    Tarik
                  </button>
                </div>
              </div>

              <div class="divide-y divide-slate-100">
                <a href="#"
                  class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                  <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <rect x="3" y="6" width="18" height="12" rx="2" />
                    <path d="M3 10h18" />
                  </svg>
                  <span class="text-sm font-semibold">Pembayaran</span>
                </a>
                <a href="#"
                  class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                  <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M8 6h13" />
                    <path d="M8 12h13" />
                    <path d="M8 18h13" />
                    <path d="M3 6h.01" />
                    <path d="M3 12h.01" />
                    <path d="M3 18h.01" />
                  </svg>
                  <span class="text-sm font-semibold">Daftar Transaksi</span>
                </a>
                <a href="#"
                  class="flex items-center gap-3 px-5 py-3 text-slate-800 transition hover:bg-slate-50">
                  <svg class="h-6 w-6 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="m3 9 9-7 9 7" />
                    <path d="M9 22V12h6v10" />
                  </svg>
                  <span class="text-sm font-semibold">Alamat Pengiriman</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-5 py-3 text-red-600 transition hover:bg-red-50">
                  <svg class="h-6 w-6 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <path d="M16 17l5-5-5-5" />
                    <path d="M21 12H9" />
                  </svg>
                  <span class="text-sm font-semibold">Keluar</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      @else
        {{-- MODE BELUM LOGIN --}}
        <div class="flex items-center gap-3">
          <a href="{{ route('portal.login') }}"
            class="rounded-lg border border-sky-500 bg-white px-5 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
            Masuk
          </a>
          <a href="{{ route('register.as') }}"
            class="rounded-lg bg-sky-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-600">
            Daftar
          </a>
        </div>
      @endif

      {{-- Mega menu anchored to container width --}}
      <div class="absolute left-0 right-0 top-full pt-3" x-show="menuOpen"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
        @mouseenter="openMenu()" @mouseleave="closeMenuWithDelay()" @keydown.escape.window="menuOpen = false" x-cloak
        style="z-index: 10001;">
        <div
          class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60"
          style="--tw-shadow: 0 25px 60px -30px rgba(15, 23, 42, 0.25);">
          <div class="absolute inset-0 bg-white"></div>
          <div class="relative grid w-full grid-cols-12">
            <div class="col-span-3 border-r border-slate-100 px-5 py-6">
              <div class="flex items-center justify-between text-[13px] font-semibold text-slate-600">
                <span>Kategori Produk & Jasa</span>
                <a href="#" class="text-sky-600 hover:text-sky-700">Lihat Semua</a>
              </div>
              <div class="mt-4 space-y-1.5 text-sm">
                @foreach ($megaMenuData as $index => $category)
                  <a href="#" class="flex items-center justify-between rounded-lg px-3 py-2 transition"
                    x-on:mouseenter="activeIndex = {{ $index }}"
                    :class="activeIndex === {{ $index }} ? 'bg-sky-50 font-semibold text-sky-700' :
                        'text-slate-700 hover:bg-slate-50 hover:text-slate-900'">
                    <span>{{ $category['label'] }}</span>
                    <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" x-show="activeIndex === {{ $index }}">
                      <path d="m9 18 6-6-6-6" />
                    </svg>
                  </a>
                @endforeach
              </div>
            </div>

            <div class="col-span-9 px-6 py-6" x-show="categories[activeIndex]" x-cloak>
              <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-slate-900"
                  x-text="categories[activeIndex]?.label || 'Kategori'">
                </p>
                <a href="#" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Semua</a>
              </div>
              <div class="mt-4 grid grid-cols-4 gap-x-8 gap-y-3 text-sm text-slate-700">
                <template x-for="(column, cIdx) in categories[activeIndex]?.columns || []" :key="`col-${cIdx}`">
                  <div class="space-y-2">
                    <template x-for="(item, iIdx) in column" :key="`item-${cIdx}-${iIdx}`">
                      <a href="#" class="block transition hover:text-sky-700" x-text="item"></a>
                    </template>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>
