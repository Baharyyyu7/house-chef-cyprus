<?php

declare(strict_types=1);

return [
    'title' => 'Besin Değerlerinin Tahmini Olması Ne Anlama Gelir?',
    'excerpt' => 'Tariflerdeki kalori ve makro değerleri neden kesin değildir? Hesaplamanın kaynakları, sapma sebepleri ve bu sayıların hangi amaçla kullanılabileceği.',
    'meta_title' => 'Tarif Besin Değerleri Neden Tahminidir?',
    'meta_description' => 'Kalori ve makro hesaplarındaki sapmaların sebepleri: malzeme değişkenliği, pişirme kaybı, porsiyon farkı ve veri tabanı farklılıkları.',
    'primary_keyword' => 'besin değeri hesaplama',
    'secondary_keywords' => ['tarif kalori hesabı', 'makro besin', 'kalori doğruluğu', 'besin veri tabanı'],
    'body' => <<<'MARKDOWN'
Tarif sayfalarında gördüğünüz kalori ve makro değerleri, ölçülmüş değil hesaplanmış sayılardır. Bu, onları değersiz yapmaz ama nasıl kullanılacaklarını bilmek gerekir.

**Önemli uyarı:** Bu yazı genel bilgilendirme amaçlıdır ve tıbbi ya da diyetetik tavsiye değildir. Tıbbi bir beslenme planı izliyorsanız, diyabet gibi kesin karbonhidrat sayımı gerektiren bir durumunuz varsa veya klinik bir hedefiniz varsa, bir hekime ya da diyetisyene danışın.

## Sayı nereden geliyor

Bir tarifin besin değeri şöyle hesaplanır:

1. Her malzemenin miktarı belirlenir.
2. Her malzemenin 100 gramdaki besin değeri bir veri tabanından alınır.
3. Miktarla çarpılıp toplanır.
4. Porsiyon sayısına bölünür.

Bu yöntem mantıklıdır ve pratikte işe yarar. Ama her adımda belirsizlik birikir.

## Sapmanın altı kaynağı

### 1. Malzeme değişkenliği

"Orta boy soğan" 100 g da olabilir 180 g da. "Bir avuç ceviz" kişiye göre değişir. Aynı marka olmayan iki yoğurdun yağ oranı farklıdır.

Doğal ürünlerde değişkenlik daha da yüksektir: bir elmanın şeker oranı olgunluğuna, mevsimine ve çeşidine göre belirgin biçimde değişir.

### 2. Veri tabanı farklılıkları

Farklı besin veri tabanları aynı malzeme için farklı değerler verir. Sebepler: ölçüm yöntemi, örneklem, coğrafya, hesaplama yaklaşımı. Aynı "beyaz peynir" için iki kaynak arasında %20 fark görmek olağandır.

### 3. Pişirme kayıpları ve kazançları

Pişirme sırasında:
- **Su kaybolur** → 100 gramdaki yoğunluk artar.
- **Yağ emilir veya damlar** → kızartmada artar, ızgarada azalır.
- **Vitaminler bozulur** → C vitamini ve bazı B vitaminleri ısıya duyarlıdır.
- **Haşlama suyuna geçiş olur** → suda çözünen besinler döküldüğünde kaybolur.

Hesaplamalar genellikle **çiğ** malzeme değerlerini kullanır. Kızartılmış bir sebzenin gerçek yağ içeriği, tarifte yazan yağın ne kadarının emildiğine bağlıdır ve bu ölçülmemiştir.

### 4. Porsiyon belirsizliği

"4 porsiyon" ifadesi, yemeğin dört eşit parçaya bölündüğünü varsayar. Gerçekte porsiyonlar eşit olmaz ve kişinin aldığı miktar tarifte varsayılandan farklıdır.

Bu, tek başına %30-40 sapma yaratabilir — diğer bütün kaynaklardan daha büyük bir etkidir.

### 5. Eksik veri

Bazı malzemelerin veri tabanında karşılığı yoktur veya eksiktir. Özellikle yerel ürünler, ev yapımı malzemeler ve baharatlar için veri seyrektir. Bu durumda ya benzer bir malzemenin değeri kullanılır ya da o kalem hesaba katılmaz.

### 6. Hazırlık farkları

Kabuk soyuldu mu? Yağ süzüldü mü? Et yağlı mı yağsız mı? Tarifte "tavuk" yazması, derili mi derisiz mi olduğunu söylemez ve fark belirgindir.

## Sapma ne kadar

Titizlikle hazırlanmış tarif hesaplamalarında bile **%20-25 sapma** normal kabul edilir. Porsiyon belirsizliği eklendiğinde bu %40'a çıkabilir.

Karşılaştırma için: paketli gıdaların etiketlerinde de yasal olarak belirli bir tolerans payı bulunur; laboratuvarda ölçülen değerler bile tek bir kesin sayı değildir.

## Bu sayılar ne işe yarar

Tahmini olmaları onları kullanışsız yapmaz. Doğru kullanım şekilleri:

**Karşılaştırma için iyidir.** İki tarif arasında hangisinin daha kalorili olduğunu görmek güvenilirdir; ikisi de aynı yöntemle hesaplandığı için sapma yönü aynıdır.

**Büyüklük sırası için iyidir.** Bir yemeğin 200 mü 600 kalori mi olduğunu bilmek anlamlıdır.

**Eğilim takibi için iyidir.** Haftalık ortalamanız artıyor mu azalıyor mu sorusuna cevap verir.

**Kesin sayım için uygun değildir.** İnsülin dozu hesaplamak gibi kritik kullanımlar için tarif hesaplamalarına güvenilmemelidir.

## Daha doğru sonuç istiyorsanız

- **Tartın.** "Bir orta boy" yerine gram kullanmak en büyük belirsizliği kaldırır.
- **Kendi markanızın etiketini kullanın.** Genel veri tabanı yerine kullandığınız ürünün değeri daha doğrudur.
- **Pişmiş ağırlığı ölçün.** Toplam pişmiş ağırlığı tartıp porsiyonunuzu tartarsanız porsiyon belirsizliği kalkar.
- **Yağı ölçün.** "Göz kararı yağ" en büyük tek sapma kaynaklarından biridir; bir yemek kaşığı zeytinyağı yaklaşık 120 kaloridir.

## Sitedeki gösterim

House Chef Cyprus tariflerinde besin değerleri porsiyon başına gösterilir ve yanında bir **tamamlanma yüzdesi** ile **güven skoru** bulunur. Bu iki sayı, hesaplamanın ne kadar veriye dayandığını ve ne kadar tahmin içerdiğini şeffaf biçimde belirtir.

Ayrıca porsiyon sayısını değiştirdiğinizde değerler yeniden hesaplanır. Porsiyon ölçekleme hakkında ayrıntı için [tariflerde porsiyon ölçekleme](/blog/tariflerde-porsiyon-olcekleme) yazısına bakabilirsiniz.

Alerjen bilgilerinin nasıl okunması gerektiği konusunda [alerjen etiketleri nasıl okunur](/blog/alerjen-etiketleri-nasil-okunur) yazısı ayrıntı içerir.
MARKDOWN,
    'faq' => [
        [
            'question' => 'Tariflerdeki kalori değerleri ne kadar doğru?',
            'answer' => 'Titizlikle hazırlanmış hesaplamalarda bile %20-25 sapma normal kabul edilir; porsiyon belirsizliği eklendiğinde bu %40\'a kadar çıkabilir. Bu sayılar karşılaştırma ve büyüklük sırası için güvenilirdir, ancak kesin sayım gerektiren tıbbi kullanımlar için uygun değildir.',
        ],
        [
            'question' => 'Neden aynı yemek için farklı kaynaklarda farklı kalori yazıyor?',
            'answer' => 'Farklı besin veri tabanları ölçüm yöntemi, örneklem ve coğrafyaya göre farklı değerler verir; aynı malzeme için kaynaklar arasında %20 fark olağandır. Ayrıca tarifteki malzeme miktarları, hazırlık farkları ve porsiyon varsayımları da kaynaktan kaynağa değişir.',
        ],
        [
            'question' => 'Besin değeri hesabını nasıl daha doğru hâle getirebilirim?',
            'answer' => 'En etkili adım tartmaktır: "bir orta boy" yerine gram kullanın. Ayrıca kendi markanızın etiket değerlerini kullanın, yağı göz kararı değil ölçerek ekleyin (bir yemek kaşığı zeytinyağı yaklaşık 120 kalori) ve toplam pişmiş ağırlığı tartıp kendi porsiyonunuzu tartın.',
        ],
        [
            'question' => 'Pişirme besin değerini değiştirir mi?',
            'answer' => 'Değiştirir. Su kaybı 100 gramdaki yoğunluğu artırır, kızartmada yağ emilir, ızgarada damlar, C vitamini gibi ısıya duyarlı besinler bozulur ve haşlama suyuna geçen besinler su dökülünce kaybolur. Hesaplamalar genellikle çiğ malzeme değerlerine dayandığı için bu farklar yansımaz.',
        ],
        [
            'question' => 'Diyabet gibi durumlarda bu değerlere güvenebilir miyim?',
            'answer' => 'Kesin karbonhidrat sayımı gerektiren durumlarda tarif hesaplamalarına güvenilmemelidir. Bu sayılar genel bilgilendirme içindir ve tıbbi tavsiye yerine geçmez. Tıbbi bir beslenme planı izliyorsanız hekiminize veya diyetisyeninize danışın.',
        ],
    ],
];
