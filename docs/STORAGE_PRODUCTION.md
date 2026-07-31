# Production depolama

Private review/generation çıktıları public diskten ayrıdır. Yalnız admin onaylı medya public görünürlük kazanabilir.

Seçenekler:

1. **VPS local storage:** `storage/app/private` ve `storage/app/public` düzenli offsite backup ile saklanır. `storage:link` deploy sırasında doğrulanır.
2. **S3/R2 uyumlu object storage:** `FILESYSTEM_DISK=s3` kullanılır; bucket, endpoint ve credential sunucuda tanımlanır. Sırları depoya koymayın.

Temporary dosyalar ve private provider adayları retention/cleanup görevleriyle temizlenir. Branded fallback görselleri gerçek/onaylı medya yerine geçmez.
