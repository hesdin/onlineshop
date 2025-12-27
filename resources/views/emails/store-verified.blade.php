<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Toko Terverifikasi</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
    style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <tr>
      <td style="padding: 24px;">
        <h2 style="margin: 0 0 12px 0; color: #0f172a;">Halo, {{ $user->name }}!</h2>
        <p style="margin: 0 0 16px 0; font-size: 16px;">Kabar baik! Toko <strong>{{ $storeName }}</strong> telah
          berhasil diverifikasi oleh tim kami.</p>

        <div
          style="background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
          <p style="margin: 0 0 8px 0; font-weight: 600; color: #047857;">Toko Anda sekarang sudah dapat:</p>
          <ul style="margin: 0; padding-left: 20px; color: #065f46;">
            <li style="margin-bottom: 6px;">✅ Menerima pesanan dari customer</li>
            <li style="margin-bottom: 6px;">✅ Menampilkan produk di marketplace</li>
            <li style="margin-bottom: 6px;">✅ Memproses transaksi</li>
          </ul>
        </div>

        <p style="margin: 0 0 16px 0;">
          <a href="{{ $dashboardUrl }}"
            style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px;">Buka
            Dashboard</a>
        </p>

        <p style="margin: 0 0 12px 0;">Terima kasih telah bergabung dengan marketplace kami!</p>

        <p style="margin: 0 0 12px 0;">Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser:</p>
        <p style="word-break: break-all; font-size: 12px; color: #475569;">{{ $dashboardUrl }}</p>

        <p style="margin-top: 20px; color: #94a3b8;">Terima kasih.</p>
      </td>
    </tr>
  </table>
</body>

</html>
