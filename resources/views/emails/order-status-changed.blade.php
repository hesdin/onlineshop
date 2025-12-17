<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Update Pesanan #{{ $order->order_number }}</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
    style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <tr>
      <td style="padding: 24px;">
        <h2 style="margin: 0 0 8px 0; color: #0f172a;">Update Pesanan</h2>
        <p style="margin: 0 0 20px 0; color: #64748b; font-size: 14px;">No. Pesanan:
          <strong>{{ $order->order_number }}</strong></p>

        <p style="margin: 0 0 12px 0;">Halo <strong>{{ $order->user?->name ?? 'Pelanggan' }}</strong>,</p>

        <p style="margin: 0 0 16px 0;">Pesanan Anda di <strong>{{ $storeName }}</strong> telah diperbarui.</p>

        <!-- Status Update Box -->
        <table width="100%" cellpadding="0" cellspacing="0"
          style="background: #f1f5f9; border-radius: 8px; margin-bottom: 20px;">
          <tr>
            <td style="padding: 16px;">
              <p
                style="margin: 0 0 8px 0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                Status Pesanan</p>
              <p style="margin: 0; font-size: 16px; font-weight: bold; color: #0f172a;">{{ $statusLabel }}</p>

              @if ($paymentStatusLabel)
                <p
                  style="margin: 12px 0 8px 0; font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                  Status Pembayaran</p>
                <p style="margin: 0; font-size: 16px; font-weight: bold; color: #0f172a;">{{ $paymentStatusLabel }}</p>
              @endif
            </td>
          </tr>
        </table>

        <!-- Status Message -->
        @if ($newStatus === 'processing')
          <p style="margin: 0 0 16px 0; color: #475569;">Pesanan Anda sedang diproses oleh penjual. Kami akan segera
            menyiapkan pesanan Anda.</p>
        @elseif($newStatus === 'shipped')
          <p style="margin: 0 0 16px 0; color: #475569;">Pesanan Anda sudah dikirim! Silakan pantau pengiriman Anda.</p>
          @if ($order->shipping_awb)
            <p style="margin: 0 0 16px 0; color: #475569;">No. Resi: <strong
                style="font-family: monospace;">{{ $order->shipping_awb }}</strong></p>
          @endif
        @elseif($newStatus === 'delivered')
          <p style="margin: 0 0 16px 0; color: #475569;">Pesanan Anda sudah sampai di tujuan. Mohon konfirmasi
            penerimaan barang.</p>
        @elseif($newStatus === 'completed')
          <p style="margin: 0 0 16px 0; color: #475569;">Pesanan Anda telah selesai. Terima kasih telah berbelanja!</p>
        @elseif($newStatus === 'cancelled')
          <p style="margin: 0 0 16px 0; color: #475569;">Pesanan Anda telah dibatalkan. Jika ini tidak sesuai harapan,
            silakan hubungi penjual.</p>
        @endif

        @if ($newPaymentStatus === 'paid')
          <p style="margin: 0 0 16px 0; color: #16a34a; font-weight: 600;">✓ Pembayaran Anda telah dikonfirmasi.</p>
        @endif

        <!-- Order Details -->
        <table width="100%" cellpadding="0" cellspacing="0"
          style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px;">
          <tr>
            <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
              <strong style="font-size: 14px;">Ringkasan Pesanan</strong>
            </td>
          </tr>
          @foreach ($order->items as $item)
            <tr>
              <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="color: #0f172a;">{{ $item->name }}</td>
                    <td style="text-align: right; color: #64748b; font-size: 14px;">{{ $item->quantity }}x</td>
                    <td style="text-align: right; color: #0f172a; font-weight: 600; width: 120px;">Rp
                      {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                  </tr>
                </table>
              </td>
            </tr>
          @endforeach
          <tr>
            <td style="padding: 12px 16px; background: #f8fafc;">
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-weight: bold; color: #0f172a;">Total</td>
                  <td style="text-align: right; font-weight: bold; color: #2563eb; font-size: 16px;">Rp
                    {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

        <p style="margin: 0 0 8px 0; color: #64748b; font-size: 14px;">Jika ada pertanyaan, silakan hubungi penjual.</p>

        <p style="margin-top: 20px; color: #94a3b8;">Terima kasih,<br><strong
            style="color: #0f172a;">{{ $storeName }}</strong></p>
      </td>
    </tr>
  </table>

  <p style="text-align: center; margin-top: 24px; color: #94a3b8; font-size: 12px;">
    Email ini dikirim secara otomatis. Jangan membalas email ini.
  </p>
</body>

</html>
