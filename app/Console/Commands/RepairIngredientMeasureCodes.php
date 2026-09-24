<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RecipeIngredient;
use App\Models\Unit;
use Illuminate\Console\Command;

/**
 * 2962 malzeme satırının 404'ünde display_measure_code hiçbir birime karşılık
 * gelmiyordu: kod yerine okunabilir ad yazılmıştı ("yemek kaşığı" yerine
 * "yemek_kasigi"), 16'sında da ölçünün yanına parantezli bir not sıkışmıştı
 * ("gram (Zırh kıyması)"). Eşleşmeyen kodda birim hiç basılmaz; tarif
 * sayfasında "1 Pul Biber" görünür ve okuyucu ne kadar koyacağını bilemez.
 *
 * Yönetim paneli bu alanı units.code'a karşı doğruluyor; hatalı satırlar
 * doğrulamayı atlayan toplu içerik akışından geldi. Komut adı koda çevirir,
 * parantezli notu display_note alanına taşır.
 */
final class RepairIngredientMeasureCodes extends Command
{
    protected $signature = 'recipes:repair-measure-codes {--apply : Değişikliği yaz, aksi hâlde yalnız rapor}';

    protected $description = 'Birim koduna karşılık gelmeyen malzeme ölçülerini onarır.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        /** @var array<string,string> $koduyla */
        $koduyla = Unit::query()->where('is_active', true)->pluck('code', 'code')->all();
        /** @var array<string,string> $adiyla */
        $adiyla = [];
        foreach (Unit::query()->where('is_active', true)->get(['code', 'name']) as $unit) {
            // Aynı ada birden çok kod düşebiliyor ("yk" ve "yemek_kasigi"); uzun
            // olan okunabilir kodu tercih ederiz.
            $ad = mb_strtolower((string) $unit->name, 'UTF-8');
            if (! isset($adiyla[$ad]) || mb_strlen((string) $unit->code) > mb_strlen($adiyla[$ad])) {
                $adiyla[$ad] = (string) $unit->code;
            }
        }

        $onarilan = 0;
        $cozulemeyen = [];

        foreach (RecipeIngredient::query()->whereNotNull('display_measure_code')->where('display_measure_code', '!=', '')->cursor() as $satir) {
            $ham = trim((string) $satir->display_measure_code);

            if (isset($koduyla[$ham])) {
                continue;
            }

            $not = null;
            $olcu = $ham;

            if (preg_match('/^(.*?)\s*\(([^)]*)\)\s*$/u', $ham, $parca) === 1) {
                $olcu = trim($parca[1]);
                $not = trim($parca[2]);
            }

            $kod = $koduyla[$olcu] ?? $adiyla[mb_strtolower($olcu, 'UTF-8')] ?? null;

            if ($kod === null) {
                $cozulemeyen[$ham] = ($cozulemeyen[$ham] ?? 0) + 1;

                continue;
            }

            $onarilan++;

            if ($apply) {
                $satir->forceFill([
                    'display_measure_code' => $kod,
                    'display_note' => $not !== null && $not !== '' ? $not : $satir->display_note,
                ])->save();
            }
        }

        foreach ($cozulemeyen as $ham => $adet) {
            $this->line(sprintf('  <fg=yellow>çözülemedi</> %-42s %d satır', '['.$ham.']', $adet));
        }

        $this->newLine();
        $this->info(sprintf('%d satır onarılabilir, %d farklı ölçü çözülemedi.', $onarilan, count($cozulemeyen)));

        if (! $apply && $onarilan > 0) {
            $this->comment('Yazmak için --apply ekleyin.');
        }

        return self::SUCCESS;
    }
}
