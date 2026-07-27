# Veritabanı taşınabilirliği

Migration’lar Laravel Schema Builder, foreign key ve taşınabilir decimal/JSON kolonları kullanır. SQLite yerel geliştirme içindir; MySQL/PostgreSQL geçişinde gerçek sunucu üzerinde migration denemesi yapılmalıdır. SQLite’a özgü SQL, enum ve adlandırılmamış uzun index kullanılmaz.

Production veritabanı seçilmeden önce index adları, JSON sorguları ve backup prosedürü hedef sürümde CI/staging ile doğrulanır.
