<?php

declare(strict_types=1);

/*
 * Burada bir zamanlar dokuz uydurma kişi ve dokuz uydurma yorum vardı. Altına
 * "temsili içeriktir" notu düşülmüştü, ama uydurma isim ve uydurma alıntı,
 * altında ne yazarsa yazsın, sayfada sosyal kanıt gibi okunuyor.
 *
 * Tasarım korundu; içerik değişti. Buradaki her madde sitede doğrulanabilir
 * ve kod tarafında karşılığı var — yani okuyucu iddiayı sınayabilir. Gerçek
 * kullanıcı yorumu geldiğinde, yalnız izinli ve doğrulanmış olanlar buraya
 * eklenir; uydurma isim bir daha girmez.
 *
 * Her maddenin 'proof' alanı, taahhüdün yazılı olduğu sayfayı gösteriyor:
 * iddia edip kanıtını göstermemek, iddiayı zayıflatır.
 *
 * 'name'  -> maddenin başlığı
 * 'role'  -> hangi konuya ait olduğu
 * 'text'  -> nerede görülebileceği
 */

return [
    'disclosure' => 'Bunlar kullanıcı yorumu değil, sitenin çalışma biçimine dair taahhütler. Her maddenin altındaki bağlantı, o taahhüdün yazılı olduğu sayfaya gider.',

    'home' => [
        [
            'key' => 'unverified_images',
            'name' => 'Onaysız görsel yayınlanmaz',
            'role' => 'Görsel bütünlüğü',
            'proof' => ['page' => 'gorsel-ve-medya-politikasi', 'label' => 'Görsel ve medya politikası'],
            'text' => 'Bir tarifin fotoğrafı yoksa yer tutucu açıkça belirtilir; ilgisiz bir yemek fotoğrafı o tarifin fotoğrafı gibi gösterilmez.',
            'image' => null,
        ],
        [
            'key' => 'ai_images',
            'name' => 'Yapay zekâ görseli olduğu yazar',
            'role' => 'Görsel bütünlüğü',
            'proof' => ['page' => 'gorsel-ve-medya-politikasi', 'label' => 'Görsel ve medya politikası'],
            'text' => 'Üretilmiş bir görsel kullanıldığında tarif sayfasında bunu söyleyen bir not bulunur.',
            'image' => null,
        ],
        [
            'key' => 'editorial_date',
            'name' => 'Editoryal kontrol tarihi görünür',
            'role' => 'İçerik',
            'proof' => ['page' => 'editorial-ilkeler', 'label' => 'Editoryal ilkeler'],
            'text' => 'Her tarifte son editoryal kontrolün ne zaman yapıldığı yazar; yapılmadıysa o da yazar.',
            'image' => null,
        ],
        [
            'key' => 'allergen_info',
            'name' => 'Alerjen bilgisi ayrı gösterilir',
            'role' => 'Beslenme',
            'proof' => ['page' => 'besin-degeri-aciklamasi', 'label' => 'Besin değeri açıklaması'],
            'text' => 'İçerir ve içerebilir ayrı ayrı listelenir, belirsizlik belirsiz olarak işaretlenir.',
            'image' => null,
        ],
        [
            'key' => 'estimated_nutrition',
            'name' => 'Besin değerleri tahminse söylenir',
            'role' => 'Beslenme',
            'proof' => ['page' => 'besin-degeri-aciklamasi', 'label' => 'Besin değeri açıklaması'],
            'text' => 'Değerlerin hesaplanmış mı yoksa tahmini bileşen içerdiği mi, güven skoruyla birlikte belirtilir.',
            'image' => null,
        ],
        [
            'key' => 'home_measurements',
            'name' => 'Ölçüler ev ölçüsüne çevrilir',
            'role' => 'Mutfak',
            'proof' => ['page' => 'tarif-dogrulama-politikasi', 'label' => 'Tarif doğrulama politikası'],
            'text' => 'Porsiyon değiştiğinde miktarlar birlikte güncellenir; dönüşümün güven düzeyi kayıt altındadır.',
            'image' => null,
        ],
        [
            'key' => 'pantry_matching',
            'name' => 'Elinizdekilerle eşleştirme',
            'role' => 'Mutfak',
            'proof' => ['page' => 'tarif-dogrulama-politikasi', 'label' => 'Tarif doğrulama politikası'],
            'text' => 'Malzeme dolabınızı tariflerle eşleştirir, eksikleri ve uygun alternatifleri açıkça gösterir.',
            'image' => null,
        ],
        [
            'key' => 'ad_consent_separation',
            'name' => 'Reklam izni ölçüm izninden ayrıdır',
            'role' => 'Gizlilik',
            'proof' => ['page' => 'gizlilik', 'label' => 'Gizlilik'],
            'text' => 'Ölçüme izin vermeniz reklam izni sayılmaz; iki tercih ayrı tutulur ve istediğiniz zaman değiştirilir.',
            'image' => null,
        ],
        [
            'key' => 'ad_free_premium',
            'name' => 'Premium üyeye reklam gösterilmez',
            'role' => 'Gizlilik',
            'proof' => ['page' => 'gizlilik', 'label' => 'Gizlilik'],
            'text' => 'Ücretli üyelikte sitede hiçbir reklam isteği yapılmaz.',
            'image' => null,
        ],
    ],
];
