<header class="sticky top-0 z-50 border-b border-slate-200 bg-white">
  {{-- Top bar --}}
  <div class="border-b border-slate-200 bg-slate-50">
    <div class="container mx-auto flex items-center justify-between px-4 py-2 text-xs text-slate-500">
      <nav class="flex items-center gap-4">
        <a href="#" class="hover:text-slate-700">Mitra PaDi UMKM</a>
        <a href="#" class="hover:text-slate-700">Menjadi Penjual</a>
        <a href="#" class="hover:text-slate-700">Info</a>
        <a href="#" class="hover:text-slate-700">Pusat Bantuan</a>
      </nav>

      <div class="flex items-center gap-3">
        {{-- Toggle bahasa dummy --}}
        <div class="flex overflow-hidden rounded-full border border-slate-200 text-[11px] font-semibold">
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

  {{-- Main bar --}}
  <div class="bg-white">
    <div class="container mx-auto flex items-center gap-4 px-4 py-3">
      {{-- Logo --}}
      <div class="flex items-center gap-2">
        {{-- ganti src dengan logo kamu kalau ada --}}
        <div class="flex h-10 w-24 items-center justify-center rounded-md bg-sky-50 text-xs font-bold text-sky-600">
          PaDi UMKM
        </div>
      </div>

      {{-- Kategori --}}
      <button class="flex items-center gap-2 text-sm font-semibold text-slate-700">
        <span class="grid grid-cols-3 gap-0.5">
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
          <span class="h-1.5 w-1.5 rounded-sm bg-slate-400"></span>
        </span>
        <span>Kategori</span>
      </button>

      {{-- Search --}}
      <div class="flex-1">
        <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2">
          <input class="w-full bg-transparent text-sm text-slate-700 placeholder:text-slate-400 outline-none"
            placeholder="Cari produk, jasa, atau vendor" />
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7" />
            <path d="m16 16 4 4" />
          </svg>
        </div>
      </div>

      {{-- Auth buttons --}}
      <div class="flex items-center gap-3">
        <a href="{{ route('login.as') }}"
          class="rounded-xl border border-sky-500 px-5 py-2 text-sm font-semibold text-sky-600 bg-white transition hover:bg-sky-50">
          Masuk
        </a>
        <a href="{{ route('register.as') }}"
          class="rounded-xl bg-sky-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-600">
          Daftar
        </a>
      </div>
    </div>
  </div>
</header>
