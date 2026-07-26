# Database Schema

## Recipe execution

- `recipe_steps`: Tarif adımları; `public_id`, `recipe_id`, benzersiz `position`, süre,
  sıcaklık, timer/optional işaretleri, ekipman ve rehber metinleri.
- `cooking_sessions`: Kullanıcının tarif oturumu, aktif adım, porsiyon anlık görüntüsü ve
  yaşam döngüsü zamanları.
- `cooking_session_steps`: Tamamlanan adım kayıtları; session/adım çifti benzersizdir.
- `cooking_timers`: Oturum içi çoklu zamanlayıcı, süre, kalan süre ve durum zamanları.

Tarif adım pozisyonu aynı tarifte benzersizdir. Tüm execution tabloları foreign key ve uygun
indexlerle kullanıcı/tarif oturumunu güvenli biçimde bağlar.
