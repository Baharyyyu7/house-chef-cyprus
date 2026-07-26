# Ölçeklenebilirlik Denetimi

## Kritik

- Tarif, malzeme ve metin alanları tek dillidir; 500 bin tarifte çeviri için kolon çoğaltmak yerine translation tabloları gerekir.
- Kaynak/lisans recipe seviyesinde tutuluyor; alan bazlı provenance ve import sürümü yoktur.
- SQLite arama sorgusu `whereRaw` ile süre topluyor; PostgreSQL'de türetilmiş/indekslenmiş arama alanlarına taşınmalıdır.

## Önemli

- Birim, besin, allerjen ve diyet verileri JSON yerine normalleştirilmiş, sürümlü tablolara ayrılmalıdır.
- Public API için dahili artan ID'ler yerine ULID public identifier kullanılmalıdır.
- Queue tabloları hazırdır; iş sınıfları, idempotency anahtarları, olaylar ve worker gözlemlenebilirliği Faz 2 altyapısında eklenmelidir.

## Yeterli olanlar

- Arama ve AI sağlayıcıları interface arkasındadır.
- Medya `disk/path` ile soyutlanmıştır; S3 diskine geçiş mümkündür.
- Controller'lar iş mantığını servis katmanına yönlendirir; SQLite migration'ları taşınabilir temel tipleri kullanır.

## Ertelenenler

Çeviri/provenance, aile profilleri, moderasyon, bildirimler, planlama ve öneri olayları ilgili domain fazında tasarlanacaktır; şimdi eklenmeleri kullanılmayan şema ve yanlış sınırlar üretir.
