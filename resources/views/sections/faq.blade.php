<section class="space-y-4">
    <header>
        <p class="text-xs font-semibold text-slate-500">Butuh bantuan?</p>
        <h3 class="text-2xl font-bold text-slate-900">Pertanyaan umum</h3>
    </header>
    <div class="space-y-3">
        @php
            $faqs = [
                'Pengadaan barang dan jasa pemerintah di marketplace PaDi UMKM',
                'Belanja barang dan jasa dari UMKM jadi lebih mudah',
                'Cara daftar / registrasi di PaDi UMKM (Buyer/Seller)',
                'Bagaimana proses verifikasi vendor?',
            ];
        @endphp
        @foreach ($faqs as $faq)
            <details class="rounded-xl border border-slate-200 bg-white px-5 py-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-900">{{ $faq }}</summary>
                <p class="mt-3 text-sm text-slate-500">Tim PaDi UMKM siap membantu proses registrasi, verifikasi, hingga pengelolaan katalog produk Anda.</p>
            </details>
        @endforeach
    </div>
</section>
