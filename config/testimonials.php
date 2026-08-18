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
 * 'name'  -> maddenin başlığı
 * 'role'  -> hangi konuya ait olduğu
 * 'text'  -> nerede görülebileceği
 */

return [
    'disclosure' => 'Buradaki maddeler kullanıcı yorumu değil, sitenin çalışma biçimine dair taahhütlerdir; her biri tarif ve yazı sayfalarında doğrulanabilir.',

    'home' => [
        [
            'name' => 'Onaysız görsel yayınlanmaz',
            'role' => 'Görsel bütünlüğü',
            'text' => 'Bir tarifin fotoğrafı yoksa yer tutucu açıkça belirtilir; ilgisiz bir yemek fotoğrafı o tarifin fotoğrafı gibi gösterilmez.',
            'image' => null,
        ],
        [
            'name' => 'Yapay zekâ görseli olduğu yazar',
            'role' => 'Görsel bütünlüğü',
            'text' => 'Üretilmiş bir görsel kullanıldığında tarif sayfasında bunu söyleyen bir not bulunur.',
            'image' => null,
        ],
        [
            'name' => 'Editoryal kontrol tarihi görünür',
            'role' => 'İçerik',
            'text' => 'Her tarifte son editoryal kontrolün ne zaman yapıldığı yazar; yapılmadıysa o da yazar.',
            'image' => null,
        ],
        [
            'name' => 'Alerjen bilgisi ayrı gösterilir',
            'role' => 'Beslenme',
            'text' => 'İçerir ve içerebilir ayrı ayrı listelenir, belirsizlik belirsiz olarak işaretlenir.',
            'image' => null,
        ],
        [
            'name' => 'Besin değerleri tahminse söylenir',
            'role' => 'Beslenme',
            'text' => 'Değerlerin hesaplanmış mı yoksa tahmini bileşen içerdiği mi, güven skoruyla birlikte belirtilir.',
            'image' => null,
        ],
        [
            'name' => 'Ölçüler ev ölçüsüne çevrilir',
            'role' => 'Mutfak',
            'text' => 'Porsiyon değiştiğinde miktarlar birlikte güncellenir; dönüşümün güven düzeyi kayıt altındadır.',
            'image' => null,
        ],
        [
            'name' => 'Elinizdekilerle eşleştirme',
            'role' => 'Mutfak',
            'text' => 'Malzeme dolabınızı tariflerle eşleştirir, eksikleri ve uygun alternatifleri açıkça gösterir.',
            'image' => null,
        ],
        [
            'name' => 'Reklam izni ölçüm izninden ayrıdır',
            'role' => 'Gizlilik',
            'text' => 'Ölçüme izin vermeniz reklam izni sayılmaz; iki tercih ayrı tutulur ve istediğiniz zaman değiştirilir.',
            'image' => null,
        ],
        [
            'name' => 'Premium üyeye reklam gösterilmez',
            'role' => 'Gizlilik',
            'text' => 'Ücretli üyelikte sitede hiçbir reklam isteği yapılmaz.',
            'image' => null,
        ],
    ],
];
