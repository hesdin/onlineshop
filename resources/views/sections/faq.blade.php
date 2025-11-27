<section class="space-y-4">
  <header>
    <h3 class="text-2xl font-bold text-slate-900">Pertanyaan umum</h3>
  </header>
  <div class="space-y-3">
    @php
      $faqs = [
          'Pengadaan barang dan jasa pemerintah di marketplace TP-PKK Marketplace',
          'Belanja barang dan jasa dari UMKM jadi lebih mudah',
          'Cara daftar / registrasi di TP-PKK Marketplace (Buyer/Seller)',
          'Bagaimana proses verifikasi vendor?',
      ];
    @endphp
    @foreach ($faqs as $faq)
      <details
        class="group rounded-lg border border-slate-200 bg-white text-slate-900 shadow-sm [&_summary::-webkit-details-marker]:hidden">
        <summary class="flex cursor-pointer items-center justify-between gap-4 px-6 py-4 text-lg font-bold">
          <span>{{ $faq }}</span>
          <svg class="h-5 w-5 text-slate-700 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </summary>

        <div class="border-t border-slate-100 px-6 py-4">
          <p class="text-sm text-slate-500">
            Tim TP-PKK Marketplace siap membantu proses registrasi, verifikasi, hingga
            pengelolaan katalog produk Anda.
          </p>
        </div>
      </details>
    @endforeach
  </div>
</section>
