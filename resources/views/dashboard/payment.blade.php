@extends('layouts.dashboard')
@php
  $activeMenu = 'pembayaran';
@endphp

@section('title', 'Dashboard - Pembayaran')

@section('dashboard-breadcrumb')
  <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
    <a href="/" class="text-sky-600 hover:underline">Beranda</a>
    <span>/</span>
    <span>Transaksi</span>
    <span>/</span>
    <span class="text-slate-900">Pembayaran</span>
  </nav>
@endsection

@section('dashboard-content')
  @php
    $tabs = [
        ['key' => 'all', 'label' => 'Semua', 'count' => 3],
        ['key' => 'pending', 'label' => 'Menunggu Pembayaran', 'count' => 1],
        ['key' => 'paid', 'label' => 'Dibayar', 'count' => 1],
        ['key' => 'expired', 'label' => 'Expired', 'count' => 1],
    ];
    $cards = [
        [
            'id' => 'PR-2025-11-25-878610046',
            'product_title' => 'Souvenir Perusahaan/ Merchandise...',
            'qty' => '20pcs (20.00kg)',
            'price' => 'Rp5.960.000',
            'request_date' => '25 November 2025, 10:27:50 malam',
            'seller' => 'Global Natura Kreativa',
            'total' => 'Rp6.006.309',
            'pay_type' => 'Pembayaran Langsung',
            'pay_method' => 'Pembayaran QR',
            'status' => 'pending',
            'status_label' => 'Menunggu Pembayaran',
            'status_color' => 'bg-slate-200 text-slate-700',
            'badge' => 'Retail',
            'badge_color' => 'border-slate-300 text-slate-700',
        ],
        [
            'id' => 'PR-2025-07-10-123456789',
            'product_title' => 'Paket Hampers Executive',
            'qty' => '10pcs (8.5kg)',
            'price' => 'Rp3.250.000',
            'request_date' => '10 Juli 2025, 08:15:22 pagi',
            'seller' => 'Natura Gift Studio',
            'total' => 'Rp3.400.000',
            'pay_type' => 'Pembayaran Langsung',
            'pay_method' => 'Virtual Account',
            'status' => 'paid',
            'status_label' => 'Dibayar',
            'status_color' => 'bg-emerald-100 text-emerald-700',
            'badge' => 'Retail',
            'badge_color' => 'border-emerald-300 text-emerald-700',
        ],
        [
            'id' => 'PR-2025-06-02-555123888',
            'product_title' => 'Stationery Kit Karyawan',
            'qty' => '50pcs (32.0kg)',
            'price' => 'Rp4.750.000',
            'request_date' => '02 Juni 2025, 14:05:10 siang',
            'seller' => 'OfficeMart Nusantara',
            'total' => 'Rp4.850.000',
            'pay_type' => 'Termin 30 Hari',
            'pay_method' => 'Transfer',
            'status' => 'expired',
            'status_label' => 'Expired',
            'status_color' => 'bg-rose-100 text-rose-700',
            'badge' => 'B2B',
            'badge_color' => 'border-slate-300 text-slate-700',
        ],
    ];
  @endphp

  <section x-data="{ tab: 'all' }" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Pembayaran</h1>
    </div>

    <div class="flex flex-wrap gap-6 text-sm font-semibold">
      @foreach ($tabs as $tab)
        <button @click="tab = '{{ $tab['key'] }}'"
          :class="tab === '{{ $tab['key'] }}' ? 'border-sky-500 text-sky-600' :
              'border-transparent text-slate-500 hover:text-slate-700'"
          class="border-b-2 pb-2">
          {{ $tab['label'] }} ({{ $tab['count'] }})
        </button>
      @endforeach
    </div>

    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
      <div class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_auto] md:items-end">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Nomor PR</label>
          <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
            <input type="text" placeholder="Cari Nomor PR" class="w-full bg-transparent outline-none" />
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="7" />
              <path d="m16 16 4 4" />
            </svg>
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Tanggal Mulai</label>
          <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
            <input type="text" placeholder="Pilih Tanggal Mulai" class="w-full bg-transparent outline-none" />
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="1.8">
              <rect x="4" y="5" width="16" height="16" rx="2" />
              <path d="M16 3v4M8 3v4M4 11h16" />
            </svg>
          </div>
        </div>

        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Tanggal Akhir</label>
          <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
            <input type="text" placeholder="Pilih Tanggal Akhir" class="w-full bg-transparent outline-none" />
            <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="1.8">
              <rect x="4" y="5" width="16" height="16" rx="2" />
              <path d="M16 3v4M8 3v4M4 11h16" />
            </svg>
          </div>
        </div>

        <div class="flex gap-2 md:justify-end">
          <button
            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 md:w-auto">
            Reset
          </button>
        </div>
      </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-6 py-4 text-sm font-semibold text-slate-700">
        <div class="grid gap-3 md:grid-cols-[1.4fr_1.4fr_0.8fr_0.8fr]">
          <span>Produk</span>
          <span>Pesanan</span>
          <span>Jumlah Total</span>
          <span>Status</span>
        </div>
      </div>

      @foreach ($cards as $card)
        <div x-show="tab === 'all' || tab === '{{ $card['status'] }}'" x-transition.opacity.duration.150ms
          class="px-6 py-4">
          <div class="grid gap-3 md:grid-cols-[1.4fr_1.4fr_0.8fr_0.8fr] md:items-center">
            <div class="flex items-center gap-3">
              <img class="h-20 w-28 rounded-lg object-cover"
                src="https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&w=400&q=80"
                alt="Produk" />
              <div class="space-y-1 text-sm text-slate-700">
                <p class="font-semibold text-slate-900">{{ $card['product_title'] }}</p>
                <p class="text-xs text-slate-500">{{ $card['qty'] }}</p>
                <p class="font-semibold text-slate-900">{{ $card['price'] }}</p>
              </div>
            </div>

            <div class="space-y-1 text-sm text-slate-700">
              <p class="font-semibold text-slate-900">No. Purchase Request:</p>
              <p>{{ $card['id'] }}</p>
              <p class="font-semibold text-slate-900">Tanggal:</p>
              <p>{{ $card['request_date'] }}</p>
              <p class="font-semibold text-slate-900">Penjual:</p>
              <p>{{ $card['seller'] }}</p>
            </div>

            <div class="space-y-1 text-sm text-slate-700">
              <p class="font-semibold text-slate-900">{{ $card['total'] }}</p>
              <p class="text-emerald-600 font-semibold">{{ $card['pay_type'] }}</p>
              <p class="text-xs text-slate-500">{{ $card['pay_method'] }}</p>
            </div>

            <div class="flex items-center justify-between gap-2 md:flex-col md:items-start md:justify-start">
              <div class="space-y-2">
                <span
                  class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold {{ $card['status_color'] }}">
                  {{ $card['status_label'] }}
                </span>
                <span
                  class="inline-flex items-center rounded-lg border px-3 py-1 text-xs font-semibold {{ $card['badge_color'] }}">
                  {{ $card['badge'] }}
                </span>
              </div>
              <button class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="1" />
                  <circle cx="12" cy="5" r="1" />
                  <circle cx="12" cy="19" r="1" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      @endforeach

      <div
        class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-4 text-sm font-semibold text-slate-600">
        <button type="button"
          class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 hover:bg-slate-50">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m15 18-6-6 6-6" />
          </svg>
        </button>
        <span class="grid h-8 min-w-[32px] place-items-center rounded-lg bg-sky-500 px-2 text-white">1</span>
        <button type="button"
          class="grid h-8 w-8 place-items-center rounded-lg border border-slate-200 hover:bg-slate-50">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="m9 6 6 6-6 6" />
          </svg>
        </button>
      </div>
    </div>
  </section>
@endsection
