<?php

declare(strict_types=1);

namespace App\Observers;

use App\Http\Controllers\LlmsTxtController;

/**
 * Tarif, yazı, çeviri ya da robots kaydı değişince llms.txt önbelleği silinir.
 *
 * Silmek ucuz; yeniden kurulum bir sonraki llms isteğinde tembel yapılır.
 * Yayından kaldırılan ya da noindex'e alınan adres korpusta bir gün
 * beklemesin.
 */
final class LlmsCacheObserver
{
    public function saved(): void
    {
        LlmsTxtController::onbellegiTemizle();
    }

    public function deleted(): void
    {
        LlmsTxtController::onbellegiTemizle();
    }
}
