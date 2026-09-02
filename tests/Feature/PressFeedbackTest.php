<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class PressFeedbackTest extends TestCase
{
    public function test_every_interactive_surface_answers_on_press(): void
    {
        // Basma geri bildirimi bırakma anında değil, basıldığı anda gelmeli.
        // Sitenin yerleşik deseni bunu kartlara ve düğmelere veriyordu; bu
        // oturumda eklediğim kapanış kapıları ve taahhüt kanıt bağlantıları
        // dışarıda kalmıştı ve sayfa değişene kadar ölü duruyorlardı.
        $stil = (string) file_get_contents(resource_path('css/app.css'));

        foreach ([
            '.hc-v3-btn:active',
            '.hc-v3-card--interactive:active',
            '.hc-kapanis__kapilar a:active',
            '.hc-taahhut__kanit:active',
        ] as $secici) {
            $this->assertStringContainsString($secici, $stil, $secici.' basma geri bildirimi vermiyor.');
        }
    }

    public function test_press_feedback_respects_reduced_motion(): void
    {
        // Azaltılmış hareket "geri bildirim yok" demek değil; ölçek değişimi
        // kalkıyor, geri kalanı duruyor.
        $stil = (string) file_get_contents(resource_path('css/app.css'));

        $bolum = mb_strstr($stil, '.hc-kapanis__kapilar a:hover');

        $this->assertStringContainsString('prefers-reduced-motion', (string) $bolum);
        $this->assertStringContainsString('.hc-kapanis__kapilar a:active,', (string) $bolum);
    }
}
