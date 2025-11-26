@php
    $collections = [
        ['title' => 'SUPERDEAL Road to PaDi Business Forum', 'color' => 'from-sky-600 to-sky-500'],
        ['title' => 'Koleksi PAR4DE Jasa Percetakan', 'color' => 'from-amber-500 to-orange-400'],
        ['title' => 'Koleksi Andalan Perjalanan Bisnis', 'color' => 'from-emerald-500 to-teal-400'],
        ['title' => 'Koleksi Perawatan Teknologi', 'color' => 'from-indigo-500 to-blue-500'],
        ['title' => 'Koleksi Produktif di Kantor', 'color' => 'from-purple-500 to-fuchsia-500'],
        ['title' => 'Koleksi Hampers Premium', 'color' => 'from-pink-500 to-rose-400'],
    ];

    $products = collect(range(1, 8))->map(function ($i) {
        return [
            'title' => 'Produk unggulan #' . $i,
            'vendor' => 'UMKM Partner ' . $i,
            'price' => 'Rp' . number_format(150000 + $i * 50000, 0, ',', '.'),
            'badge' => $i % 2 === 0 ? 'Ready Stock' : 'Custom',
        ];
    });
@endphp

@foreach ($collections as $collection)
    <section class="space-y-4">
        <header class="flex flex-wrap items-center gap-4">
            <div>
                <p class="text-xs font-semibold text-slate-500">Pilihan kurasi</p>
                <h3 class="text-2xl font-bold text-slate-900">{{ $collection['title'] }}</h3>
            </div>
            <span class="ml-auto inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                <span class="h-2 w-2 rounded-xl bg-sky-500"></span>
                Update harian
            </span>
            <a class="text-sm font-semibold text-sky-600" href="#">Lihat Semua</a>
        </header>
        <div class="rounded-2xl bg-gradient-to-r {{ $collection['color'] }} p-6 text-white shadow-lg">
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div>
                    <p class="text-xs uppercase tracking-wide text-white/80">Program unggulan</p>
                    <h4 class="text-2xl font-bold">{{ $collection['title'] }}</h4>
                    <p class="text-sm text-white/80">Belanja lebih hemat dengan koleksi vendor terbaik pilihan PaDi UMKM.</p>
                </div>
                <button class="ml-auto rounded-xl bg-white/15 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/30">
                    Daftar Vendor Premium
                </button>
            </div>
        </div>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $product)
                <article class="flex flex-col rounded-xl border border-slate-100 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex h-40 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        {{ $product['title'] }}
                    </div>
                    <div class="mt-4 flex flex-1 flex-col gap-3">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="rounded-xl bg-sky-50 px-2 py-0.5 font-semibold text-sky-600">{{ $product['badge'] }}</span>
                            <span>Lead time 3-5 hari</span>
                        </div>
                        <h5 class="text-base font-semibold text-slate-900">{{ $product['title'] }}</h5>
                        <p class="text-sm text-slate-500">{{ $product['vendor'] }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $product['price'] }}</p>
                        <button class="mt-auto rounded-xl border border-sky-200 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
                            Ajukan Penawaran
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endforeach
