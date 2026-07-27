# Editoryal Tarif Şablonu

Her yeni tarif, aşağıdaki alanlar tamamlanmadan yayına alınmaz.

## Kimlik ve sınıflandırma

- `public_id`, başlık, slug, alternatif adlar
- mutfak, bölge, kategori ve alt kategori
- sezon, zorluk, porsiyon ve süreler

## Pişirme içeriği

- kısa ve uzun özgün açıklama
- ekipman, malzeme grupları, household ölçü, canonical karşılık, hazırlama notu
- numaralı özgün adımlar, süre ve uygun olduğunda sıcaklık
- püf noktaları, sık hatalar, alternatif malzemeler
- saklama, tekrar ısıtma, dondurma ve servis önerisi

## Sağlık ve uygunluk

- alerjen analizi ve override gerekçesi
- diyet uyumu ve belirsizlik durumu
- malzeme/tarif besin verisi, basis, origin, confidence ve completeness

## Güven kaydı

- kaynak/provenance: `source_type`, `source_name`, `source_url`, `accessed_at`
- lisans, field-level provenance, editoryal notlar
- `verification_status`, `confidence`, `reviewed_at`, `reviewed_by`, `last_checked_at`
- görsel durumu, lisansı, atfı ve inceleme zamanı

Public yüzeyde yalnız güvenli kaynak özeti gösterilir. İç editoryal not, teknik kanıt ve hassas araştırma ayrıntıları API veya public şablona çıkmaz.
