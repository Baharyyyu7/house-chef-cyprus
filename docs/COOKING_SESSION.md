# Cooking Session

Bir yemek oturumu kullanıcıya ve tarife bağlıdır. Aynı kullanıcı/tarif çifti için devam eden
veya duraklatılmış oturum yeniden kullanılır. Oturumlar `in_progress`, `paused`, `completed`
ve `abandoned` durumlarını destekler.

Tamamlanan adımlar `cooking_session_steps` içinde saklanır ve geri alınabilir. İlerleme oranı
yalnızca zorunlu adımlara göre hesaplanır; isteğe bağlı adımlar ilerlemeyi yapay olarak artırmaz.
Web rotaları public ULID kullanır ve controller, oturumun yalnızca sahibi tarafından
görüntülenmesini veya değiştirilmesini doğrular.
