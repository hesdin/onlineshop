<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Daftar - PKK UMKM')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 font-sans text-slate-900">
  <section class="relative flex min-h-screen items-center justify-center px-4 py-16">
    <!-- Kartu utama -->
    <div class="relative mx-auto flex w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl">

      <!-- KIRI: Form daftar -->
      <div class="flex w-1/2 flex-col justify-between border-r border-slate-100 px-8 py-14">
        <div>
          <div class="mb-8 flex items-start justify-between gap-4">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
              Daftar
            </h1>

            <!-- logo mini (bisa diganti logo asli) -->
            <div class="flex flex-col items-end leading-none text-right">
              <span class="text-xs font-semibold tracking-wide text-sky-500">PaDi</span>
              <span class="text-xs font-bold text-sky-700">UMKM</span>
            </div>
          </div>

          <form action="#" method="POST" class="space-y-4 text-sm">
            @csrf

            <!-- Nama -->
            <div class="space-y-1">
              <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Nama</label>
              <input type="text" name="name"
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="John" />
            </div>

            <!-- Email -->
            <div class="space-y-1">
              <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Alamat Email</label>
              <input type="email" name="email"
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="john@email.com" />
            </div>

            <!-- Telepon -->
            <div class="space-y-1">
              <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Telepon</label>
              <div
                class="flex items-center rounded-lg border border-slate-200 bg-slate-50 text-sm text-slate-800 focus-within:border-sky-400 focus-within:bg-white">
                <span class="flex h-10 items-center border-r border-slate-200 px-3 text-slate-600">+62</span>
                <input type="tel" name="phone"
                  class="w-full bg-transparent px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none"
                  placeholder="8123456789" />
              </div>
            </div>

            <!-- Kode Referal -->
            <div class="space-y-1">
              <label class="block text-xs font-semibold uppercase tracking-wide text-slate-600">
                Kode Referal <span class="normal-case font-normal text-slate-400">(Tidak Wajib)</span>
              </label>
              <input type="text" name="referral"
                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-sky-400 focus:bg-white focus:outline-none"
                placeholder="Masukkan kode referal" />
            </div>

            <!-- Checkbox Terms -->
            <div class="pt-2">
              <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox"
                  class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                <span>
                  Saya sudah membaca dan menyetujui
                  <a href="#" class="font-semibold text-sky-600 hover:underline">Syarat dan Ketentuan</a>
                  serta
                  <a href="#" class="font-semibold text-sky-600 hover:underline">Kebijakan Privasi</a>
                  yang berlaku
                </span>
              </label>
            </div>

            <!-- reCAPTCHA placeholder -->
            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 text-xs text-slate-600">
                  <div class="flex h-5 w-5 items-center justify-center rounded border border-slate-300 bg-white"></div>
                  <span>I'm not a robot</span>
                </div>
                <div class="h-6 w-20 rounded bg-slate-200"></div>
              </div>
            </div>

            <!-- Tombol Daftar -->
            <button type="submit"
              class="mt-4 w-full rounded-lg bg-slate-100 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed">
              Daftar
            </button>
          </form>
        </div>

        <!-- footer kecil -->
        <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs sm:text-sm text-slate-500">
          Sudah punya akun PaDi UMKM?
          <a href="{{ route('login') }}" class="font-semibold text-sky-600 hover:underline">Masuk</a>
        </div>
      </div>

      <!-- KANAN: Panel info & ilustrasi -->
      <div class="flex w-1/2 flex-col bg-gradient-to-br from-sky-400 via-sky-500 to-sky-600 px-10 py-14 text-white">
        <div class="max-w-sm">
          <h2 class="text-2xl font-bold">
            Belanja Efisien Kemana Saja
          </h2>
          <p class="mt-4 text-sm leading-relaxed text-white/90">
            Dengan berbagai kemudahan berbelanja di PaDi UMKM, proses transaksi pembelanjaan menjadi cepat dan efisien
            tanpa harus melewati proses yang merepotkan. Barang akan sampai kemana pun yang Anda inginkan dengan aman.
          </p>
        </div>

        <div class="mt-10 flex flex-1 items-center justify-center">
          <img src="https://padiumkm.id/_next/static/media/login-card.5fbc3bf9.svg" alt="Belanja di PaDi UMKM"
            class="w-56 sm:w-64 lg:w-72" />
        </div>
      </div>
    </div>

    <!-- Customer Care -->
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
