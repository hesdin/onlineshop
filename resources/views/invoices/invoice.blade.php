<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice - {{ $order->order_number }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 10px;
      /* Smaller font as per reference */
      color: #333;
      line-height: 1.4;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      padding: 30px 40px;
      background: #fff;
    }

    /* Header */
    .header {
      width: 100%;
      margin-bottom: 30px;
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #0ea5e9;
      /* Sky Blue */
      margin-bottom: 5px;
    }

    .invoice-title-block {
      text-align: right;
      vertical-align: top;
    }

    .invoice-label {
      font-size: 10px;
      font-weight: bold;
      color: #333;
      text-transform: uppercase;
    }

    .invoice-number {
      font-size: 11px;
      color: #0ea5e9;
      font-weight: bold;
      margin-top: 2px;
    }

    /* Info Section */
    .info-table {
      width: 100%;
      margin-bottom: 20px;
    }

    .info-table td {
      vertical-align: top;
    }

    .info-label {
      font-weight: bold;
      font-size: 10px;
      text-transform: uppercase;
      margin-bottom: 5px;
      color: #333;
    }

    .info-row {
      margin-bottom: 3px;
    }

    .info-key {
      display: inline-block;
      width: 100px;
      color: #666;
    }

    .seller-name {
      font-weight: bold;
      display: inline-block;
    }

    .buyer-name {
      font-weight: bold;
    }

    .buyer-address {
      margin-top: 2px;
      color: #666;
      max-width: 300px;
      line-height: 1.4;
    }

    /* Items Table */
    .items-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .items-table th {
      text-align: left;
      padding: 10px 0;
      border-top: 2px solid #ddd;
      border-bottom: 2px solid #ddd;
      font-size: 9px;
      font-transform: uppercase;
      font-weight: bold;
      color: #333;
    }

    .items-table td {
      padding: 15px 0;
      vertical-align: top;
      border-bottom: 1px solid #f0f0f0;
    }

    .product-name {
      font-weight: bold;
      font-size: 11px;
      color: #0ea5e9;
      margin-bottom: 4px;
    }

    .product-meta {
      font-size: 9px;
      color: #888;
    }

    .text-right {
      text-align: right;
    }

    .text-center {
      text-align: center;
    }

    /* Totals */
    .totals-container {
      width: 100%;
      margin-top: 10px;
    }

    .totals-table {
      width: 45%;
      float: right;
    }

    .totals-table td {
      padding: 3px 0;
    }

    .totals-label {
      font-weight: bold;
      color: #333;
    }

    .totals-value {
      text-align: right;
      color: #333;
    }

    .grand-total-row td {
      padding-top: 10px;
      font-size: 12px;
      font-weight: bold;
    }

    /* Watermark */
    .watermark {
      position: absolute;
      top: 300px;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-15deg);
      border: 3px solid #e0f2fe;
      color: #bae6fd;
      font-size: 80px;
      font-weight: bold;
      padding: 10px 40px;
      opacity: 0.8;
      z-index: -1;
      letter-spacing: 10px;
    }

    .watermark-unpaid {
      position: absolute;
      top: 300px;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-15deg);
      border: 3px solid #fef3c7;
      color: #fcd34d;
      font-size: 60px;
      font-weight: bold;
      padding: 10px 40px;
      opacity: 0.8;
      z-index: -1;
      letter-spacing: 5px;
    }

    /* Payment Status Badge */
    .payment-status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .payment-status-paid {
      background-color: #d1fae5;
      color: #065f46;
      border: 1px solid #6ee7b7;
    }

    .payment-status-unpaid {
      background-color: #fef3c7;
      color: #92400e;
      border: 1px solid #fcd34d;
    }

    /* Footer */
    .footer {
      margin-top: 50px;
      font-size: 9px;
      color: #666;
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .payment-method-label {
      color: #666;
      margin-bottom: 4px;
    }

    .payment-method-value {
      font-weight: bold;
      font-size: 11px;
      color: #333;
      margin-bottom: 20px;
    }

    .insurance-info {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 9px;
      color: #666;
      margin-top: 20px;
    }

    .insurance-logo {
      font-weight: bold;
      color: #0ea5e9;
      background: #e0f2fe;
      padding: 1px 4px;
      border-radius: 2px;
      font-size: 9px;
    }
  </style>
</head>

<body>
  @if ($order->payment_status == 'paid')
    <div class="watermark">LUNAS</div>
  @else
    <div class="watermark-unpaid">BELUM LUNAS</div>
  @endif

  <div class="container">
    <!-- Header -->
    <table class="header">
      <tr>
        <td style="vertical-align: top;">
          <!-- Logo / Brand Text -->
          <div class="logo">{{ config('app.name', 'TP-PKK Marketplace') }}</div>
        </td>
        <td class="invoice-title-block">
          <div class="invoice-label">INVOICE</div>
          <div class="invoice-number">{{ $order->order_number }}</div>
          <div style="margin-top: 8px;">
            @if ($order->payment_status == 'paid')
              <span class="payment-status-badge payment-status-paid">✓ LUNAS</span>
            @else
              <span class="payment-status-badge payment-status-unpaid">! BELUM LUNAS</span>
            @endif
          </div>
        </td>
      </tr>
    </table>

    <!-- Info Table -->
    <table class="info-table">
      <tr>
        <td style="width: 50%; padding-right: 20px;">
          <div class="info-label">DITERBITKAN ATAS NAMA</div>
          <div class="info-row">
            <span class="info-key">Penjual</span>
            <span class="seller-name">: {{ $order->store->name }}</span>
          </div>
        </td>
        <td style="width: 50%;">
          <div class="info-label">UNTUK</div>
          <div class="info-row">
            <span class="info-key">Pembeli</span>
            <span class="buyer-name">: {{ $order->address->recipient_name ?? $order->user->name }}</span>
          </div>
          <div class="info-row">
            <span class="info-key">Tanggal Pembelian</span>
            <span>: {{ $order->created_at->translatedFormat('d F Y') }}</span>
          </div>
          <div class="info-row">
            <span class="info-key" style="vertical-align: top;">Alamat Pengiriman</span>
            <span style="display: inline-block; width: 65%; vertical-align: top;">
              : @if ($order->address)
                <strong>{{ $order->address->recipient_name }}</strong> ({{ $order->address->phone }})<br>
                {{ $order->address->address_line }}<br>
                {{ $order->address->district ? $order->address->district . ', ' : '' }}
                {{ $order->address->city }}, {{ $order->address->postal_code }}
                {{ $order->address->province }}
              @else
                {{ $order->user->email }}
              @endif
            </span>
          </div>
        </td>
      </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
      <thead>
        <tr>
          <th style="width: 45%;">INFO PRODUK</th>
          <th class="text-center" style="width: 15%;">JUMLAH</th>
          <th class="text-right" style="width: 20%;">HARGA SATUAN</th>
          <th class="text-right" style="width: 20%;">TOTAL HARGA</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->items as $item)
          <tr>
            <td>
              <div class="product-name">{{ $item->product->name }}</div>
              <div class="product-meta">Berat: 1 kg {{-- Placeholder as weight is not in item model --}}</div>
              @if ($item->note)
                <div class="product-meta" style="font-style: italic;">Catatan: {{ $item->note }}</div>
              @endif
            </td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-right">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="text-right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-container">
      <table class="totals-table">
        <tr>
          <td class="totals-label">TOTAL HARGA ({{ $order->items_count }} BARANG)</td>
          <td class="totals-value">
            Rp{{ number_format($order->grand_total - ($order->shipping_cost ?? 0) + ($order->discount_amount ?? 0), 0, ',', '.') }}
          </td>
        </tr>
        <tr>
          <td class="totals-label" style="font-weight: normal;">Total Ongkos Kirim</td>
          <td class="totals-value">Rp{{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}</td>
        </tr>
        @if ($order->discount_amount > 0)
          <tr>
            <td class="totals-label" style="font-weight: normal;">Diskon</td>
            <td class="totals-value">-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</td>
          </tr>
        @endif
        <tr class="grand-total-row">
          <td>TOTAL BELANJA</td>
          <td class="totals-value">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr class="grand-total-row">
          <td>TOTAL TAGIHAN</td>
          <td class="totals-value">Rp{{ number_format($order->grand_total, 0, ',', '.') }}</td>
        </tr>
      </table>
      <div style="clear: both;"></div>
    </div>

    <!-- Footer Info -->
    <div style="margin-top: 30px;">
      <div class="payment-method-label">Metode Pembayaran</div>
      <div class="payment-method-value">{{ $order->paymentMethod ? $order->paymentMethod->name : '-' }}</div>

      <div style="margin-top: 10px;">
        <div class="payment-method-label">Status Pembayaran</div>
        <div style="margin-top: 5px;">
          @if ($order->payment_status == 'paid')
            <span class="payment-status-badge payment-status-paid">✓ LUNAS</span>
          @else
            <span class="payment-status-badge payment-status-unpaid">! BELUM LUNAS</span>
          @endif
        </div>
      </div>

      <div class="insurance-info">
        <span class="insurance-logo">HITS</span>
        <span>Asuransi Pengiriman {{ config('app.name') }}</span>
      </div>
    </div>

    <div class="footer">
      <p>Invoice ini sah dan diproses oleh komputer</p>
      <p>Silakan hubungi <strong>{{ config('app.name') }} Care</strong> apabila kamu membutuhkan bantuan.</p>
    </div>
  </div>
</body>

</html>
