# Hostinger Resource Budget

- Frontend build production sunucusunda çalıştırılmaz.
- Tek Laravel scheduler cron kullanılır; queue worker bounded ve `--stop-when-empty` modelindedir.
- Ağır SEO, medya ve içerik işleri çakışmayacak saatlere bölünür ve `withoutOverlapping` kilidi kullanır.
- Export, tarama ve medya işleri chunk/batch limitli kalır.
- Release artifact en az üç geri dönüş release'i korur; private backup/log retention sınırlıdır.
- Bilinmeyen CPU, inode ve connection limitleri ölçülmeden artırılmış paralellik etkinleştirilmez.
