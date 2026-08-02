# Kişiselleştirme mimarisi

Kişiselleştirme açık rıza ile başlar. `PersonalizationService` yalnız kullanıcının kendi profili, favorileri, geçerli dolabı ve geri bildirimi üzerinden deterministik sıralama yapar. Öneri adayları yalnız `published` ve `verified` tariflerden gelir; alerjen ve hariç tutulan malzemeler hard constraint'tir. Skor yalnız sıralama içindir ve API/UI'a verilmez.

Her cache anahtarı kullanıcı public ID'si içerir. Opt-out, profil değişikliği ve veri silme cache'i hemen geçersiz kılar. Yönetim ekranı yalnız toplulaştırılmış sayıları gösterir.
