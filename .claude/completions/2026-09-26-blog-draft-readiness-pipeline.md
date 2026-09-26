# Blog taslak hazırlık hattı — 2026-09-26

Blog yönetimindeki yanıltıcı tek toplu yayın akışı üç ayrı adıma ayrıldı:
salt okunur taslak denetimi, mevcut metinden güvenli SEO alanı hazırlama ve
yalnız tüm kapıları geçen yazıları yayınlama.

`BlogDraftReadinessSnapshotService` yayınlanmamış kayıtları 100'lük parçalarla
okur, hazır/engelli sayılarını ve gruplanmış eksikleri verir; hiçbir kayıt
değiştirmez. `BlogDraftPreparationService` en fazla 100 kayıtta yalnız boş SEO
başlığı, uygun uzunlukta SEO açıklaması ve ana konuyu mevcut başlık/özetten
türetir. Kaynak, SSS, fact-check, özgünlük, editoryal onay ve insan onayı
uydurulmaz; durum ve yayın tarihi değiştirilmez. Tekrar çalıştırma idempotenttir.
Hazırlanan kayıtlar sonraki 100'lük çalışmada yeniden seçilmez; kuyruk ilerler.

Canlı ön denetim: 1.196 yayınlanmamış kayıt, 0 hazır, 1.196 engelli. En yüksek
eksikler: özgünlük denetimi 1.190, insan onayı 1.047, gerçek SSS 1.035, içerik
amacı 1.034, 900 kelime sınırı 1.031. Bu nedenle toplu yayın sırasında sıfır
sonuç alınması sistem hatası değil, içerik hazırlık eksikleridir.

Odak doğrulama: 22 test, 527 assertion geçti. Vite üretim derlemesi ve
`git diff --check` başarılı. Hiçbir taslak otomatik yayımlanmadı, dil
etkinleştirilmedi ve medya toplu onaylanmadı.
