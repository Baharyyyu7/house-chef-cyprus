<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Talebinize yanıt</title></head>
<body style="margin:0;background:#f7f1e8;color:#18221b;font-family:Arial,sans-serif">
<div style="display:none;max-height:0;overflow:hidden">House Chef Cyprus iletişim talebinize yanıt verdi.</div>
<div style="max-width:640px;margin:0 auto;padding:32px 20px">
    <div style="border-top:5px solid #ff6b00;background:#fff;border-radius:14px;padding:28px">
        <p style="margin:0 0 6px;color:#ff6b00;font-weight:700;letter-spacing:.12em">HOUSE CHEF CYPRUS</p>
        <h1 style="margin:0 0 22px;font-size:26px">Talebinize yanıt</h1>
        <p>Merhaba {{ $contactRequest->first_name }},</p>
        <div style="white-space:pre-wrap;line-height:1.65">{{ $body }}</div>
        <p style="margin-top:24px;color:#66736b">Talep numaranız: {{ $contactRequest->public_id }}</p>
        <p style="margin-top:28px;font-size:13px"><a href="{{ url('/yasal-bilgilendirme#iletisim-ve-geri-bildirim') }}" style="color:#c84f00">İletişim ve yasal bilgilendirme</a> · <a href="{{ url('/yasal-bilgilendirme#gizlilik-ve-kisisel-veriler') }}" style="color:#c84f00">Gizlilik</a></p>
        <p style="color:#66736b;font-size:12px">House Chef Cyprus · info@housechefcyprus.com</p>
    </div>
</div>
</body>
</html>
