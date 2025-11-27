@extends('layouts.app')

@section('title', 'Checkout - TP-PKK Marketplace')

@section('content')
  @php
    $addresses = [
        [
            'name' => 'Hesdin (Nps Hesdin)',
            'phone' => '082338973159',
            'detail' => 'Hartaco Jaya, Kota Makassar, Sulawesi Selatan, 90231',
            'tag' => 'Rumah',
        ],
        [
            'name' => 'Kantor TP PKK',
            'phone' => '082198765432',
            'detail' => 'Jl. Veteran No. 45, Kota Makassar, Sulawesi Selatan, 90114',
            'tag' => 'Kantor',
        ],
    ];

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
                    'type' => 'Barang',
                    'weight' => 500,
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
                    'tags' => ['Jasa', 'PPN 23'],
                    'status' => 'Tersedia',
                    'type' => 'Jasa',
                    'weight' => null,
                ],
            ],
        ],
    ];
  @endphp

  <section class="space-y-6" x-data="checkoutPage(@js($cartGroups), @js($addresses))" x-cloak>
    <div class="flex items-center gap-2">
      <h1 class="text-3xl font-bold text-slate-900">Pengiriman</h1>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,2.4fr)_minmax(320px,1fr)] items-start">
      <div class="space-y-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-base font-semibold text-slate-800">Alamat Pengiriman</p>
          <div class="mt-2 text-sm text-slate-600">
            <p class="font-semibold text-slate-900" x-text="selectedAddress.name"></p>
            <p class="text-slate-700" x-text="selectedAddress.phone"></p>
            <p class="text-slate-600" x-text="selectedAddress.detail"></p>
          </div>
          <button type="button"
            class="mt-3 inline-flex w-full items-center justify-center rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
            @click="addressModalOpen = true">
            Pilih Alamat Lain
          </button>
        </div>

        <template x-for="(group, groupIndex) in groups" :key="groupIndex">
          <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
              <div class="h-10 w-10 flex items-center justify-center rounded-full bg-slate-100">
                <img src="{{ asset('assets/img/store-icon.svg') }}" alt="Store icon" class="h-6 w-6" />
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900" x-text="group.vendor"></p>
                <p class="text-xs text-slate-500" x-text="group.location"></p>
              </div>
              <span
                class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-3 py-1 text-[11px] font-semibold text-sky-700"
                x-text="group.benefit"></span>
            </div>

            <div class="divide-y divide-slate-100">
              <template x-for="(item, itemIndex) in group.items" :key="itemIndex">
                <div class="space-y-3 px-5 py-4">
                  <div class="flex items-start gap-4">
                    <div class="h-20 w-20 overflow-hidden rounded-lg border border-slate-100 bg-slate-50">
                      <img :src="item.img" :alt="item.name" class="h-full w-full object-cover" />
                    </div>

                    <div class="flex-1 space-y-2">
                      <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-lg bg-emerald-50 px-3 py-1 font-semibold text-emerald-700"
                          x-text="item.status"></span>
                        <div class="flex items-center gap-1 text-slate-500">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21z" />
                            <circle cx="12" cy="9.5" r="2.3" />
                          </svg>
                          <span x-text="group.location"></span>
                        </div>
                      </div>

                      <p class="text-sm font-semibold leading-snug text-slate-900" x-text="item.name"></p>

                      <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                        <template x-for="(tag, tIndex) in item.tags" :key="tIndex">
                          <span class="rounded-lg bg-slate-100 px-2.5 py-1" x-text="tag"></span>
                        </template>
                      </div>
                    </div>

                    <div class="flex flex-col items-end gap-3">
                      <p class="text-base font-bold text-slate-900" x-text="formatCurrency(item.price * item.qty)"></p>
                    </div>
                  </div>

                  <div class="pl-[100px] space-y-2">
                    <button type="button"
                      class="inline-flex items-center gap-2 text-sm font-semibold text-sky-600 hover:text-sky-700"
                      @click="toggleNote(groupIndex, itemIndex)"
                      :class="itemStates[groupIndex][itemIndex].noteOpen ? 'text-sky-700' : 'text-sky-600'">
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

                    <template x-if="itemStates[groupIndex][itemIndex].noteOpen">
                      <div class="relative">
                        <textarea rows="3" :maxlength="noteLimit"
                          class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 placeholder:font-normal placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100"
                          placeholder="Tuliskan catatan untuk Penjual" x-model="itemStates[groupIndex][itemIndex].note"
                          @input="limitNoteLength(groupIndex, itemIndex)"></textarea>
                        <span class="pointer-events-none absolute bottom-2 right-3 text-xs font-semibold text-slate-400"
                          x-text="`${itemStates[groupIndex][itemIndex].note.length}/${noteLimit}`"></span>
                      </div>
                    </template>
                  </div>

                  <div class="border-t border-slate-100 pt-4">
                    <div class="relative">
                      <div
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 flex items-center justify-center">
                        <span class="flex h-10 w-10 items-center justify-center text-sky-600">
                          <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path opacity="0.4"
                              d="M18.3334 11.6665V14.1665C18.3334 15.5498 17.2167 16.6665 15.8334 16.6665H15C15 15.7498 14.25 14.9998 13.3334 14.9998C12.4167 14.9998 11.6667 15.7498 11.6667 16.6665H8.33335C8.33335 15.7498 7.58335 14.9998 6.66669 14.9998C5.75002 14.9998 5.00002 15.7498 5.00002 16.6665H4.16669C2.78335 16.6665 1.66669 15.5498 1.66669 14.1665V11.6665H10.8334C11.75 11.6665 12.5 10.9165 12.5 9.99984V4.1665H14.0334C14.6334 4.1665 15.1834 4.49151 15.4834 5.00818L16.9083 7.49984H15.8334C15.375 7.49984 15 7.87484 15 8.33317V10.8332C15 11.2915 15.375 11.6665 15.8334 11.6665H18.3334Z"
                              fill="#00A6F4" />
                            <path
                              d="M6.66667 18.3333C7.58714 18.3333 8.33333 17.5871 8.33333 16.6667C8.33333 15.7462 7.58714 15 6.66667 15C5.74619 15 5 15.7462 5 16.6667C5 17.5871 5.74619 18.3333 6.66667 18.3333Z"
                              fill="#00A6F4" />
                            <path
                              d="M13.3334 18.3333C14.2538 18.3333 15 17.5871 15 16.6667C15 15.7462 14.2538 15 13.3334 15C12.4129 15 11.6667 15.7462 11.6667 16.6667C11.6667 17.5871 12.4129 18.3333 13.3334 18.3333Z"
                              fill="#00A6F4" />
                            <path
                              d="M18.3333 10.4417V11.6667H15.8333C15.375 11.6667 15 11.2917 15 10.8333V8.33333C15 7.875 15.375 7.5 15.8333 7.5H16.9083L18.1167 9.61665C18.2583 9.86665 18.3333 10.15 18.3333 10.4417Z"
                              fill="#00A6F4" />
                            <path
                              d="M11.6666 1.6665H4.99996C3.37496 1.6665 2.02497 2.83317 1.73331 4.37484H5.83329C6.17496 4.37484 6.45829 4.65817 6.45829 4.99984C6.45829 5.3415 6.17496 5.62484 5.83329 5.62484H1.66663V6.87484H4.16663C4.50829 6.87484 4.79163 7.15817 4.79163 7.49984C4.79163 7.8415 4.50829 8.12484 4.16663 8.12484H1.66663V9.37484H2.49996C2.84163 9.37484 3.12496 9.65817 3.12496 9.99984C3.12496 10.3415 2.84163 10.6248 2.49996 10.6248H1.66663V11.6665H10.8333C11.75 11.6665 12.5 10.9165 12.5 9.99984V2.49984C12.5 2.0415 12.125 1.6665 11.6666 1.6665Z"
                              fill="#00A6F4" />
                            <path
                              d="M1.73333 4.375H0.833313C0.491646 4.375 0.208313 4.65833 0.208313 5C0.208313 5.34167 0.491646 5.625 0.833313 5.625H1.66665V5C1.66665 4.78333 1.69166 4.575 1.73333 4.375Z"
                              fill="#00A6F4" />
                            <path
                              d="M0.833313 6.875C0.491646 6.875 0.208313 7.15833 0.208313 7.5C0.208313 7.84167 0.491646 8.125 0.833313 8.125H1.66665V6.875H0.833313Z"
                              fill="#00A6F4" />
                            <path
                              d="M0.833313 9.375C0.491646 9.375 0.208313 9.65833 0.208313 10C0.208313 10.3417 0.491646 10.625 0.833313 10.625H1.66665V9.375H0.833313Z"
                              fill="#00A6F4" />
                          </svg>

                        </span>
                      </div>
                      <select
                        class="block w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 py-3 pl-20 pr-12 text-base font-semibold text-slate-700 transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-100">
                        <option selected>Pilih Pengiriman</option>
                        <option>JNE - Reguler</option>
                        <option>JNT - Ekspres</option>
                        <option>SiCepat - Best</option>
                      </select>
                      <div class="pointer-events-none absolute right-5 top-1/2 -translate-y-1/2 text-slate-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                          stroke-width="1.8">
                          <path d="M5 7.5 10 12l5-4.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                    <span>Subtotal</span>
                    <span x-text="formatCurrency(item.price * item.qty)"></span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </template>
      </div>

      <aside class="space-y-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-lg font-semibold text-slate-900">Ringkasan Belanja</p>

          <div class="mt-4 space-y-4 text-sm text-slate-600">
            <template x-for="(order, idx) in orderSummaries" :key="idx">
              <div class="space-y-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                <p class="text-base font-semibold text-slate-800" x-text="`Pesanan ${idx + 1}`"></p>

                <div class="flex items-start justify-between gap-4">
                  <span class="text-slate-500">Nama Toko</span>
                  <span class="max-w-[60%] text-sm font-semibold text-slate-800 text-right"
                    x-text="order.vendor"></span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-slate-500" x-text="order.typeLabel"></span>
                  <span class="font-semibold text-slate-800" x-text="formatCurrency(order.total)"></span>
                </div>

                <template x-if="order.benefit">
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-600" x-text="order.benefit"></span>
                  </div>
                </template>

                <div class="flex items-center justify-between font-semibold text-slate-800">
                  <span>Total Pesanan</span>
                  <span x-text="formatCurrency(order.total)"></span>
                </div>
              </div>
            </template>

            <div
              class="border-t border-slate-200 pt-3 flex items-center justify-between text-base font-semibold text-slate-800">
              <span>Total Harga</span>
              <span class="text-base text-slate-900" x-text="formatCurrency(selectedTotal)"></span>
            </div>
          </div>

          <button type="button"
            class="mt-5 w-full rounded-lg px-4 py-3 text-sm font-semibold shadow-sm transition bg-slate-200 text-slate-500 cursor-not-allowed">
            Pilih Pembayaran
          </button>
        </div>
      </aside>
    </div>

    <div x-show="addressModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-10" x-transition.opacity>
      <div class="w-full max-w-3xl rounded-xl bg-white shadow-2xl ring-1 ring-black/5"
        @click.away="addressModalOpen = false" x-transition>
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
          <div>
            <p class="text-lg font-bold text-slate-900">Alamat Pengiriman</p>
            <p class="text-sm text-slate-500">Pilih alamat tujuan pesananmu</p>
          </div>
          <div class="flex items-center gap-2">
            <button type="button"
              class="rounded-lg border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50"
              @click="openNewAddress()">
              Tambah Alamat
            </button>
            <button type="button" class="p-2 text-slate-500 hover:text-slate-700" @click="addressModalOpen = false">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m6 6 12 12M6 18 18 6" stroke-linecap="round" />
              </svg>
            </button>
          </div>
        </div>

        <div class="space-y-4 px-6 py-5 max-h-[70vh] overflow-y-auto">
          <template x-for="(addr, index) in addresses" :key="index">
            <div class="flex flex-col gap-3 rounded-xl border px-4 py-3 sm:flex-row sm:items-center sm:gap-6"
              :class="selectedAddressIndex === index ? 'border-emerald-300 bg-emerald-50/70' : 'border-slate-200 bg-white'">
              <div class="flex-1 space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                  <p class="text-sm font-semibold text-slate-900" x-text="addr.name"></p>
                  <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600"
                    x-text="addr.tag"></span>
                  <span x-show="selectedAddressIndex === index"
                    class="inline-flex items-center rounded-full bg-emerald-500 px-3 py-1 text-xs font-semibold text-white">Terpilih</span>
                </div>
                <p class="text-xs text-slate-500" x-text="addr.phone"></p>
                <p class="text-sm font-semibold text-slate-800" x-text="addr.detail"></p>
              </div>
              <div class="flex flex-col items-stretch gap-2 sm:w-40">
                <button type="button"
                  class="w-full rounded-lg border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50"
                  @click="selectAddress(index)">
                  Pilih Alamat
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script>
    function checkoutPage(groups, addresses) {
      // addresses is an array of available addresses
      const currency = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
      });

      return {
        groups,
        addresses,
        addressModalOpen: false,
        selectedAddressIndex: 0,
        noteLimit: 100,
        itemStates: groups.map(group => group.items.map(item => ({
          checked: true,
          price: item.price,
          qty: item.qty,
          type: item.type,
          weight: item.weight ?? 0,
          note: '',
          noteOpen: false,
        }))),
        get selectedTotal() {
          return this.itemStates.flat().reduce((sum, item) => {
            return item.checked ? sum + item.price * item.qty : sum;
          }, 0);
        },
        get selectedAddress() {
          return this.addresses[this.selectedAddressIndex] || this.addresses[0] || {};
        },
        get orderSummaries() {
          return this.groups.map((group, groupIndex) => {
            const selected = this.itemStates[groupIndex].filter(item => item.checked);
            if (selected.length === 0) {
              return null;
            }
            const itemCount = selected.reduce((sum, item) => sum + item.qty, 0);
            const total = selected.reduce((sum, item) => sum + item.price * item.qty, 0);
            const totalWeight = selected.reduce((sum, item) => sum + (item.weight || 0) * item.qty, 0);
            const hasOnlyServices = selected.every(item => item.type === 'Jasa');
            const typeLabel = hasOnlyServices ?
              'Total Harga Jasa' :
              `Total Harga ${itemCount} Barang${totalWeight ? ` (${totalWeight.toFixed(2)} gram)` : ''}`;

            return {
              vendor: group.vendor,
              total,
              typeLabel,
              benefit: group.benefit === 'Gratis Ongkir' ? 'Gratis Ongkir' : null,
            };
          }).filter(Boolean);
        },
        formatCurrency(value) {
          return currency.format(value);
        },
        selectAddress(index) {
          this.selectedAddressIndex = index;
          this.addressModalOpen = false;
        },
        openNewAddress() {
          // Placeholder: integrate with real add-address flow later
          alert('Tambah Alamat belum terhubung');
        },
        toggleNote(groupIndex, itemIndex) {
          const state = this.itemStates[groupIndex][itemIndex];
          state.noteOpen = !state.noteOpen;
        },
        limitNoteLength(groupIndex, itemIndex) {
          const state = this.itemStates[groupIndex][itemIndex];
          state.note = (state.note || '').slice(0, this.noteLimit);
        },
      };
    }
  </script>
@endpush
