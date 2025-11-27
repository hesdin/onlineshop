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
      <div class="absolute inset-0 bg-linear-to-b from-sky-500/40 via-sky-500/10 to-sky-700/50"></div>

      <span class="absolute -left-24 bottom-10 h-40 w-40 rotate-6 rounded-4xl border border-white/10"></span>
      <span class="absolute -right-10 top-10 h-32 w-32 rounded-4xl border border-white/10"></span>
      <span class="absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-sky-500/30 blur-3xl"></span>
    </div>

    <div
      class="relative mx-auto flex w-full max-w-6xl flex-col items-center gap-10 px-6 py-12 lg:flex-row lg:items-stretch lg:gap-16 lg:py-16">
      {{-- KIRI: ilustrasi + teks --}}
      <div class="flex flex-1 flex-col items-center justify-center text-center lg:items-start lg:text-left">
        <div class="max-w-md">
          <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="Login TP-PKK Marketplace"
            class="mx-auto w-[230px] sm:w-[290px] lg:w-[320px]" />

          <h2 class="mt-10 text-2xl sm:text-3xl font-bold">
            Bersama TP-PKK Marketplace
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
        <div class="relative z-10 ml-auto w-full max-w-lg rounded-lg bg-white p-8 sm:p-10 text-slate-900 shadow-2xl">
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
              class="flex h-10 w-28 items-center justify-center rounded-lg border border-slate-200 text-[11px] font-bold text-sky-600 p-3">
              TP-PKK Marketplace
            </div>
          </div>

          <form class="mt-8 space-y-5">
            <div class="space-y-1.5">
              <label class="text-sm font-semibold text-slate-600">Email</label>
              <input type="email"
                class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-3 text-sm sm:text-base text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="padi@email.com" />
            </div>

            <div class="space-y-1.5">
              <label class="text-sm font-semibold text-slate-600">Kata Sandi</label>
              <div
                class="flex items-center rounded-md border border-slate-200 bg-slate-50 px-3 focus-within:border-sky-400 focus-within:bg-white">
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
              class="mt-4 flex w-full items-center justify-center rounded-md bg-slate-100 py-3 text-sm sm:text-base font-semibold text-slate-400">
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
        <svg width="22" height="19" viewBox="0 0 22 19" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M10.1328 0.489063C7.89219 0.793751 5.96563 2.0875 4.86406 4.02344C4.56406 4.54844 4.29219 5.24219 4.15156 5.83281L4.04375 6.28281L3.85625 6.26406C3.65469 6.24063 2.34219 6.51719 1.98594 6.65781C1.56875 6.82188 1.11875 7.22031 0.926563 7.60469C0.729688 7.98438 0.6875 8.27969 0.6875 9.25C0.6875 10.2203 0.729688 10.5156 0.926563 10.8953C1.11875 11.2797 1.56875 11.6781 1.99063 11.8422C2.24375 11.9406 3.58906 12.25 3.76719 12.25C4.19375 12.25 4.70938 11.9266 4.92031 11.5281C5.02344 11.3406 5.02344 11.2891 5.05156 9.08594C5.07969 6.63906 5.09844 6.42344 5.36563 5.64063C5.57656 5.03594 5.72656 4.72656 6.05 4.23438C6.575 3.45156 7.21719 2.84219 8.00938 2.37813C9.50938 1.49688 11.3797 1.32344 13.0156 1.91406C14.7172 2.52813 16.0906 3.95313 16.6391 5.66406C16.9156 6.53594 16.9531 6.94375 16.9531 9.29688C16.9531 11.2375 16.9531 11.2422 17.0656 11.4766C17.2625 11.9078 17.7828 12.25 18.2328 12.25C18.3969 12.25 19.7047 11.9547 19.9906 11.8563C20.3938 11.7109 20.8766 11.2844 21.0734 10.8953C21.2703 10.5156 21.3125 10.2203 21.3125 9.25C21.3125 8.27969 21.2703 7.98438 21.0734 7.60469C20.8766 7.21563 20.3938 6.78906 19.9906 6.64375C19.5781 6.49844 18.3359 6.24063 18.1438 6.26406L17.9563 6.28281L17.8484 5.82344C17.7875 5.57031 17.6516 5.14375 17.5438 4.87656C16.6531 2.65469 14.7172 1.04688 12.3641 0.573439C11.8344 0.465626 10.625 0.418751 10.1328 0.489063Z"
            fill="#0084D1" />
          <path opacity="0.4"
            d="M10.2733 3.48433C9.54202 3.60151 8.75921 3.91089 8.12171 4.33745C7.7514 4.58589 7.08577 5.2562 6.83265 5.62651C5.65609 7.37964 5.64671 9.5312 6.8139 11.2984C7.07171 11.6921 7.71859 12.3437 8.12171 12.625C8.59046 12.9484 9.26077 13.2578 9.8139 13.4125L10.2733 13.5343L12.617 13.5484L14.9608 13.5671L15.1717 13.4593C15.4858 13.2953 15.6076 13.089 15.5842 12.7375C15.5655 12.4093 15.4858 12.2828 15.1155 12.0062C14.9795 11.9031 14.8717 11.8 14.8764 11.7765C14.8858 11.7531 14.9889 11.6031 15.1061 11.4437C16.3108 9.80776 16.367 7.48276 15.2467 5.75776C14.5576 4.69839 13.4608 3.91089 12.242 3.59683C11.717 3.46089 10.7748 3.40933 10.2733 3.48433ZM9.51859 8.12026C9.64046 8.24683 9.6639 8.30308 9.6639 8.49995C9.6639 8.70151 9.64046 8.75308 9.50921 8.88433C9.37796 9.01558 9.3264 9.03901 9.12484 9.03901C8.92327 9.03901 8.87171 9.01558 8.74046 8.88433C8.60921 8.75308 8.58577 8.70151 8.58577 8.50464C8.58577 8.26089 8.70296 8.07339 8.9139 7.97964C9.08734 7.90464 9.3639 7.96558 9.51859 8.12026ZM11.3936 8.12026C11.5155 8.24683 11.5389 8.30308 11.5389 8.49995C11.5389 8.70151 11.5155 8.75308 11.3842 8.88433C11.253 9.01558 11.2014 9.03901 10.9998 9.03901C10.7983 9.03901 10.7467 9.01558 10.6155 8.88433C10.4842 8.75308 10.4608 8.70151 10.4608 8.50464C10.4608 8.26089 10.578 8.07339 10.7889 7.97964C10.9623 7.90464 11.2389 7.96558 11.3936 8.12026ZM13.2686 8.12026C13.3905 8.24683 13.4139 8.30308 13.4139 8.49995C13.4139 8.70151 13.3905 8.75308 13.2592 8.88433C13.128 9.01558 13.0764 9.03901 12.8748 9.03901C12.6733 9.03901 12.6217 9.01558 12.4905 8.88433C12.3592 8.75308 12.3358 8.70151 12.3358 8.50464C12.3358 8.26089 12.453 8.07339 12.6639 7.97964C12.8373 7.90464 13.1139 7.96558 13.2686 8.12026Z"
            fill="#0084D1" />
          <path opacity="0.4"
            d="M4.78901 13.2296C4.62964 13.2999 4.48433 13.4921 4.4562 13.6655C4.41401 13.9093 4.5312 14.1155 5.00933 14.6171C6.11558 15.789 7.54995 16.4733 9.2187 16.6327L9.52808 16.6608L9.52339 17.0921C9.52339 17.4671 9.54214 17.5608 9.65464 17.8093C10.2687 19.1311 12.1015 19.1265 12.7203 17.8046C12.8328 17.5655 12.8515 17.4624 12.8515 17.1249C12.8515 16.7874 12.8328 16.6843 12.7203 16.4452C12.5468 16.0796 12.2328 15.7561 11.8718 15.5968C11.5484 15.4468 11.3328 15.4374 10.1281 15.5077C9.34058 15.5499 8.7687 15.4749 7.99995 15.2218C7.07183 14.9124 6.4437 14.4999 5.71245 13.7171C5.30464 13.2811 5.23433 13.2249 5.07026 13.2061C4.96714 13.1968 4.84058 13.2061 4.78901 13.2296Z"
            fill="#0084D1" />
        </svg>
        Customer Care
      </button>
    </div>
  </section>
</body>

</html>
