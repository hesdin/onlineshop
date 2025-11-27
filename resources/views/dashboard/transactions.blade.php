@extends('layouts.dashboard')
@php
  $activeMenu = 'transaksi';
@endphp

@section('title', 'Dashboard - Daftar Transaksi')

@section('dashboard-breadcrumb')
  <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
    <a href="/" class="text-sky-600 hover:underline">Beranda</a>
    <span>/</span>
    <span>Transaksi</span>
    <span>/</span>
    <span class="text-slate-900">Daftar Transaksi</span>
  </nav>
@endsection

@section('dashboard-content')
  @php
    $tabs = [
        ['key' => 'all', 'label' => 'Semua'],
        ['key' => 'diproses', 'label' => 'Diproses'],
        ['key' => 'dikirim', 'label' => 'Dikirim'],
        ['key' => 'pembayaran', 'label' => 'Pembayaran'],
        ['key' => 'refund', 'label' => 'Refund'],
        ['key' => 'diterima', 'label' => 'Diterima'],
        ['key' => 'selesai', 'label' => 'Selesai'],
        ['key' => 'lainnya', 'label' => 'Lainnya'],
    ];

    $items = [
        [
            'status' => 'diproses',
            'po' => 'PO-2025-12-01-001',
            'title' => 'Souvenir Kit New Hire',
            'date' => '01 Desember 2025, 09:20',
            'seller' => 'Global Natura Kreativa',
            'price' => 'Rp5.960.000',
            'type' => 'Retail',
        ],
        [
            'status' => 'dikirim',
            'po' => 'PO-2025-11-28-110',
            'title' => 'Paket Hampers Executive',
            'date' => '28 November 2025, 14:45',
            'seller' => 'Natura Gift Studio',
            'price' => 'Rp3.400.000',
            'type' => 'Retail',
        ],
        [
            'status' => 'pembayaran',
            'po' => 'PO-2025-11-20-088',
            'title' => 'Bundle Perlengkapan Kerja',
            'date' => '20 November 2025, 11:15',
            'seller' => 'OfficeMart Nusantara',
            'price' => 'Rp4.850.000',
            'type' => 'B2B',
        ],
        [
            'status' => 'selesai',
            'po' => 'PO-2025-11-10-221',
            'title' => 'Stationery Kit Karyawan',
            'date' => '10 November 2025, 10:00',
            'seller' => 'OfficeMart Nusantara',
            'price' => 'Rp4.200.000',
            'type' => 'Retail',
        ],
    ];
  @endphp

  <section x-data="{ tab: 'all' }" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      <p class="font-semibold">Batas waktu Penjual merespon pesanamu</p>
      <p>Jika pesanamu tidak direspon penjual dalam kurun waktu 2 x 24 jam (hari kerja, Senin s.d. Jumat) maka pesanamu
        akan <span class="font-semibold">dibatalkan otomatis</span> oleh sistem.</p>
    </div>

    <div class="space-y-3">
      <h1 class="text-2xl font-semibold text-slate-900">Daftar Transaksi</h1>
      <div class="flex flex-wrap gap-5 text-sm font-semibold">
        @foreach ($tabs as $tab)
          <button @click="tab = '{{ $tab['key'] }}'"
            :class="tab === '{{ $tab['key'] }}' ? 'border-sky-500 text-sky-600' :
                'border-transparent text-slate-500 hover:text-slate-700'"
            class="border-b-2 pb-2">
            {{ $tab['label'] }}
          </button>
        @endforeach
      </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
      <div class="grid gap-3 md:grid-cols-[1.2fr_1fr_1fr_auto] md:items-end">
        <div class="space-y-1">
          <label class="text-xs font-semibold text-slate-600">Nama PO</label>
          <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm focus-within:border-sky-400 focus-within:ring-2 focus-within:ring-sky-100">
            <input type="text" placeholder="Cari nama PO/SO/BAST" class="w-full bg-transparent outline-none" />
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

    <div class="rounded-lg border border-slate-200 bg-sky-50 px-4 py-3 text-sm text-slate-700">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <svg class="h-5 w-5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M3 7h18v9H7l-4 4V7z" />
          </svg>
          <p>Tekan terima sekaligus untuk konfirmasi penerimaan semua pesanamu!</p>
        </div>
        <button
          class="rounded-lg border border-sky-500 px-4 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-50">
          Terima Sekaligus
        </button>
      </div>
    </div>

    <div class="space-y-3">
      @foreach ($items as $item)
        <div x-show="tab === 'all' || tab === '{{ $item['status'] }}'" x-transition.opacity.duration.150ms
          class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3 text-sm">
            <div class="space-y-1">
              <p class="font-semibold text-slate-900">PO: {{ $item['po'] }}</p>
              <p class="text-slate-500">{{ $item['date'] }}</p>
            </div>
            <span
              class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700 capitalize">
              {{ $item['status'] }}
            </span>
          </div>
          <div class="mt-3 grid gap-3 md:grid-cols-[1.5fr_1fr_0.8fr_auto] md:items-center">
            <div class="space-y-1 text-sm text-slate-700">
              <p class="font-semibold text-slate-900">{{ $item['title'] }}</p>
              <p class="text-xs text-slate-500">Penjual: {{ $item['seller'] }}</p>
            </div>
            <div class="text-sm text-slate-700">
              <p class="text-xs text-slate-500">Tipe</p>
              <p class="font-semibold text-slate-900">{{ $item['type'] }}</p>
            </div>
            <div class="text-sm text-slate-700">
              <p class="text-xs text-slate-500">Total</p>
              <p class="font-semibold text-slate-900">{{ $item['price'] }}</p>
            </div>
            <div class="flex items-center justify-end gap-2">
              <button
                class="rounded-lg border border-sky-500 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">
                Detail
              </button>
              <button class="rounded-full p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
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
        x-show="(tab !== 'all') && {{ count($items) }} > 0 && ! [email protected]($items, $itm => $itm['status'] === tab)"
        x-transition.opacity.duration.150ms class="flex justify-center py-6 text-slate-500">
        <div class="text-center">
          <img class="mx-auto h-48 w-auto" src="https://padiumkm.id/_next/static/media/general-confirm.544b51ce.svg"
            alt="Empty illustration" />
          <p class="mt-2 text-sm">Belum ada transaksi pada tab ini.</p>
        </div>
      </div>
    </div>
  </section>
@endsection
