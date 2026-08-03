<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Yeni Üye Kaydı</title></head>
<body style="margin:0;background:#f7f1e8;color:#18221b;font-family:Arial,sans-serif">
<div style="max-width:640px;margin:0 auto;padding:32px 20px">
    <div style="border-top:5px solid #ff6b00;background:#fff;border-radius:14px;padding:28px">
        <p style="margin:0 0 6px;color:#ff6b00;font-weight:700;letter-spacing:.12em">HOUSE CHEF CYPRUS</p>
        <h1 style="margin:0 0 22px;font-size:26px">Yeni üye kaydı</h1>
        <table role="presentation" style="width:100%;border-collapse:collapse;font-size:15px;line-height:1.5">
            @php($missing = 'Belirtilmedi')
            <tr><td style="padding:7px 0;color:#66736b">Üye referansı</td><td style="padding:7px 0;font-weight:700">{{ $member->public_id }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Ad soyad</td><td style="padding:7px 0">{{ trim($member->first_name.' '.$member->last_name) ?: $member->name }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">E-posta</td><td style="padding:7px 0">{{ $member->email }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Telefon</td><td style="padding:7px 0">{{ $member->phone ?: $missing }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Ülke</td><td style="padding:7px 0">{{ $member->country ?: $missing }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Şehir / bölge</td><td style="padding:7px 0">{{ $member->city_or_region ?: $missing }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Dil</td><td style="padding:7px 0">{{ mb_strtoupper($member->preferred_language ?: 'tr') }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Kayıt zamanı</td><td style="padding:7px 0">{{ $member->created_at?->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">E-posta doğrulama</td><td style="padding:7px 0">{{ $member->hasVerifiedEmail() ? 'Doğrulandı' : 'Bekliyor' }}</td></tr>
            <tr><td style="padding:7px 0;color:#66736b">Kayıt kaynağı</td><td style="padding:7px 0">{{ $member->registration_source ?: $missing }}</td></tr>
        </table>
        <p style="margin:24px 0 0"><a href="{{ route('admin.users.show', ['user' => $member->public_id]) }}" style="display:inline-block;padding:12px 18px;border-radius:9px;background:#ff6b00;color:#fff;text-decoration:none;font-weight:700">Üye profilini aç</a></p>
        <p style="margin:20px 0 0;color:#66736b;font-size:12px">Bu operasyonel bildirim pazarlama izni anlamına gelmez.</p>
    </div>
</div>
</body>
</html>
