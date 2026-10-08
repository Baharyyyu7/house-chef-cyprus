House Chef Cyprus - günlük operasyon özeti ({!! now()->format('Y-m-d H:i') !!})

Başarısız görevler ({!! count($ozet['failed_tasks']) !!}):
@forelse ($ozet['failed_tasks'] as $ad => $gorev)
- {!! $ad !!}: {!! $gorev['last_failed_at'] ?? '-' !!} | {!! $gorev['last_error'] ?? 'hata metni yok' !!}
@empty
- yok
@endforelse

Bayat görevler ({!! count($ozet['stale_tasks']) !!}) - beklenen sıklığın iki katından uzun süredir başarılı koşmadı:
@forelse ($ozet['stale_tasks'] as $ad => $gorev)
- {!! $ad !!}: son başarı {!! $gorev['last_success_at'] ?? 'hiç' !!}, beklenen her {!! $gorev['expected_interval_minutes'] !!} dk
@empty
- yok
@endforelse

Son 24 saatin hataları ({!! count($ozet['errors']) !!} tür):
@forelse ($ozet['errors'] as $hata)
- {!! $hata['count'] !!}x {!! $hata['message'] !!} (son: {!! $hata['last_seen_at'] !!})
@empty
- yok
@endforelse

Ayrıntı: php artisan app:scheduler-health --json
