# Ücretsiz yerel tarif görseli hattı

Bu proje, ücretli sağlayıcı çağrılarından bağımsız bir seçenek olarak ComfyUI + FLUX.1 Schnell için private-review odaklı bir adapter içerir. Adapter yalnız `http://127.0.0.1:8188` veya açıkça loopback olarak doğrulanan bir ComfyUI adresine bağlanır.

## Güvenlik ve yayın akışı

1. `recipe-images:comfyui-batch --dry-run` yalnız uygun tarifleri listeler.
2. Gerçek üretim, yalnız ComfyUI health ve workflow doğrulandıysa başlar.
3. Çıktılar önce private storage’a gider; otomatik yayın kapalıdır.
4. Yönetici, tarif uyumu, yazı/logo/watermark, tabak bütünlüğü ve tekrar görsel denetimini yaptıktan sonra onaylar.
5. Sadece onaylanan medya public görünür olur.

Model ağırlıkları, ComfyUI kurulumu, çıktı klasörü ve anahtarlar repository’ye eklenmez. Workflow `resources/workflows/comfyui/flux1-schnell-api.json` içindedir; model dosyası ismi yalnız yerel ComfyUI ortamında çözülür.

## Bu makinedeki güvenlik kararı

29 Temmuz 2026 denetiminde Apple Silicon cihazda 16 GB birleşik bellek ve yaklaşık 33 GiB boş disk bulundu. FLUX.1 Schnell’in model/çalışma belleği ve ComfyUI bağımlılıklarıyla 50 görsellik güvenilir toplu üretim için bu pay yeterli değildir. Bu nedenle ComfyUI veya model indirmesi başlatılmadı; gerçek görsel üretilmiş gibi işaretlenmez ve placeholder’lar korunur.

Uygun bir yerel makinede üretim öncesi `COMFYUI_ENABLED=true` ayarlanır, model lisansı/SHA-256 kaydedilir ve önce en fazla beş tariflik dry-run + review batch’i uygulanır.
