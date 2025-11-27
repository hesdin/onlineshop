<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Daftar Sebagai - PKK UMKM')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans text-slate-900">
  <section class="relative flex min-h-screen items-center justify-center px-4 py-16">
    {{-- KARTU UTAMA --}}
    <div class="relative mx-auto flex w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl">

      {{-- KIRI: DAFTAR SEBAGAI --}}
      <div class="flex w-1/2 flex-col justify-between border-r border-slate-100 px-8 py-14">
        <div>
          <div class="flex items-start justify-between gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
              Daftar Sebagai
            </h1>

            {{-- logo kecil (bisa diganti logo asli) --}}
            <div class="flex flex-col items-end leading-none text-right">
              <span class="text-[10px] font-semibold tracking-wide text-sky-500">PaDi</span>
              <span class="text-xs font-bold text-sky-700">UMKM</span>
            </div>
          </div>

          <div class="mt-8 space-y-4">
            {{-- Pembeli Retail --}}
            <a href="{{ route('register', ['role' => 'retail']) }}"
              class="flex items-center gap-4 rounded-2xl bg-slate-50 px-5 py-4 transition hover:bg-slate-100">
              <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-teal-100 text-teal-600">
                {{-- icon keranjang --}}
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M3 8h18l-1.5 10H4.5L3 8Z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M9 11.5v2.5M15 11.5v2.5" stroke-linecap="round" />
                </svg>
              </span>
              <div class="space-y-1">
                <p class="text-base font-semibold text-slate-900">Pembeli Retail</p>
                <p class="text-sm leading-snug text-slate-600">
                  Dapatkan diskon dan promo menarik setiap hari
                </p>
              </div>
            </a>

            {{-- Penjual --}}
            <a href="{{ route('register', ['role' => 'seller']) }}"
              class="flex items-center gap-4 rounded-2xl bg-slate-50 px-5 py-4 transition hover:bg-slate-100">
              <span class="flex h-14 w-14 flex-none items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                {{-- icon toko --}}
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                  <path d="M4 10V7l2-3h12l2 3v3" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M5 10h14v8H5z" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M10 14h4" stroke-linecap="round" />
                </svg>
              </span>
              <div class="space-y-1">
                <p class="text-base font-semibold text-slate-900">Penjual</p>
                <p class="text-sm leading-snug text-slate-600">
                  Jual produk secara efisien ke BUMN maupun retail
                </p>
              </div>
            </a>
          </div>
        </div>

        <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs sm:text-sm text-slate-500">
          Sudah punya akun TP-PKK Marketplace?
          <a href="{{ route('login') }}" class="font-semibold text-sky-600 hover:underline">Masuk</a>
        </div>
      </div>

      {{-- KANAN: ILLUSTRASI + TEKS --}}
      <div class="flex w-1/2 flex-col bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 px-8 py-14">
        <div class="flex flex-1 flex-col items-center justify-center">
          <img src="https://padiumkm.id/_next/static/media/register-as.cf421d13.svg" alt="Daftar TP-PKK Marketplace"
            class="w-56 sm:w-64 lg:w-72" />
        </div>

        <div class="mt-6 text-center text-white">
          <h2 class="text-xl sm:text-2xl font-bold">
            Bersama TP-PKK Marketplace
          </h2>
          <p class="mt-3 text-sm leading-relaxed text-white/90">
            Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
          </p>
        </div>
      </div>
    </div>

    {{-- Customer Care di kanan bawah --}}
    <div class="pointer-events-none absolute bottom-6 right-6">
      <button
        class="pointer-events-auto inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-semibold text-sky-600 shadow-lg">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
          stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M18 11.5a6 6 0 1 0-7 5.91V21l2.9-1.45 2.9 1.45v-3.59A6 6 0 0 0 18 11.5Z" />
        </svg>
        Customer Care
      </button>
    </div>
  </section>
</body>

</html>
