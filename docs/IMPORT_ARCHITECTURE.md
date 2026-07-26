# İçe Aktarma Mimarisi

Import Engine, Foundation V1 kapsamından bilinçli olarak çıkarılmıştır. Uygulama bu sürümde
yalnızca yerel, seed edilmiş içerikle çalışır; dış kaynaktan tarif çekmez ve kullanıcıya açık
bir import komutu veya yönetim ekranı sunmaz.

Korunan hazırlıklar:

- `DataSource` modeli ve tarifteki kaynak kimliği alanları, içerik provenance'ı için korunur.
- `ImportConnectorInterface` ve `NormalizedRecipeData`, gerçek bir kaynak seçildiğinde
  connector sözleşmesini başlatmak için korunur.
- `ImportRun` tablosu/modeli, geçmiş import denetimi için ayrılmıştır; aktif pipeline değildir.

Tam pipeline, doğrulama sonuçları, duplicate adayları, retry/backoff, failed import kaydı,
raw payload güvenliği ve import yönetimi Foundation V1.5 kapsamındadır. Ayrıntılı tetikleyici
ve kabul ölçütleri için `docs/FOUNDATION_V1_5_BACKLOG.md` dosyasına bakın.
