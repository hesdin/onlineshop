<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Login - PKK UMKM')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-sky-600 font-sans text-slate-900">
  <section class="relative flex min-h-screen items-center justify-center overflow-hidden bg-sky-600 text-white">
    {{-- dekorasi background tipis --}}
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-gradient-to-b from-sky-500/40 via-sky-500/10 to-sky-700/50"></div>

      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-[32px] border border-white/10"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-[32px] border border-white/10"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-500/30 blur-3xl"></span>
    </div>

    <div
      class="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-10 px-6 py-12 lg:flex-row lg:items-stretch lg:gap-16 lg:py-16">
      {{-- KIRI: ilustrasi + teks --}}
      <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
        <div class="max-w-md">
          <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="Login PaDi UMKM"
            class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px]" />

          <h2 class="mt-10 text-2xl sm:text-3xl font-bold">
            Bersama PaDi UMKM
          </h2>
          <p class="mt-3 text-sm sm:text-base text-white/90">
            Mari tingkatkan pertumbuhan ekonomi UMKM untuk Indonesia yang lebih maju.
          </p>

          <div class="mt-6 flex items-center justify-center gap-2 lg:justify-start">
            <span class="inline-flex h-2 w-6 rounded-full bg-white"></span>
            <span class="inline-flex h-2 w-2 rounded-full bg-white/50"></span>
            <span class="inline-flex h-2 w-2 rounded-full bg-white/50"></span>
            <span class="inline-flex h-2 w-2 rounded-full bg-white/50"></span>
          </div>
        </div>
      </div>

      {{-- tombol panah di tengah (desktop) --}}
      <button type="button"
        class="pointer-events-auto absolute left-1/2 top-1/2 hidden h-10 w-10 -translate-x-1/2 -translate-y-1/2 transform items-center justify-center rounded-full bg-white text-sky-500 shadow-lg lg:inline-flex">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10 7l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      {{-- KANAN: kartu login (rounded lebih kecil) --}}
      <div class="relative flex-1">
        <div class="relative z-10 ml-auto w-full max-w-lg rounded-xl bg-white p-8 sm:p-10 text-slate-900 shadow-2xl">
          <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <button type="button" class="rounded-full p-1 text-slate-400 hover:bg-slate-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
              <h3 class="text-2xl sm:text-3xl font-bold text-slate-900">Login Pembeli</h3>
            </div>
            <div
              class="flex h-10 w-28 items-center justify-center rounded-xl border border-slate-200 text-[11px] font-bold text-sky-600">
              PaDi UMKM
            </div>
          </div>

          <form class="mt-8 space-y-5">
            <div class="space-y-1.5">
              <label class="text-sm font-semibold text-slate-600">Email</label>
              <input type="email"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="padi@email.com" />
            </div>

            <div class="space-y-1.5">
              <label class="text-sm font-semibold text-slate-600">Kata Sandi</label>
              <div
                class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 focus-within:border-sky-400 focus-within:bg-white">
                <input type="password"
                  class="w-full bg-transparent py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  placeholder="Masukan Kata Sandi" />
                <button type="button" class="p-1 text-slate-400 hover:text-slate-600">
                  <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M1 12s3.5-7 11-7 11 7 11 7-3.5 7-11 7S1 12 1 12Z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                </button>
              </div>
            </div>

            <button type="submit"
              class="mt-4 flex w-full items-center justify-center rounded-xl bg-slate-100 py-3 text-sm sm:text-base font-semibold text-slate-400">
              Login
            </button>

            <p class="mt-2 text-center text-xs sm:text-sm text-slate-500">
              Lupa Kata Sandi?
              <a href="#" class="font-semibold text-sky-600 hover:underline">Atur Ulang Kata Sandi</a>
            </p>

            <div class="mt-4 flex items-center gap-3 text-xs text-slate-400">
              <div class="h-px flex-1 bg-slate-200"></div>
              <span>Atau</span>
              <div class="h-px flex-1 bg-slate-200"></div>
            </div>

            <p class="text-center text-xs sm:text-sm text-slate-500">
              Belum punya akun?
              <a href="#" class="font-semibold text-sky-600 hover:underline">Daftar Sekarang</a>
            </p>
          </form>
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
</body>

</html>
