<section class="">
  <div class="grid gap-4 lg:grid-cols-3 lg:items-stretch">
    @php
      $heroBanners = [
          [
              'image' => asset('assets/img/hero/hero-1.png'),
              'url' => '#internet-bisnis',
              'label' => 'Promo internet bisnis',
          ],
          [
              'image' => asset('assets/img/hero/hero-2.png'),
              'url' => '#hibah-digital',
              'label' => 'Hibah digitalisasi UMKM',
          ],
          [
              'image' => asset('assets/img/hero/hero-3.jpg'),
              'url' => '#paket-hampers',
              'label' => 'Paket hampers akhir tahun',
          ],
      ];
    @endphp

    <article class="lg:col-span-2" x-data="{
        banners: @js($heroBanners),
        active: 0,
        interval: null,
        start() {
            this.interval = setInterval(() => this.next(), 4500);
        },
        stop() {
            if (this.interval) clearInterval(this.interval);
        },
        restart() {
            this.stop();
            this.start();
        },
        next() {
            this.active = (this.active + 1) % this.banners.length;
        },
        prev() {
            this.active = (this.active + this.banners.length - 1) % this.banners.length;
        }
    }" x-init="start()" @mouseenter="stop()"
      @mouseleave="start()">
      <div class="relative h-full overflow-hidden rounded-lg bg-slate-50" style="aspect-ratio: 1440 / 620;">
        <template x-for="(banner, index) in banners" :key="index">
          <a x-show="active === index" x-cloak class="block h-full w-full" :href="banner.url" target="_blank"
            rel="noopener">
            <img :src="banner.image" :alt="banner.label"
              class="h-full w-full object-cover transition duration-500"
              x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-105"
              x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-300"
              x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" />
          </a>
        </template>

        <div class="pointer-events-none absolute inset-0 flex items-center justify-between px-4">
          <button class="pointer-events-auto rounded-full bg-white/80 p-2 text-slate-700 shadow"
            @click="prev(); restart();">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="m15 18-6-6 6-6" />
            </svg>
          </button>
          <button class="pointer-events-auto rounded-full bg-white/80 p-2 text-slate-700 shadow"
            @click="next(); restart();">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="m9 6 6 6-6 6" />
            </svg>
          </button>
        </div>

        <div class="absolute bottom-4 left-4 flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 shadow">
          <span class="h-[6px] w-10 rounded-full bg-slate-200"></span>
          <template x-for="(banner, index) in banners" :key="`dot-${index}`">
            <button class="h-2 w-2 rounded-full bg-slate-200 transition hover:bg-sky-500"
              :class="active === index ? 'scale-100 bg-sky-500' : 'opacity-80'" @click="active = index; restart();"
              :aria-label="`Slide ${index + 1}`"></button>
          </template>
        </div>
      </div>
    </article>

    <article class="flex h-full flex-col gap-4">
      <a href="#promo-rumah-tangga" class="group flex-1 overflow-hidden rounded-lg ring-1 ring-slate-100">
        <img src="{{ asset('assets/img/hero/hero-right-1.jpg') }}" alt="Gratis ongkir Jakarta dan diskon Sinar Bersih"
          class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" />
      </a>
      <a href="#promo-kuliner" class="group flex-1 overflow-hidden rounded-lg ring-1 ring-slate-100">
        <img src="{{ asset('assets/img/hero/hero-right-2.png') }}" alt="Proses kilat dapur solo lunch box"
          class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" />
      </a>
    </article>
  </div>

  <div class="flex items-center justify-end text-md font-bold text-sky-600 mt-2">
    <a class="inline-flex items-center gap-2 hover:text-sky-700" href="#promo-terkini">
      Lihat semua promo
    </a>
  </div>
</section>
