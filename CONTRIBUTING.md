# Katkı

Her değişiklik test, Pint, PHPStan ve production build ile doğrulanmalıdır. Secret, yerel veritabanı ve backup commit edilmez. PR’lar CI geçmeden merge edilmez; major Dependabot güncellemeleri otomatik merge edilmez.

Branch protection ayarı GitHub yetkisi gerektirir: `main` için PR zorunluluğu, başarılı `CI / quality` kontrolü, force-push engeli ve yönetici bypass sınırlaması etkinleştirilmelidir.
