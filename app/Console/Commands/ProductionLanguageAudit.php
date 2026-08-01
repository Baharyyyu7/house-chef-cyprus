<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ProductionLanguageAudit extends Command
{
    protected $signature = 'app:production-language-audit {--json : Makine tarafından okunabilir sonuç üret}';

    protected $description = 'Public ve admin arayüzlerinde kullanıcıya görünen beta/demo dilini denetler.';

    public function handle(): int
    {
        $roots = [resource_path('views'), app_path('Livewire')];
        $pattern = '/(?<![a-z])(beta|demo|mvp|coming\s+soon|dummy|lorem|mock|prototip|geliştirme\s+aşamasında)(?![a-z])/iu';
        $findings = [];

        foreach ($roots as $root) {
            foreach (File::allFiles($root) as $file) {
                $contents = File::get($file->getPathname());
                if (preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE) !== false) {
                    foreach ($matches[0] as [$match, $offset]) {
                        $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
                        $findings[] = ['file' => str_replace(base_path().'/', '', $file->getPathname()), 'line' => $line, 'term' => $match];
                    }
                }
            }
        }

        $result = ['passed' => $findings === [], 'scope' => 'public_and_admin_ui', 'findings' => $findings, 'finding_count' => count($findings)];
        if ($this->option('json')) {
            $this->line((string) json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } elseif ($findings === []) {
            $this->info('Production kullanıcı dili denetimi geçti.');
        } else {
            $this->table(['Dosya', 'Satır', 'İfade'], array_map(fn (array $finding): array => [$finding['file'], $finding['line'], $finding['term']], $findings));
        }

        return $findings === [] ? self::SUCCESS : self::FAILURE;
    }
}
