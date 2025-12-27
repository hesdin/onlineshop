<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dokumen Seller Baru</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
    style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <tr>
      <td style="padding: 24px;">
        <h2 style="margin: 0 0 12px 0; color: #0f172a;">Halo Admin!</h2>
        <p style="margin: 0 0 16px 0;">Ada pengajuan verifikasi dokumen seller baru yang perlu ditinjau:</p>

        <div style="background: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Nama Toko</td>
              <td style="padding: 6px 0; font-weight: 600; text-align: right;">{{ $store->name }}</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Jenis Toko</td>
              <td style="padding: 6px 0; font-weight: 600; text-align: right;">{{ ucfirst($store->type ?? '-') }}</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Pemilik</td>
              <td style="padding: 6px 0; font-weight: 600; text-align: right;">{{ $ownerName }}</td>
            </tr>
            <tr>
              <td style="padding: 6px 0; color: #64748b; font-size: 14px;">Email</td>
              <td style="padding: 6px 0; font-weight: 600; text-align: right;">{{ $ownerEmail }}</td>
            </tr>
          </table>
        </div>

        <p style="margin: 0 0 16px 0;">
          <a href="{{ $actionUrl }}"
            style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px;">Review
            Dokumen</a>
        </p>

        <p style="margin: 0 0 12px 0;">Silakan verifikasi dokumen seller dalam 1-3 hari kerja.</p>

        <p style="margin: 0 0 12px 0;">Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser:</p>
        <p style="word-break: break-all; font-size: 12px; color: #475569;">{{ $actionUrl }}</p>

        <p style="margin-top: 20px; color: #94a3b8;">Terima kasih.</p>
      </td>
    </tr>
  </table>
</body>

</html>
