# Özgün Tarif Veri Programı

## Amaç

House Chef Cyprus için genişleyebilir, özgün, doğrulanabilir ve lisans açısından güvenli bir tarif kataloğu kurmaktır. Program, üçüncü taraf sitelerden tarif metni, sistematik malzeme listesi, miktar, yapılış adımı, görsel, yorum veya yazar bilgisi kopyalamaz.

## Mevcut katalog durumu

Faz 3 tamamlandı: sürümlü editoryal veri kümesinde 50 tarif `published` ve `verified` durumundadır; her tarif için bir provenance kaydı, household/canonical ölçüler ve kontrollü placeholder medya durumu bulunur. Faz 3A'nın 20 tariflik ara hedefi, tamamlanmış Faz 3 tarafından aşılmıştır; veri kaybı oluşturmayacak şekilde **SUPERSEDED BY COMPLETED FAZ 3** kabul edilir.

## Kesin içerik güvenliği kuralları

- Tarif başlıkları ve kategori adları araştırma sinyali olabilir; metin, adım ve görsel kaynak değildir.
- Bir kaynağın miktar dizisi ya da adım sırası topluca çoğaltılmaz.
- robots.txt, kullanım koşulları, lisans ve istek sınırları incelenmeden otomasyon yapılmaz.
- Görsel yalnızca `original`, `licensed` veya izinli `generated` kaydıyla kullanılabilir. Aksi halde markalı placeholder kullanılır.
- Her yeni içerik sürümlü editoryal veri kümesinden, kendi cümleleriyle üretilir ve denetlenir.

## Kademeli hedef

| Parti | Hedef | Geçiş koşulu |
| --- | ---: | --- |
| 1 | Eski 12 tarif denetimi | Faz 3 içinde 50 kayıt standardına taşındı |
| 2 | Toplam 50 tarif | Tamamlandı: editoryal katalog, provenance ve yayın koruması |
| 3 | Toplam 150 tarif | Sonraki içerik genişletme backlog'u; henüz başlatılmadı |
| 4 | Toplam 500 tarif | Ölçek, görsel ve inceleme kapasitesi doğrulaması |
| 5 | 1.000+ tarif | Lisans, kalite ve operasyonel izleme olgunluğu |

Her partide data-quality, duplicate, household measure, nutrition, allergen, dietary, editoryal inceleme, test ve CI kontrolleri uygulanır.

## Veri yaşam döngüsü

`candidate → researched → drafted → editorial_review → partially_verified → verified → published`

`needs_review`, `rejected` ve `deprecated` durumları yayınlanmaz. Mevcut uygulamadaki public sorgular yalnızca `published` ve `verified` kayıtları döndürür.

## Import sözleşmesi

Versioned editoryal veri kümesi `database/editorial/first_50.php` ile başlar. Komutlar:

```bash
php artisan recipes:import-editorial --validate-only
php artisan recipes:import-editorial --dry-run --batch=1
php artisan recipes:import-editorial --json
php artisan recipes:completeness-report --json
```

Import, mevcut tarifi slug üzerinden günceller; yeni sürümde duplicate oluşturmaz. Yeni dış kaynak connector'ı ya da genişletilmiş import bu program için ayrıca onaylanmadan başlatılmaz.

## Onay kapısı

Bu belgeler yalnızca araştırma ve editoryal hazırlıktır. İlk 150 tarif planı onaylanmadan yeni tarif importu, dış kaynak entegrasyonu veya toplu veri toplama yapılmaz.
