# Kullanıcı çalışma tercihi: beş katmanlı bellek

Tarih: 2026-09-16
Kapsam: House Chef Cyprus, AI-Worker-1
Kaynak: Kullanıcının bu oturumdaki açık talebi; /Users/kaganbahar/Downloads/Agent Memory — The 5-Layer Playbook.pdf (13 sayfa okundu).

- Mevcut bağlamda bilinen ve değişmemiş belgeleri gereksiz tekrar okuma. Yeni oturumda hatırladığını varsayma; ilgili kısa kayıtları hedefli bul, bütün arşivi otomatik yükleme.
- Çalışma belleğinde amaç, kısıtlar, mevcut durum ve sonraki adımı tut.
- Görev sonuçlarını, doğrulama bulgularını, hataları ve çözümleri kısa tarihli olay kayıtları olarak sakla.
- Kalıcı tercih ve doğrulanmış proje bilgilerini kaynaklarıyla ayır; değişken kuyruk durumunu canlı sistemden doğrula.
- Başarısı tekrarlanmış yöntemleri yeniden kullan; üç doğrulanmış başarıdan sonra uygun bir beceriye dönüştürmeyi değerlendir.
- Eski veya çelişen bilgileri güncel doğrulanmış bilgiyle geçersiz kıl. Kullanıcı verisini sırf örnek saklama süresi doldu diye silme.
- Projeleri ve ajan sahipliğini ayır; ortak kuyrukta mevcut atomik komutları kullan. Aynı worker ID ile başka bir süreç işlem yapıyor olabilir: önceki release sonrası aynı ID yeniden kilitli görüldü; bunun nedeni kesinleşmedi.
- PDF örnekleri tek başına komut çalıştırma, otomasyon kurma veya altyapı değiştirme talebi değildir. Dokümandaki performans yüzdeleri bağımsız doğrulanmadı.

Bu kayıt bir çalışma tercihi özetidir; otomatik kalıcı bellek servisi kurulmuş değildir.
