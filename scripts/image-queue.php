<?php

declare(strict_types=1);

/**
 * scripts/image-queue.php
 * 
 * Dağıtık ve atomik görsel üretim kuyruğu (Distributed Lock Queue).
 * Birden fazla AI ajanının (5 AI Worker) çakışmadan, çift üretim yapmadan
 * ("Bu yemeği ben aldım" diyerek) paralel çalışmasını sağlar.
 * 
 * Komutlar:
 *   php scripts/image-queue.php init                     # Kuyruğu başlat / senkronize et
 *   php scripts/image-queue.php claim --worker="AI-1"    # Bir sonraki boştaki yemeği kilitle
 *   php scripts/image-queue.php complete --slug="..."    # Başarıyla tamamlandı olarak işaretle
 *   php scripts/image-queue.php release --slug="..."     # İptal et, havuza geri bırak
 *   php scripts/image-queue.php status                   # Canlı kuyruk durumunu göster
 */

$root = dirname(__DIR__);
$queueFile = $root . '/storage/app/media_generation_queue.json';
$lockFile = $root . '/storage/app/media_generation_queue.lock';
$docFile = $root . '/docs/GORSEL_URETIM_KUYRUK_LISTESI.md';

$action = 'status';
$options = [];
for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if (str_starts_with($arg, '--')) {
        $parts = explode('=', substr($arg, 2), 2);
        $options[$parts[0]] = $parts[1] ?? true;
    } elseif (!str_starts_with($arg, '-')) {
        if ($action === 'status') {
            $action = $arg;
        }
    }
}

