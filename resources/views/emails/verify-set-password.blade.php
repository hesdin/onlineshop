<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi & Setel Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; padding: 24px;">
    @php
        $variant = $variant ?? 'default';
        $isAdminCreated = in_array($variant, ['admin_created', 'admin_seller'], true);
        $isCustomerSelf = $variant === 'customer_self';
        $roleText = $user->hasRole('seller') ? 'sebagai penjual di TP-PKK Marketplace.' : 'di TP-PKK Marketplace.';
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <tr>
            <td style="padding: 24px;">
                <h2 style="margin: 0 0 12px 0; color: #0f172a;">Halo, {{ $user->name }}</h2>
                @if($isCustomerSelf)
                    <p style="margin: 0 0 12px 0;">Terima kasih sudah mendaftar {{ $roleText }}</p>
                @elseif($isAdminCreated)
                    <p style="margin: 0 0 12px 0;">Kami membuat akun untukmu {{ $roleText }}</p>
                @else
                    <p style="margin: 0 0 12px 0;">Kami membuat akun untukmu di TP-PKK Marketplace.</p>
                @endif
                <p style="margin: 0 0 12px 0;">Silakan verifikasi email dan setel password baru dengan tombol di bawah:</p>

                <p style="margin: 0 0 16px 0;">
                    <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 12px 18px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px;">Verifikasi & Setel Password</a>
                </p>

                <p style="margin: 0 0 12px 0;">Tautan ini berlaku selama 3 hari. Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser:</p>
                <p style="word-break: break-all; font-size: 12px; color: #475569;">{{ $verificationUrl }}</p>

                <p style="margin-top: 20px; color: #475569;">Jika kamu merasa tidak membuat akun ini, abaikan email ini.</p>
                <p style="margin-top: 8px; color: #94a3b8;">Terima kasih.</p>
            </td>
        </tr>
    </table>
</body>
</html>
