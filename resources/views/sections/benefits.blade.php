<section class="space-y-6 rounded-2xl bg-white p-8 shadow-lg">
    <header class="flex flex-wrap items-center gap-4">
        <div>
            <p class="text-xs font-semibold text-slate-500">Mengapa PaDi UMKM?</p>
            <h3 class="text-2xl font-bold text-slate-900">Keuntungan bergabung</h3>
        </div>
        <a class="ml-auto text-sm font-semibold text-sky-600" href="#">Pelajari lebih lanjut</a>
    </header>
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        @php
            $benefits = [
                ['title' => 'Kemudahan Pembayaran', 'desc' => 'Mendukung berbagai metode pembayaran B2B yang aman dan transparan.'],
                ['title' => 'Kepastian Pengiriman', 'desc' => 'Didukung logistik nasional untuk menjangkau seluruh Indonesia.'],
                ['title' => 'Pasar yang Pasti', 'desc' => 'Akses ke BUMN & perusahaan besar untuk memperluas bisnis.'],
                ['title' => 'Sistem Penilaian Produk', 'desc' => 'Penilaian vendor dan produk guna menjaga kualitas pengadaan.'],
            ];
        @endphp
        @foreach ($benefits as $benefit)
            <article class="rounded-xl border border-slate-100 p-5">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    ☆
                </div>
                <h4 class="text-lg font-semibold text-slate-900">{{ $benefit['title'] }}</h4>
                <p class="mt-2 text-sm text-slate-500">{{ $benefit['desc'] }}</p>
            </article>
        @endforeach
    </div>
</section>