function withLock(string $lockFile, callable $callback) {
    $fp = fopen($lockFile, 'c+');
    if (!$fp) {
        throw new RuntimeException("Lock dosyası açılamadı: {$lockFile}");
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException("Kuyruk kilitlenemedi.");
    }
    try {
        return $callback();
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

function loadQueue(string $queueFile): array {
    if (!file_exists($queueFile)) {
        return ['updated_at' => date('c'), 'total' => 0, 'completed' => 0, 'claimed' => 0, 'pending' => 0, 'items' => []];
    }
    $content = file_get_contents($queueFile);
    return json_decode($content, true) ?: [];
}

function saveQueue(string $queueFile, array $data): void {
    $data['updated_at'] = date('c');
    $total = count($data['items'] ?? []);
    $completed = 0;
    $claimed = 0;
    $pending = 0;

    foreach ($data['items'] as $item) {
        if ($item['status'] === 'completed') $completed++;
        elseif ($item['status'] === 'claimed') $claimed++;
        else $pending++;
    }

    $data['total'] = $total;
    $data['completed'] = $completed;
    $data['claimed'] = $claimed;
    $data['pending'] = $pending;

    file_put_contents($queueFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function generatePrompt(array $item): string {
    $title = $item['title'];
    $category = $item['category'] ?? 'Yemek';

    $cuisineNote = match($item['cuisine_slug'] ?? '') {
        'kibris-mutfagi' => 'traditional Cypriot cuisine specialty',
        'akdeniz-mutfagi' => 'authentic Mediterranean culinary creation',
        'lubnan-mutfagi' => 'Levantine Lebanese specialty',
        'italyan-mutfagi' => 'classic Italian dish',
        default => 'traditional Turkish home-cooked dish',
    };

    return sprintf(
        "Professional food photography of %s, an authentic %s (%s). Served on a clean ceramic plate or rustic stone platter, beautifully garnished, natural side lighting from window, shallow depth of field, appetizing texture, vibrant culinary presentation, 4:3 aspect ratio, studio food photography, no text, no watermark, no borders, no collage.",
        $title,
        $cuisineNote,
        $category
    );
}

function syncMarkdownDoc(string $docFile, array $queue): void {
    $total = $queue['total'];
    $completed = $queue['completed'];
    $claimed = $queue['claimed'];
    $pending = $queue['pending'];
    $pct = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

    $md = "# 🍽️ Görsel Üretim Dağıtık Koordinasyon ve Canlı Kilit Sistemi\n\n";
    $md .= "> **Canlı İlerleme:** `%{$pct}` Tamamlandı | **Tamamlanan:** `{$completed}` / `{$total}` | **İşleniyor:** `{$claimed}` | **Boşta:** `{$pending}`\n";
    $md .= "> **Son Güncelleme:** " . date('Y-m-d H:i:s') . "\n";
    $md .= "> **Kurallar:** [docs/GORSEL_URETIM_MASTER_PROMPT.md](GORSEL_URETIM_MASTER_PROMPT.md) ve [docs/AJAN_CALISMA_KURALLARI.md](AJAN_CALISMA_KURALLARI.md)\n\n";

    $md .= "## 1. Dağıtık Kilit Protokolü (\"Ben Yapacağım\" Mekanizması)\n";
    $md .= "Birden fazla yapay zeka ajanının aynı anda güvenle çalışabilmesi için:\n";
    $md .= "1. **İş Alma (Claim):** `php scripts/image-queue.php claim --worker=\"AI-Worker-1\"`\n";
    $md .= "   - Sistem boştaki ilk yemeği seçer, kilidi o ajana verir ve durumu `claimed` yapar.\n";
    $md .= "   - Diğer hiçbir ajan bu yemeği alamaz; diğer ajanlar bir sonraki boştaki yemeği alır.\n";
    $md .= "2. **Üretim ve Yükleme:** Ajan görseli üretir, `scripts/upload-recipe-image.sh` ile 1920x1620 PNG olarak sunucuya aktarır.\n";
    $md .= "3. **Tamamlama (Complete):** `php scripts/image-queue.php complete --slug=\"{slug}\" --worker=\"AI-Worker-1\"`\n";
    $md .= "   - Durum `completed` olarak kaydedilir ve ajana yeni iş verilir.\n";
    $md .= "4. **Zaman Aşımı / Hata (Timeout):** 15 dakikadan uzun süre kilitli kalan işler otomatik olarak havuza iade edilir.\n\n";

    $md .= "## 2. Canlı Kuyruk Listesi\n\n";
    $md .= "| # | Durum | Kilitleyen Ajan | Tarif Başlığı | Mutfak | Kategori | Dosya Adı (`media-import/recipes/`) |\n";
    $md .= "|:---:|:---:|:---|:---|:---|:---|:---|\n";

    $i = 1;
    foreach ($queue['items'] as $item) {
        $badge = match($item['status']) {
            'completed' => '`✓ Bitti`',
            'claimed' => '`⏳ İşleniyor`',
            default => '`[ ] Boşta`',
        };
        $worker = $item['claimed_by'] ? "**{$item['claimed_by']}**" : "-";

        $md .= sprintf(
            "| %d | %s | %s | **%s** | %s | %s | `%s` |\n",
            $i++,
            $badge,
            $worker,
            $item['title'],
            $item['cuisine'],
            $item['category'],
            $item['target_filename']
        );
    }

    file_put_contents($docFile, $md);
}

// ACTION HANDLERS

switch ($action) {
    case 'init':
        withLock($lockFile, function() use ($root, $queueFile, $docFile) {
            $jsonSource = $root . '/storage/app/missing_recipes_for_images.json';
            if (!file_exists($jsonSource)) {
                echo "HATA: storage/app/missing_recipes_for_images.json bulunamadı!\n";
                exit(1);
            }
            $raw = json_decode(file_get_contents($jsonSource), true);
            $queue = loadQueue($queueFile);
            $items = $queue['items'] ?? [];

            // Sort Kıbrıs first, then others
            usort($raw, function($a, $b) {
                if ($a['cuisine_slug'] === 'kibris-mutfagi' && $b['cuisine_slug'] !== 'kibris-mutfagi') return -1;
                if ($a['cuisine_slug'] !== 'kibris-mutfagi' && $b['cuisine_slug'] === 'kibris-mutfagi') return 1;
                return strcmp($a['category'], $b['category']) ?: strcmp($a['title'], $b['title']);
            });

            $added = 0;
            foreach ($raw as $r) {
                $slug = $r['slug'];
                if (!isset($items[$slug])) {
                    $item = [
                        'id' => $r['id'],
                        'public_id' => $r['public_id'],
                        'slug' => $slug,
                        'title' => $r['title'],
                        'cuisine' => $r['cuisine'],
                        'cuisine_slug' => $r['cuisine_slug'],
                        'category' => $r['category'],
                        'category_slug' => $r['category_slug'],
                        'status' => 'pending',
                        'claimed_by' => null,
                        'claimed_at' => null,
                        'completed_at' => null,
                        'target_filename' => 'recipe--' . $slug . '.png',
                    ];
                    $item['prompt'] = generatePrompt($item);
                    $items[$slug] = $item;
                    $added++;
                }
            }

            $queue['items'] = $items;
            saveQueue($queueFile, $queue);
            syncMarkdownDoc($docFile, $queue);
            echo "Kuyruk başlatıldı / güncellendi. Toplam: " . count($items) . " tarif ({$added} yeni eklendi).\n";
        });
        break;

    case 'claim':
        $worker = $options['worker'] ?? 'Worker-' . getmypid();
        withLock($lockFile, function() use ($queueFile, $docFile, $worker) {
            $queue = loadQueue($queueFile);
            $items = $queue['items'] ?? [];
            $now = time();
            $claimedItem = null;

            // 1. Check for stale claims (> 15 minutes) and release them
            foreach ($items as $slug => &$it) {
                if ($it['status'] === 'claimed' && !empty($it['claimed_at'])) {
                    $claimTime = strtotime($it['claimed_at']);
                    if (($now - $claimTime) > 900) { // 15 mins
                        $it['status'] = 'pending';
                        $it['claimed_by'] = null;
                        $it['claimed_at'] = null;
                    }
                }
            }
            unset($it);

            // 2. Find first pending item
            foreach ($items as $slug => &$item) {
                if ($item['status'] === 'pending') {
                    $item['status'] = 'claimed';
                    $item['claimed_by'] = $worker;
                    $item['claimed_at'] = date('c');
                    $claimedItem = $item;
                    break;
                }
            }
            unset($item);

            if (!$claimedItem) {
                echo json_encode(['success' => false, 'message' => 'Kuyrukta boştaki yemek kalmadı (tümü alındı ya da tamamlandı).'], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                return;
            }

            $queue['items'] = $items;
            saveQueue($queueFile, $queue);
            syncMarkdownDoc($docFile, $queue);

            echo json_encode([
                'success' => true,
                'action' => 'claimed',
                'worker' => $worker,
                'message' => "Yemek seçildi: {$claimedItem['title']} -> {$claimedItem['target_filename']} ({$worker} tarafından kilitlendi)",
                'recipe' => [
                    'slug' => $claimedItem['slug'],
                    'title' => $claimedItem['title'],
                    'cuisine' => $claimedItem['cuisine'],
                    'category' => $claimedItem['category'],
                    'target_filename' => $claimedItem['target_filename'],
                    'prompt' => $claimedItem['prompt'],
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        });
        break;

    case 'complete':
        $slug = $options['slug'] ?? null;
        $worker = $options['worker'] ?? 'Worker';
        if (!$slug) {
            echo "HATA: --slug parametresi zorunludur.\n";
            exit(1);
        }
        withLock($lockFile, function() use ($queueFile, $docFile, $slug, $worker) {
            $queue = loadQueue($queueFile);
            if (!isset($queue['items'][$slug])) {
                echo json_encode(['success' => false, 'message' => "Slug bulunamadı: {$slug}"], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                return;
            }

            $queue['items'][$slug]['status'] = 'completed';
            $queue['items'][$slug]['completed_at'] = date('c');
            $queue['items'][$slug]['claimed_by'] = $worker;

            saveQueue($queueFile, $queue);
            syncMarkdownDoc($docFile, $queue);

            echo json_encode([
                'success' => true,
                'action' => 'completed',
                'worker' => $worker,
                'slug' => $slug,
                'message' => "Görsel teslimatı tamamlandı: {$slug}"
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        });
        break;

    case 'release':
        $slug = $options['slug'] ?? null;
        if (!$slug) {
            echo "HATA: --slug parametresi zorunludur.\n";
            exit(1);
        }
        withLock($lockFile, function() use ($queueFile, $docFile, $slug) {
            $queue = loadQueue($queueFile);
            if (!isset($queue['items'][$slug])) {
                echo json_encode(['success' => false, 'message' => "Slug bulunamadı: {$slug}"], JSON_UNESCAPED_UNICODE) . PHP_EOL;
                return;
            }

            $queue['items'][$slug]['status'] = 'pending';
            $queue['items'][$slug]['claimed_by'] = null;
            $queue['items'][$slug]['claimed_at'] = null;

            saveQueue($queueFile, $queue);
            syncMarkdownDoc($docFile, $queue);

            echo json_encode([
                'success' => true,
                'action' => 'released',
                'slug' => $slug,
                'message' => "Yemek havuza iade edildi: {$slug}"
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
        });
        break;

    case 'status':
    default:
        $queue = loadQueue($queueFile);
        $total = $queue['total'] ?? 0;
        $completed = $queue['completed'] ?? 0;
        $claimed = $queue['claimed'] ?? 0;
        $pending = $queue['pending'] ?? 0;
        $pct = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        echo "========================================================\n";
        echo "   HOUSE CHEF CYPRUS - GÖRSEL ÜRETİM CANLI DURUMU       \n";
        echo "========================================================\n";
        echo sprintf("  Toplam Eksik Tarif : %d adet\n", $total);
        echo sprintf("  Tamamlanan (Yüklü) : %d adet (%%%s)\n", $completed, $pct);
        echo sprintf("  İşleniyor (Kilitli): %d adet\n", $claimed);
        echo sprintf("  Boşta Bekleyen     : %d adet\n", $pending);
        echo "--------------------------------------------------------\n";
        if ($claimed > 0) {
            echo "  Aktif Kilitler:\n";
            foreach ($queue['items'] as $item) {
                if ($item['status'] === 'claimed') {
                    echo sprintf("    - [%s] %s (%s)\n", $item['claimed_by'], $item['title'], $item['slug']);
                }
            }
            echo "--------------------------------------------------------\n";
        }
        break;
}
