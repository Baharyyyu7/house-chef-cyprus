# İlk 50 Tarif Kataloğu

Bu katalog versioned editoryal veri setidir (`database/editorial/first_50.php`). Her kayıt kaynak ve ölçü taslağıyla içe aktarılır; **researched** durumu insan doğrulaması yerine geçmez. Yayın için `verified` editoryal kontrol zorunludur.

| Tarif | Mutfak | Kategori | Durum |
|---|---|---|---|
| Kıbrıs Usulü Fırın Tavuk | Kıbrıs | Ana yemek | researched |
| Zeytinyağlı Nohutlu Pilav | Türk | Sebze | researched |
| Limonlu Akdeniz Makarnası | İtalyan | Ana yemek | researched |
| Sebzeli Teriyaki Tavuk | Japon | Ana yemek | researched |
| Fırın Sebzeli Taco Kasesi | Meksika | Ana yemek | researched |
| Yoğurtlu Nohut Salatası | Lübnan | Salata | researched |
| Domatesli Tavuk Sote | Türk | Ana yemek | researched |
| Limonlu Otlu Patates | Kıbrıs | Sebze | researched |
| Sarımsaklı Zeytinyağlı Makarna | İtalyan | Ana yemek | researched |
| Pirinçli Tavuk Çorbası | Türk | Çorba | researched |
| Nohutlu Akdeniz Kasesi | Lübnan | Salata | researched |
| Fırında Baharatlı Tavuk | Meksika | Ana yemek | researched |

Kalan 38 kayıt; kahvaltı (5), çorba (4), ana yemek (6), sebze (3), hamur işi (5), tatlı (6), salata/meze (4) ve pratik atıştırmalık (5) dengesini tamamlar. Tam başlık, ana malzemeler, zorluk, kaynak durumu ve görsel placeholder ataması veri setinde idempotent olarak tanımlanır.

## Yayın kuralı

Katalogdaki bir tarif ancak kaynakları insan tarafından kontrol edilip `verified` olduğunda, nutrition/allergen/dietary sonuçları incelendiğinde ve completeness kontrol listesi `publishable` döndürdüğünde yayınlanır.
