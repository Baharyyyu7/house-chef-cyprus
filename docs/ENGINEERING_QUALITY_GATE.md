# Engineering Quality Gate

Her sprint kapanışında minimum kanıt: temiz migration/seed, ilgili ve tam test paketi, Pint, PHPStan, production build ve CI sonucu.

PR'larda açıklanacaklar: kullanıcı etkisi, test komutları/sonucu, migration ve geri dönüş etkisi, API sözleşmesi, güvenlik ve erişilebilirlik etkisi. Production'a yönelik değişiklikler ayrıca yedek, health check ve rollback planı gerektirir.

Kalite kapısı; çalışmayan yeni özelliği sırf planlanan bir otomasyon henüz yok diye tamamlanmış saymaz. Yeni scanner'lar önce bilgi amaçlı çalışır, gerçekçi bir baseline sonrası zorunlu hale gelir.
# Engineering Quality Gate

Her push ve pull request; bağımlılık kurulumu, temiz migration/iki seed, PHPUnit, Pint, PHPStan, Vite build, Composer/npm audit, OpenAPI doğrulaması, environment validation, cache oluşturma, temel secret/yasak dosya ve belge bağlantısı taramalarından geçer. CI yalnızca okuma yetkisine sahiptir; fork PR’larda secret kullanılmaz.

Bu kalite kapısı Foundation E ile doğrulanmıştır; production deployment denetimi yerine geçmez.
