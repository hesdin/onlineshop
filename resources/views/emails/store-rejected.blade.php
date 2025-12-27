<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Verifikasi Toko Ditolak</title>
</head>

<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
    style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <tr>
      <td style="padding: 24px;">
        <h2 style="margin: 0 0 12px 0; color: #0f172a;">Halo, {{ $user->name }}!</h2>
        <p style="margin: 0 0 16px 0;">Mohon maaf, pengajuan verifikasi toko <strong>{{ $storeName }}</strong> belum
          dapat kami setujui.</p>

        @if ($reason)
          <div
            style="background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #991b1b;">Catatan dari tim verifikasi:</p>
            <p style="margin: 0; color: #7f1d1d;">{{ $reason }}</p>
          </div>
        @endif

        <p style="margin: 0 0 16px 0;">Silakan periksa kembali dokumen Anda dan ajukan ulang verifikasi.</p>

        <p style="margin: 0 0 16px 0;">
          <a href="{{ $documentsUrl }}"
            style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px;">Periksa
            Dokumen</a>
        </p>

        <p style="margin: 0 0 12px 0;">Jika Anda memiliki pertanyaan, silakan hubungi tim support kami.</p>

        <p style="margin: 0 0 12px 0;">Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser:</p>
        <p style="word-break: break-all; font-size: 12px; color: #475569;">{{ $documentsUrl }}</p>

        <p style="margin-top: 20px; color: #94a3b8;">Terima kasih.</p>
      </td>
    </tr>
  </table>
</body>

</html>
