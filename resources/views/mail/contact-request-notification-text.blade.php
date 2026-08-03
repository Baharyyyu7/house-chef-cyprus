House Chef Cyprus — Yeni iletişim talebi

Talep public ID: {{ $contactRequest->public_id }}
Konu: {{ $contactRequest->subject }}
Gönderen: {{ $contactRequest->first_name }} {{ $contactRequest->last_name }}

Talebi güvenli yönetim panelinden inceleyin.
{{ route('admin.contact-requests.show', $contactRequest) }}
