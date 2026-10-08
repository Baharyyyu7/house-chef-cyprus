<?php

declare(strict_types=1);

namespace App\Services\Scheduler;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Zamanlayıcı HTTP'den tetiklendiğinde uygulama komutlarının Artisan'da
 * kayıtlı olmasını garanti eder.
 *
 * Neden var: üretimde `/_zamanlayici` ucunun içinden yapılan
 * `Artisan::call('<özel komut>')` CommandNotFoundException atıyordu; aynı
 * komut aynı sunucuda CLI'da listeleniyor ve çalışıyordu. Zamanlanmış
 * görevlerin neredeyse hepsi `Artisan::call` ile koştuğu için yedek, yedek
 * doğrulaması ve blog yayını gibi işler sessizce koşmuyor olabilirdi.
 *
 * Laravel komutları dosya sisteminden keşfederken sınıf adını
 * `realpath()` ile hesaplıyor (Kernel::commandClassFromFile). LiteSpeed'in
 * lsphp süreçleri realpath önbelleğini sürümler arası taşıyabiliyor; yol
 * çözümü saparsa sınıf adı tutmuyor ve komut sessizce listeden düşüyor.
 * Burada sınıf adı yalnız göreli yoldan türetiliyor ve eksik kalan her
 * komut doğrudan Artisan örneğine ekleniyor. Komutlar zaten kayıtlıysa
 * (CLI, testler) hiçbir şey değişmiyor.
 */
class ConsoleCommandRegistrar
{
    /**
     * @return list<class-string<Command>> Eksik bulunup bu çağrıda kaydedilen komutlar
     */
    public function ensureRegistered(): array
    {
        $kernel = app(ConsoleKernel::class);

        // registerCommand yalnız çerçevenin kendi çekirdeğinde var; testte
        // sahte bir çekirdek takılıysa kaydedilecek bir Artisan da yok.
        if (! $kernel instanceof FoundationConsoleKernel) {
            return [];
        }

        $kernel->bootstrap();

        $kayitli = [];
        foreach ($kernel->all() as $command) {
            $kayitli[$command::class] = true;
        }

        $eklenen = [];
        foreach ($this->expectedCommandClasses() as $class) {
            if (isset($kayitli[$class])) {
                continue;
            }

            try {
                $kernel->registerCommand(app($class));
                $eklenen[] = $class;
            } catch (Throwable $exception) {
                // Tek bir bozuk komut diğerlerinin kaydını engellememeli.
                report($exception);
            }
        }

        if ($eklenen !== []) {
            // Teşhis için: bu satır üretimde görünüyorsa kök neden hâlâ
            // duruyor demektir; görevler yine de koşar.
            Log::warning('zamanlayici.eksik_komutlar_kaydedildi', [
                'adet' => count($eklenen),
                'ornek' => array_slice(array_map(class_basename(...), $eklenen), 0, 5),
            ]);
        }

        return $eklenen;
    }

    /**
     * @return list<class-string<Command>>
     */
    private function expectedCommandClasses(): array
    {
        // schedule:run çerçeve komutu; tetikleyicinin ilk çağrısı o.
        $classes = [ScheduleRunCommand::class];
        $kok = app_path('Console/Commands');

        if (! is_dir($kok)) {
            return $classes;
        }

        foreach (Finder::create()->in($kok)->name('*.php')->files() as $file) {
            $class = 'App\\Console\\Commands\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (! class_exists($class) || ! is_subclass_of($class, Command::class) || (new ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }
}
