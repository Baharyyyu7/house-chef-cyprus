# Tarif Araştırma Yöntemi

## Kaynak hiyerarşisi

1. Resmî kültür, turizm veya kamu kurumlarının yayınları.
2. Geleneksel yemek kitapları ve üniversite/kurum yayınları.
3. Açık lisanslı veri setleri; lisans ve atıf koşulları ayrıca kaydedilir.
4. Güvenilir beslenme veri tabanları; nutrition için tarif metni yerine ayrı kanıt kaynağıdır.
5. Ticari tarif platformları yalnızca konu, yaygın isim ve genel teknik sinyali için; metin, miktar, adım veya görsel kaynağı olarak kullanılmaz.

## Her aday için araştırma kaydı

- Kaynak türü, adı, URL'si ve erişim tarihi.
- Lisans/kullanım koşulu özeti.
- Genel ortak noktalar: temel malzemeler, oran aralığı, teknik, yaklaşık süre, bölgesel varyasyon ve servis biçimi.
- Çelişki, belirsizlik ve editoryal karar notu.
- `verification_status`, güven skoru, inceleyen ve son kontrol tarihi.

## Oran ve ölçü denetimi

- En az iki bağımsız kaynak türüyle **genel oran aralığı** karşılaştırılır.
- Ev ölçüsü ile canonical gram/ml karşılığı ingredient-specific dönüşümden gelir.
- Yoğunluğu bilinmeyen bardak/kaşık ölçüsü otomatik gramlaştırılmaz.
- Belirsiz değer `editorial_estimate` olarak kalır; doğrulanmış gibi işaretlenmez.
- Tuz, baharat, maya ve kabartıcılar için doğrusal ölçekleme uyarısı değerlendirilir.

## Benzerlik kontrolü

Yayın öncesinde editör; açıklama, adımlar ve notların tek bir kaynağa aşırı benzemediğini; başka House Chef tarifleriyle gereksiz tekrar içermediğini; malzeme dizisinin tek kaynağın sistematik kopyası olmadığını inceler. Yüksek benzerlikte içerik sıfırdan yeniden yazılır veya aday reddedilir.
