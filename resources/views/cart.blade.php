@extends('layouts.app')

@section('title', 'Keranjang - TP-PKK Marketplace')

@section('content')
  @php
    $cartGroups = [
        [
            'vendor' => 'Investko Megamart/GratisOngkir S&K berlaku',
            'location' => 'Jawa Barat',
            'benefit' => 'Gratis Ongkir',
            'items' => [
                [
                    'name' => 'Dove Body Wash Deeply Nourishing Botol 400ml / Sabun Cair / Body Wash',
                    'price' => 86800,
                    'qty' => 1,
                    'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&w=800&q=80',
                    'tags' => ['UMKM', 'PPN 22'],
                    'status' => 'Tersedia',
                ],
            ],
        ],
        [
            'vendor' => 'Prestige Printopia Mandiri',
            'location' => 'Jawa Timur',
            'benefit' => 'Custom',
            'items' => [
                [
                    'name' => 'Cetak Flyer Custom ukuran A5 | 2 Sisi',
                    'price' => 1500,
                    'qty' => 1,
                    'img' => 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&w=800&q=80',
                    'tags' => ['UMKM', 'PPN 22'],
                    'status' => 'Tersedia',
                ],
            ],
        ],
    ];

    $recommendations = [
        [
            'title' => 'Mesin Pelubang Kertas 2 Lubang',
            'price' => 1158250,
            'location' => 'Kota Depok',
            'sold' => 'Terjual 8',
            'img' => 'https://images.unsplash.com/photo-1454165205744-3b78555e5572?auto=format&w=800&q=80',
        ],
        [
            'title' => 'R0TS70 - Kertas A4 Art Paper BLUEPRINT',
            'price' => 94000,
            'location' => 'Kota Depok',
            'sold' => 'Terjual 33',
            'img' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&w=800&q=80',
        ],
        [
            'title' => 'Pulpen Pilot Ball Liner 0.8mm Hitam/Biru',
            'price' => 315000,
            'location' => 'Jakarta Timur',
            'sold' => 'Terjual 2',
            'img' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&w=800&q=80',
        ],
        [
            'title' => 'DT097 - Direct Thermal Sticker Kertas Label',
            'price' => 82000,
            'location' => 'Jakarta Timur',
            'sold' => 'Terjual 1',
            'img' => 'https://images.unsplash.com/photo-1433832597046-4f10e10ac764?auto=format&w=800&q=80',
        ],
        [
            'title' => 'Papan Suku Bunga dan Kurs Digital',
            'price' => 310273874,
            'location' => 'Kota Gorontalo',
            'sold' => 'Terjual 1',
            'img' => 'https://images.unsplash.com/photo-1448932223592-d1fc686e76ea?auto=format&w=800&q=80',
        ],
        [
            'title' => 'Jasa Pemasangan Signage Event & Booth',
            'price' => 986762000,
            'location' => 'Kota Dumai',
            'sold' => 'Terjual 1',
            'img' => 'https://images.unsplash.com/photo-1454165205744-3b78555e5572?auto=format&w=800&q=80&sig=2',
        ],
        [
            'title' => 'Zebra Pulpen Sarasa 1.0',
            'price' => 345000,
            'location' => 'Kota Depok',
            'sold' => 'Terjual 103',
            'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&w=800&q=80&sig=3',
        ],
        [
            'title' => 'Zebra Pulpen Sarasa 0.5',
            'price' => 325000,
            'location' => 'Kota Depok',
            'sold' => 'Terjual 102',
            'img' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&w=800&q=80&sig=4',
        ],
        [
            'title' => 'Lampu Cyber 500W',
            'price' => 125500,
            'location' => 'Kab. Indramayu',
            'sold' => 'Terjual 2',
            'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&w=800&q=80',
        ],
        [
            'title' => 'Baterai ABC Alkaline AAA',
            'price' => 445000,
            'location' => 'Kota Depok',
            'sold' => 'Terjual 31',
            'img' => 'https://images.unsplash.com/photo-1433832597046-4f10e10ac764?auto=format&w=800&q=80&sig=5',
        ],
    ];
  @endphp

  <section class="space-y-6" x-data="cartPage(@js($cartGroups))" x-cloak>
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-bold text-slate-900">Keranjang</h1>
      <div class="flex items-center gap-3 text-sm font-semibold text-slate-700">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
            :checked="isAllChecked" @change="toggleAll($event.target.checked)"
            x-effect="$el.indeterminate = hasPartialAll" />
          <span>Pilih Semua</span>
        </label>
      </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2.3fr)_minmax(320px,1fr)] items-start">
      <div class="space-y-4">
        @foreach ($cartGroups as $groupIndex => $group)
          <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
              <input type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                :checked="isGroupChecked({{ $groupIndex }})"
                @change="toggleGroup({{ $groupIndex }}, $event.target.checked)"
                x-effect="$el.indeterminate = isGroupIndeterminate({{ $groupIndex }})" />
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900">{{ $group['vendor'] }}</p>
                <p class="text-xs text-slate-500">{{ $group['location'] }}</p>
              </div>
              <span
                class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700">
                {{ $group['benefit'] }}
              </span>
            </div>

            <div class="divide-y divide-slate-100">
              @foreach ($group['items'] as $itemIndex => $item)
                <div class="space-y-3 px-5 py-4">
                  <div class="flex items-start gap-4">
                    <input type="checkbox"
                      class="mt-1 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                      :checked="itemStates[{{ $groupIndex }}][{{ $itemIndex }}].checked"
                      @change="toggleItem({{ $groupIndex }}, {{ $itemIndex }}, $event.target.checked)" />

                    <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center md:gap-6">
                      <div class="h-24 w-24 overflow-hidden rounded-lg border border-slate-100 bg-slate-50">
                        <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover" />
                      </div>

                      <div class="flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                          <span class="rounded-lg bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
                            {{ $item['status'] }}
                          </span>
                          <div class="flex items-center gap-1 text-slate-500">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="1.8">
                              <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                              <circle cx="12" cy="9.5" r="2.3" />
                            </svg>
                            <span>{{ $group['location'] }}</span>
                          </div>
                        </div>

                        <p class="text-base font-semibold leading-snug text-slate-900">
                          {{ $item['name'] }}
                        </p>

                        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                          @foreach ($item['tags'] as $tag)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1">{{ $tag }}</span>
                          @endforeach
                        </div>
                      </div>

                      <div class="flex flex-col items-end gap-3">
                        <p class="text-lg font-bold text-slate-900">
                          Rp{{ number_format($item['price'], 0, ',', '.') }}
                        </p>

                        <div class="flex items-center gap-3 text-slate-400">
                          <button type="button" class="rounded-lg p-2 transition hover:bg-slate-50"
                            aria-label="Hapus item">
                            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                              stroke-linecap="round" stroke-linejoin="round" height="1em" width="1em"
                              xmlns="http://www.w3.org/2000/svg">
                              <polyline points="3 6 5 6 21 6"></polyline>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                              </path>
                              <line x1="10" y1="11" x2="10" y2="17"></line>
                              <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                          </button>

                          <div class="inline-flex overflow-hidden rounded-md border border-slate-200">
                            <button type="button"
                              class="px-3 py-2 text-slate-500 transition hover:bg-slate-50">−</button>
                            <span class="border-x border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800">
                              {{ $item['qty'] }}
                            </span>
                            <button type="button"
                              class="px-3 py-2 text-slate-500 transition hover:bg-slate-50">+</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="pl-11 md:pl-[140px]">
                    <button type="button"
                      class="inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:text-sky-700">
                      <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 20H21.5" stroke="#00A6F4" stroke-width="1.5" stroke-linecap="round"
                          stroke-linejoin="round" />
                        <path
                          d="M17 3.49998C17.3978 3.10216 17.9374 2.87866 18.5 2.87866C18.7786 2.87866 19.0544 2.93353 19.3118 3.04014C19.5692 3.14674 19.803 3.303 20 3.49998C20.197 3.69697 20.3532 3.93082 20.4598 4.18819C20.5664 4.44556 20.6213 4.72141 20.6213 4.99998C20.6213 5.27856 20.5664 5.55441 20.4598 5.81178C20.3532 6.06915 20.197 6.303 20 6.49998L7.5 19L3.5 20L4.5 16L17 3.49998Z"
                          stroke="#00A6F4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>

                      <span>Catatan Untuk Penjual</span>
                    </button>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>

      <aside class="space-y-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-lg font-semibold text-slate-900">Ringkasan Belanja</p>

          <div class="mt-4 space-y-4 text-sm text-slate-600">
            <template x-if="orderSummaries.length === 0">
              <p class="text-xs text-slate-500">Pilih produk untuk melihat ringkasan pesanan.</p>
            </template>

            <template x-for="(order, idx) in orderSummaries" :key="idx">
              <div class="space-y-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                <p class="text-base font-semibold text-slate-800" x-text="`Pesanan ke ${idx + 1}`"></p>

                <div class="flex items-start justify-between gap-4">
                  <span class="text-slate-500">Nama Toko</span>
                  <span class="max-w-[60%] text-sm font-semibold text-slate-800 text-right" x-text="order.vendor"></span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-500" x-text="`Total Harga ${order.itemCount} Barang`"></span>
                  <span class="font-semibold text-slate-800" x-text="formatCurrency(order.total)"></span>
                </div>

                <div class="flex items-center justify-between font-semibold text-slate-800">
                  <span>Total Pesanan</span>
                  <span x-text="formatCurrency(order.total)"></span>
                </div>
              </div>
            </template>

            <div class="flex items-center justify-between text-xs text-slate-500">
              <span x-text="selectedCount ? `${selectedGroups} toko, ${selectedCount} barang dipilih` : 'Belum ada barang dipilih'"></span>
              <span class="rounded-lg bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">Belum termasuk ongkos kirim</span>
            </div>

            <div class="border-t border-slate-200 pt-3 flex items-center justify-between text-base font-semibold text-slate-800">
              <span>Total Harga</span>
              <span class="text-base text-slate-900" x-text="formatCurrency(selectedTotal)"></span>
            </div>
          </div>

          <button type="button"
            class="mt-5 w-full rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition"
            :class="selectedCount === 0 ? 'bg-slate-200 text-slate-500 cursor-not-allowed' : 'bg-sky-500 text-white hover:bg-sky-600'"
            :disabled="selectedCount === 0">
            <span x-text="selectedCount === 0 ? 'Pilih produk terlebih dahulu' : 'Beli'"></span>
          </button>
        </div>

        <div class="rounded-lg border border-dashed border-slate-200 bg-white px-5 py-4 text-sm text-slate-600 shadow-sm">
          <p class="font-semibold text-slate-800">Butuh bantuan?</p>
          <p class="mt-1 text-slate-500">Hubungi pusat bantuan atau chat penjual sebelum checkout.</p>
        </div>
      </aside>
    </div>
  </section>

  <section class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-3xl font-bold text-slate-900">Rekomendasi Untuk Kamu</h2>
      <a href="#" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Lihat Semua</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
      @foreach ($recommendations as $rec)
        <article
          class="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
          <div class="h-40 w-full overflow-hidden bg-slate-100">
            <img src="{{ $rec['img'] }}" alt="{{ $rec['title'] }}" class="h-full w-full object-cover" />
          </div>
          <div class="flex flex-1 flex-col gap-2 p-4">
            <div class="flex items-center gap-2 text-[11px] font-semibold">
              <span class="rounded-sm bg-sky-500 px-2 py-0.5 uppercase tracking-wide text-white">UMKM</span>
              <span class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-600">PKP</span>
            </div>

            <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $rec['title'] }}</p>

            <div class="flex items-center gap-1 text-[11px] text-slate-500">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path
                  d="M10 2.5a5.25 5.25 0 00-5.25 5.25c0 3.714 4.338 7.458 4.83 7.88a.56.56 0 00.72 0c.492-.422 4.95-4.166 4.95-7.88A5.25 5.25 0 0010 2.5zm0 7.25a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
              <span>{{ $rec['location'] }}</span>
            </div>

            <p class="text-base font-bold text-slate-900">
              Rp{{ number_format($rec['price'], 0, ',', '.') }}
            </p>

            <div class="mt-auto flex items-center justify-between text-xs text-slate-500">
              <span>{{ $rec['sold'] }}</span>
              <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Stok aman</span>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="flex flex-wrap items-center gap-4 rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
    <p class="text-sm font-semibold text-slate-800">Metode Pembayaran</p>
    <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
      @foreach (['mandiri', 'BNI', 'BSI', 'BTN', 'BCA', 'BRI', 'POSPAY', 'LINKAJA', 'DANA', 'OVO', 'VISA', 'MASTER'] as $pay)
        <span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1 capitalize">{{ $pay }}</span>
      @endforeach
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    function cartPage(groups) {
      const currency = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
      });

      return {
        groups,
        itemStates: groups.map(group => group.items.map(item => ({
          checked: false,
          price: item.price,
          qty: item.qty,
        }))),
        toggleAll(value) {
          this.itemStates.forEach(group => group.forEach(item => item.checked = value));
        },
        toggleGroup(index, value) {
          this.itemStates[index].forEach(item => item.checked = value);
        },
        toggleItem(groupIndex, itemIndex, value) {
          this.itemStates[groupIndex][itemIndex].checked = value;
        },
        isGroupChecked(index) {
          return this.itemStates[index].every(item => item.checked);
        },
        isGroupIndeterminate(index) {
          const group = this.itemStates[index];
          const someChecked = group.some(item => item.checked);
          const allChecked = group.every(item => item.checked);
          return someChecked && !allChecked;
        },
        get totalItems() {
          return this.itemStates.reduce((sum, group) => sum + group.length, 0);
        },
        get selectedCount() {
          return this.itemStates.flat().filter(item => item.checked).length;
        },
        get selectedGroups() {
          return this.itemStates.filter(group => group.some(item => item.checked)).length;
        },
        get selectedTotal() {
          return this.itemStates.flat().reduce((sum, item) => {
            return item.checked ? sum + item.price * item.qty : sum;
          }, 0);
        },
        get orderSummaries() {
          return this.groups.map((group, groupIndex) => {
            const selected = this.itemStates[groupIndex].filter(item => item.checked);
            if (selected.length === 0) {
              return null;
            }
            const itemCount = selected.reduce((sum, item) => sum + item.qty, 0);
            const total = selected.reduce((sum, item) => sum + item.price * item.qty, 0);
            return { vendor: group.vendor, itemCount, total };
          }).filter(Boolean);
        },
        get isAllChecked() {
          return this.totalItems > 0 && this.selectedCount === this.totalItems;
        },
        get hasPartialAll() {
          return this.selectedCount > 0 && this.selectedCount < this.totalItems;
        },
        formatCurrency(value) {
          return currency.format(value);
        },
      };
    }
  </script>
@endpush
