<section class="space-y-4">
    <header class="flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500">Eksplor Produk</p>
            <h3 class="text-2xl font-bold text-slate-900">Kategori populer</h3>
        </div>
        <a class="text-sm font-semibold text-sky-600" href="#">Lihat semua</a>
    </header>
    <div class="grid gap-3 rounded-2xl bg-white p-6 shadow-lg sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6">
        @php
            $categories = [
                ['name' => 'ATK Kantor', 'color' => 'bg-amber-50 text-amber-700'],
                ['name' => 'Pangan Segar', 'color' => 'bg-rose-50 text-rose-700'],
                ['name' => 'Fashion', 'color' => 'bg-emerald-50 text-emerald-700'],
                ['name' => 'Elektronik', 'color' => 'bg-indigo-50 text-indigo-700'],
                ['name' => 'Jasa Kreatif', 'color' => 'bg-sky-50 text-sky-700'],
                ['name' => 'Gadget', 'color' => 'bg-purple-50 text-purple-700'],
                ['name' => 'Kesehatan', 'color' => 'bg-cyan-50 text-cyan-700'],
                ['name' => 'Hampers', 'color' => 'bg-pink-50 text-pink-700'],
                ['name' => 'Maintenance', 'color' => 'bg-teal-50 text-teal-700'],
                ['name' => 'Dekorasi', 'color' => 'bg-lime-50 text-lime-700'],
                ['name' => 'Furniture', 'color' => 'bg-slate-50 text-slate-700'],
                ['name' => 'Percetakan', 'color' => 'bg-orange-50 text-orange-700'],
            ];
        @endphp
        @foreach ($categories as $category)
            <button class="flex flex-col items-start rounded-xl border border-slate-100 p-4 text-left transition hover:-translate-y-1 hover:border-sky-200 hover:shadow-md">
                <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-lg text-lg font-bold {{ $category['color'] }}">
                    {{ substr($category['name'], 0, 1) }}
                </div>
                <p class="text-sm font-semibold text-slate-900">{{ $category['name'] }}</p>
                <p class="text-xs text-slate-500">146+ produk</p>
            </button>
        @endforeach
    </div>
</section>
