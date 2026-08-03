<h1>Yeni iletişim talebi</h1>
<p><strong>Talep no:</strong> {{ $contactRequest->public_id }}</p>
<p><strong>Konu:</strong> {{ config('contact-requests.subjects.'.$contactRequest->subject) }}</p>
<p><strong>Gönderen:</strong> {{ $contactRequest->displayName() }} · {{ $contactRequest->email }}</p>
<p>{{ $contactRequest->message }}</p>
<p>Bu e-posta yalnız House Chef Cyprus yönetim işlemleri içindir.</p>
