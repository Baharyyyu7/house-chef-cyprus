# Data Quality Scoring V2

Skor, yalnız açık ve açıklanabilir `DataQualityIssue` kayıtlarından üretilir. Her kategori 100 puandan başlar; açık issue şiddeti puan düşürür: critical 25, error 12, warning 4 ve info 1 puan. Kategori puanları sıfırın altına inmez.

Genel skor, kategori puanlarının ağırlıklı ortalamasıdır: içerik bütünlüğü %16, veri güveni %14, ölçümler %12, nutrition %12, alerjenler %9, diyet uygunluğu %8, medya %14, provenance %10 ve operasyonel QA %5. Yalnız gerçek trend geçmiş skor kaydı varsa gösterilir; aksi halde trend iddiası yapılmaz.

Placeholder medya, düşük güvenli/demo dönüşümler, düşük güvenli veya tahmine dayalı nutrition ve eksik yerel browser QA fixture’ları ayrı issue’lar üretir. Böylece yer tutucu katalog veya doğrulanmamış dönüşümler 100/100 sonucu veremez. Issue detayı yalnız public kimlik, güvenli özet, önerilen işlem ve maskelenmiş metadata sunar.
# AI görsel prompt doğruluğu

`ai_image_recipe_fidelity_blocked` bir tarifin adıyla uyumlu temel malzeme kaydı eksik olduğunda `data_trust` kategorisinde hata olarak açılır. Bu kapı, eksik veya çelişkili tarif verisini gerçekçi görünen ama yanlış bir AI görseline dönüştürmeyi engeller. Issue, editoryal malzeme düzeltmesi sonrasında pilot planı yeniden senkronize edilip tarama çalıştırıldığında otomatik çözülür.
