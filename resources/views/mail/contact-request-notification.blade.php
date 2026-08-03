<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Yeni iletişim talebi</title></head>
<body style="margin:0;background:#f7f1e8;color:#18221b;font-family:Arial,sans-serif">
<div style="display:none;max-height:0;overflow:hidden">Yeni iletişim talebi {{ $contactRequest->public_id }}</div>
<div style="max-width:640px;margin:0 auto;padding:32px 20px">
    <div style="border-top:5px solid #ff6b00;background:#fff;border-radius:14px;padding:28px">
        <p style="margin:0 0 6px;color:#ff6b00;font-weight:700;letter-spacing:.12em">HOUSE CHEF CYPRUS</p>
        <h1 style="margin:0 0 22px;font-size:26px">Yeni iletişim talebi</h1>
        <p><strong>Talep no:</strong> {{ $contactRequest->public_id }}</p>
        <p><strong>Konu:</strong> {{ config('contact-requests.subjects.'.$contactRequest->subject) }}</p>
        <p><strong>Gönderen:</strong> {{ $contactRequest->displayName() }} · {{ $contactRequest->email }}</p>
        <div style="margin:22px 0;padding:18px;border-radius:10px;background:#f7f1e8;white-space:pre-wrap">{{ $contactRequest->message }}</div>
        <p><a href="{{ route('admin.contact-requests.show', $contactRequest) }}" style="display:inline-block;padding:12px 18px;border-radius:9px;background:#ff6b00;color:#fff;text-decoration:none;font-weight:700">Talebi güvenli panelde aç</a></p>
        <p style="margin:20px 0 0;color:#66736b;font-size:12px">Bu e-posta yalnız House Chef Cyprus yönetim işlemleri içindir. Hassas yanıtları e-posta ile paylaşmayın.</p>
    </div>
</div>
</body>
</html>
