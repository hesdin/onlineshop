<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <tr>
            <td style="padding: 24px;">
                <h2 style="margin: 0 0 12px 0; color: #0f172a;">Reset Password</h2>
                <p style="margin: 0 0 12px 0;">Kami menerima permintaan untuk mengatur ulang password akunmu.</p>

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px;">Reset Password</a>
                </p>

                <p style="margin: 0 0 12px 0;">Tautan ini berlaku selama 1 jam. Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser:</p>
                <p style="word-break: break-all; font-size: 12px; color: #475569;">{{ $resetUrl }}</p>

                <p style="margin-top: 20px; color: #475569;">Jika kamu tidak meminta reset password, abaikan email ini.</p>
                <p style="margin-top: 8px; color: #94a3b8;">Terima kasih.</p>
            </td>
        </tr>
    </table>
</body>
</html>
