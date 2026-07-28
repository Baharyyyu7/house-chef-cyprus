# Grafik Kütüphanesi Kararı

Admin V3 Part 1 için **Chart.js 4** seçildi.

| Kriter | Karar gerekçesi |
| --- | --- |
| Bakım/lisans | Aktif bakım ve MIT lisansı |
| Kapsam | Line, bar ve doughnut grafiklerini tek pakette sağlar |
| Entegrasyon | Blade canvas + Vite ile Alpine/Livewire'ı ikinci kez başlatmadan çalışır |
| Tema/motion | CSS semantic tokenlardan palette alır; reduced-motion altında animasyonu kapatır |
| Erişilebilirlik | Canvas yanında zorunlu metinsel özet ve anlamlı `aria-label` gösterilir |

Canvas bilgiye erişmenin tek yolu değildir. Veri yoksa canvas kurulmaz; dürüst empty state gösterilir. İhracat aksiyonu yalnız gerçek ihtiyaç oluştuğunda eklenir.
