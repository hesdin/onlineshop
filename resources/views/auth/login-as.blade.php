<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Pilih Login - PKK UMKM')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-sky-600 font-sans text-slate-900">
  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-sky-600 text-white">
    {{-- dekorasi background --}}
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-gradient-to-b from-sky-500/40 via-sky-500/10 to-sky-700/50"></div>

      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-[32px] border border-white/10"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-[32px] border border-white/10"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-500/30 blur-3xl"></span>
    </div>

    {{-- KONTEN UTAMA --}}
    <div
      class="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-10 px-6 py-12 lg:flex-row lg:items-stretch lg:gap-16 lg:py-16">

      {{-- KIRI: ilustrasi + teks (bisa dijadikan slide) --}}
      <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
        <div id="heroSlider" class="max-w-md">
          {{-- SLIDE 1 --}}
          <div data-slide="0" class="space-y-6">
            <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="Login PaDi UMKM"
              class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px]" />

            <div>
              <h2 class="text-2xl sm:text-3xl font-bold">
                Bersama PaDi UMKM
              </h2>
              <p class="mt-3 text-sm sm:text-base text-white/90">
                Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
              </p>
            </div>
          </div>

          {{-- SLIDE 2 (opsional, bisa dihapus kalau tidak perlu) --}}
          <div data-slide="1" class="hidden space-y-6">
            <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="UMKM Berkembang"
              class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px]" />

            <div>
              <h2 class="text-2xl sm:text-3xl font-bold">
                UMKM Berkembang Pesat
              </h2>
              <p class="mt-3 text-sm sm:text-base text-white/90">
                Akses pengadaan barang dan jasa dengan lebih mudah, aman, dan transparan.
              </p>
            </div>
          </div>

          {{-- SLIDE 3 (opsional) --}}
          <div data-slide="2" class="hidden space-y-6">
            <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="Digitalisasi"
              class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px]" />

            <div>
              <h2 class="text-2xl sm:text-3xl font-bold">
                Digitalisasi Pengadaan
              </h2>
              <p class="mt-3 text-sm sm:text-base text-white/90">
                Satu platform untuk menghubungkan UMKM dengan BUMN dan pembeli korporat.
              </p>
            </div>
          </div>

          {{-- DOTS --}}
          <div class="mt-6 flex items-center justify-center gap-2 lg:justify-start">
            <button data-dot="0" class="h-2 w-6 rounded-full bg-white transition-all"></button>
            <button data-dot="1" class="h-2 w-2 rounded-full bg-white/50 transition-all"></button>
            <button data-dot="2" class="h-2 w-2 rounded-full bg-white/50 transition-all"></button>
          </div>
        </div>
      </div>

      {{-- TOMBOL PANAH (next slide) --}}
      <button type="button" id="sliderNext"
        class="pointer-events-auto absolute left-1/2 top-1/2 hidden h-10 w-10 -translate-x-1/2 -translate-y-1/2 transform items-center justify-center rounded-full bg-white text-sky-500 shadow-lg lg:inline-flex">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      {{-- KANAN: KARTU PILIH LOGIN --}}
      <div class="relative flex-1">
        <div class="relative z-10 ml-auto w-full max-w-xl rounded-xl bg-white p-8 sm:p-10 text-slate-900 shadow-2xl">
          <div class="mb-8 flex items-center justify-between gap-6">
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-900">
              Login Sebagai
            </h3>
            <div
              class="flex h-10 min-w-[7rem] items-center justify-center rounded-xl border border-slate-200 text-[11px] font-bold text-sky-600">
              PaDi UMKM
            </div>
          </div>

          <div class="space-y-4">
            {{-- Penjual --}}
            <a href="{{ route('login', ['role' => 'seller']) }}"
              class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 p-5 transition hover:border-sky-300 hover:bg-white hover:shadow-md">
              <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                  {{-- icon placeholder --}}
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M3 7h18M5 7v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7M9 7V5a3 3 0 0 1 6 0v2"
                      stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </span>
                <div>
                  <p class="text-base font-semibold text-slate-900">Penjual</p>
                  <p class="text-sm text-slate-500">
                    Jual produk secara efisien ke BUMN maupun retail
                  </p>
                </div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>

            {{-- Pembeli --}}
            <a href="{{ route('login', ['role' => 'buyer']) }}"
              class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/80 p-5 transition hover:border-sky-300 hover:bg-white hover:shadow-md">
              <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                  {{-- icon placeholder --}}
                  <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M3 5h2l1.68 8.39A2 2 0 0 0 8.64 15h8.05a2 2 0 0 0 1.92-1.51L20 7H6" stroke-linecap="round"
                      stroke-linejoin="round" />
                    <circle cx="9" cy="19" r="1.5" />
                    <circle cx="17" cy="19" r="1.5" />
                  </svg>
                </span>
                <div>
                  <p class="text-base font-semibold text-slate-900">Pembeli</p>
                  <p class="text-sm text-slate-500">
                    Login pembeli retail dan B2B
                  </p>
                </div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Customer care di kanan bawah --}}
    <div class="pointer-events-none absolute bottom-6 right-6">
      <button
        class="pointer-events-auto inline-flex items-center gap-2 rounded-full bg-white/90 px-5 py-2 text-sm font-semibold text-sky-600 shadow-lg">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
          stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M18 11.5a6 6 0 1 0-7 5.91V21l2.9-1.45 2.9 1.45v-3.59A6 6 0 0 0 18 11.5Z" />
        </svg>
        Customer Care
      </button>
    </div>
  </section>

  {{-- SCRIPT SLIDER SEDERHANA --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const slides = Array.from(document.querySelectorAll('[data-slide]'));
      const dots = Array.from(document.querySelectorAll('[data-dot]'));
      const nextBtn = document.getElementById('sliderNext');

      let current = 0;

      function showSlide(index) {
        slides.forEach((slide, i) => {
          if (i === index) slide.classList.remove('hidden');
          else slide.classList.add('hidden');
        });

        dots.forEach((dot, i) => {
          if (i === index) {
            dot.classList.remove('w-2', 'bg-white/50');
            dot.classList.add('w-6', 'bg-white');
          } else {
            dot.classList.remove('w-6', 'bg-white');
            dot.classList.add('w-2', 'bg-white/50');
          }
        });

        current = index;
      }

      function nextSlide() {
        const next = (current + 1) % slides.length;
        showSlide(next);
      }

      if (nextBtn) nextBtn.addEventListener('click', nextSlide);
      dots.forEach((dot, i) => dot.addEventListener('click', () => showSlide(i)));

      showSlide(0);
    });
  </script>
</body>

</html>
