<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            border-bottom: 3px solid #0ea5e9;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #0ea5e9;
            margin-bottom: 5px;
        }
        .header .subtitle {
            color: #64748b;
            font-size: 11px;
        }
        .invoice-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .invoice-info table {
            width: 100%;
        }
        .invoice-info td {
            padding: 5px;
        }
        .invoice-info td:first-child {
            font-weight: 600;
            width: 150px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .two-col {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 10px;
        }
        .col:first-child {
            padding-left: 0;
        }
        .col:last-child {
            padding-right: 0;
        }
        .box {
            background: #f8fafc;
            padding: 12px;
            border-radius: 4px;
            min-height: 100px;
        }
        .box h3 {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .box p {
            margin: 4px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table thead {
            background: #0ea5e9;
            color: white;
        }
        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .items-table th {
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table tbody tr:hover {
            background: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            float: right;
            width: 350px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals td:first-child {
            font-weight: 600;
        }
        .totals td:last-child {
            text-align: right;
        }
        .grand-total {
            background: #0ea5e9;
            color: white;
            font-size: 14px;
            font-weight: 700;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .instructions {
            background: #eff6ff;
            border-left: 4px solid #0ea5e9;
            padding: 15px;
            margin-top: 20px;
        }
        .instructions h4 {
            color: #0369a1;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .instructions p {
            color: #475569;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'TP-PKK Marketplace') }}</h1>
            <div class="subtitle">Invoice Pemesanan</div>
        </div>

        <div class="invoice-info">
            <table>
                <tr>
                    <td>No. Invoice:</td>
                    <td><strong>{{ $order->order_number }}</strong></td>
                </tr>
                <tr>
                    <td>Tanggal Pemesanan:</td>
                    <td>{{ $order->ordered_at?->format('d F Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td>Status Pembayaran:</td>
                    <td>
                        <span class="status-badge status-pending">
                            {{ $order->payment_status === 'pending' ? 'Menunggu Pembayaran' : ucfirst($order->payment_status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Metode Pembayaran:</td>
                    <td>{{ $order->paymentMethod->name }}</td>
                </tr>
            </table>
        </div>

        <div class="two-col">
            <div class="col">
                <div class="box">
                    <h3>Informasi Pembeli</h3>
                    <p><strong>{{ $order->user->name }}</strong></p>
                    <p>{{ $order->user->email }}</p>
                    @if($order->address)
                        <p style="margin-top: 8px;">{{ $order->address->recipient_name }}</p>
                        <p>{{ $order->address->phone }}</p>
                        <p>{{ $order->address->address_line }}</p>
                        <p>{{ $order->address->city }}, {{ $order->address->province }}</p>
                        <p>{{ $order->address->postal_code }}</p>
                    @endif
                </div>
            </div>
            <div class="col">
                <div class="box">
                    <h3>Informasi Penjual</h3>
                    <p><strong>{{ $order->store->name }}</strong></p>
                    @if($order->store->phone)
                        <p>{{ $order->store->phone }}</p>
                    @endif
                    @if($order->store->address_line)
                        <p style="margin-top: 8px;">{{ $order->store->address_line }}</p>
                    @endif
                    @if($order->store->city || $order->store->province)
                        <p>{{ $order->store->city }}@if($order->store->city && $order->store->province), @endif{{ $order->store->province }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Detail Pesanan</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Produk</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 20%;">Harga Satuan</th>
                        <th class="text-right" style="width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                {{ $item->product->name }}
                                @if($item->note)
                                    <br><small style="color: #64748b;">Catatan: {{ $item->note }}</small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals">
                <table>
                    <tr>
                        <td>Subtotal</td>
                        <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Ongkos Kirim</td>
                        <td>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->discount_total > 0)
                        <tr>
                            <td>Diskon</td>
                            <td>- Rp {{ number_format($order->discount_total, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>Total</td>
                        <td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>

        @if($order->paymentMethod->metadata && isset($order->paymentMethod->metadata['instructions']))
            <div class="instructions">
                <h4>Instruksi Pembayaran - {{ $order->paymentMethod->name }}</h4>
                <p>{{ $order->paymentMethod->metadata['instructions'] }}</p>
                @if($order->paymentMethod->code === 'manual_transfer')
                    <p style="margin-top: 10px;"><strong>Silakan hubungi toko untuk informasi rekening transfer.</strong></p>
                @endif
            </div>
        @endif

        <div class="footer">
            <p>Invoice ini dibuat secara otomatis oleh sistem {{ config('app.name') }}</p>
            <p>Untuk pertanyaan, silakan hubungi toko terkait.</p>
        </div>
    </div>
</body>
</html>
