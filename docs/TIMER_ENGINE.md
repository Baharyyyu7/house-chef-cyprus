# Timer Engine

`cooking_timers` bir cooking session içinde birden fazla zamanlayıcı tutar. Zamanlayıcılar
`ready`, `running`, `paused`, `completed` ve `cancelled` durumlarını destekler. Süreler pozitif
tamsayı saniye olarak saklanır.

Kalan süre, çalışırken `started_at` ve kaydedilmiş kalan süreden hesaplanır. Bu yaklaşım sayfa
yenilendiğinde veya kullanıcı geri döndüğünde süreyi tarayıcı belleğine bağlı kalmadan yeniden
hesaplar. Pause/resume/complete işlemleri `CookingTimerService` üzerinden yapılır.
