# Yerelleştirme Stratejisi

Aktif içerik dili ve fallback `tr`'dir; dinamik veriler ana tablolarda Türkçe kalır. Gelecek çoklu dil fazında `recipe_translations` ve `ingredient_translations` tabloları; `locale`, çevrilmiş slug, başlık/açıklama/metin ve doğrulama durumu taşır. Ana tablo dil-bağımsız kimlik, süre, kaynak ve ilişki alanlarını korur. Bu tablolar Faz 2'de oluşturulmaz.

Güncelleme: İngilizce sürüm için `recipe_translations` ve `blog_post_translations` tabloları eklendi; URL, hreflang ve içerik akışı [Uluslararası SEO](INTERNATIONAL_SEO.md) belgesindedir.
