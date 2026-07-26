# Deployment Automation Backlog

Bu backlog production fazına kadar uygulanmaz.

- Staging deploy: gerçek barındırma, domain ve environment seçildiğinde.
- Production deploy: staging sağlık kontrolü ve rollback prosedürü kanıtlandığında.
- Environment secrets/protection: deploy hesabı ve sorumlular tanımlandığında.
- Backup/restore: production veritabanı ve saklama politikası onaylandığında.
- Health check/rollback: uygulama `/health` sözleşmesi ve gözlemlenebilirlik hedefi kesinleştiğinde.
- Scheduled denetimler: production bağımlılıkları ve uyarı kanalı belirlendiğinde.
