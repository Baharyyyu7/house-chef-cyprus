<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\RouteAudit\RouteSurfaceClassifier;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

final class RouteFunctionalityAudit extends Command
{
    protected $signature = 'route:functionality-audit
        {--json : Makine okunabilir çıktı}
        {--output= : JSON raporunun yazılacağı proje içi yol}
        {--include-admin : Yönetim GET rotalarını da güvenli biçimde dene}
        {--probe : Güvenli statik GET rotalarını HTTP çekirdeğiyle dene}
        {--limit=25 : Tek çalıştırmada denenecek azami rota}
        {--no-probe : Eski çağrılar için; yalnız envanter çıkar}';

    protected $description = 'Tüm rotaları sınıflandırır ve yalnız güvenli GET yüzeylerini işlevsellik açısından denetler.';

    public function handle(RouteSurfaceClassifier $classifier, Kernel $kernel): int
    {
        $remainingProbes = max(0, (int) $this->option('limit'));
        $rows = collect(RouteFacade::getRoutes()->getRoutes())
            ->map(function (Route $route) use ($classifier, $kernel, &$remainingProbes): array {
                $allowProbe = (bool) $this->option('probe') && ! (bool) $this->option('no-probe') && $remainingProbes > 0;
                $row = $this->row($route, $classifier, $kernel, $allowProbe);
                if ($row['observed_status'] !== null) {
                    $remainingProbes--;
                }

                return $row;
            })
            ->values();

        $report = [
            'generated_at' => now()->toIso8601String(),
            'total' => $rows->count(),
            'summary' => $rows->countBy('result')->sortKeys()->all(),
            'surfaces' => $rows->countBy('surface')->sortKeys()->all(),
            'routes' => $rows->all(),
        ];

        $json = (string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $output = trim((string) $this->option('output'));

        if ($output !== '') {
            $path = str_starts_with($output, DIRECTORY_SEPARATOR) ? $output : base_path($output);
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $json.PHP_EOL);
        }

        if ($this->option('json')) {
            $this->line($json);
        } else {
            $this->table(['Yüzey', 'Sayı'], collect($report['surfaces'])->map(fn (int $count, string $surface): array => [$surface, $count])->values());
            $this->info('Toplam rota: '.$report['total']);
        }

        return self::SUCCESS;
    }

    private function row(Route $route, RouteSurfaceClassifier $classifier, Kernel $kernel, bool $allowProbe): array
    {
        $surface = $classifier->classify($route);
        $probe = $allowProbe
            && $classifier->isSafelyProbeable($route)
            && in_array($surface, ['public', 'machine'], true)
            && ($surface !== 'admin' || $this->option('include-admin'))
            && ! Str::startsWith($route->uri(), ['_zamanlayici', 'livewire/', 'storage/']);
        $observed = null;
        $issues = [];

        if ($probe) {
            try {
                $request = Request::create($classifier->path($route), 'GET');
                $response = $kernel->handle($request);
                $observed = $response->getStatusCode();
                $kernel->terminate($request, $response);
                if ($observed >= 500) {
                    $issues[] = 'server_error';
                }
            } catch (\Throwable) {
                $observed = 500;
                $issues[] = 'probe_exception';
            }
        }

        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
            'surface' => $surface,
            'access' => $classifier->access($route),
            'expected_status' => $this->expectedStatus($surface),
            'observed_status' => $observed,
            'primary_action' => $this->primaryAction($route),
            'result' => $observed === null ? 'inventory_only' : ($observed < 500 ? 'reachable' : 'failed'),
            'issues' => $issues,
        ];
    }

    private function expectedStatus(string $surface): string
    {
        return match ($surface) {
            'admin', 'authenticated' => '302_guest_or_2xx_authorized',
            'api' => '2xx_or_contract_error',
            default => '2xx_or_intentional_redirect',
        };
    }

    private function primaryAction(Route $route): string
    {
        if (! in_array('GET', $route->methods(), true)) {
            return 'mutation';
        }

        $name = (string) $route->getName();

        return match (true) {
            Str::contains($name, ['create', 'edit']) => 'form',
            Str::endsWith($name, ['.show']) => 'detail',
            Str::contains($name, ['index', 'list']) => 'list',
            default => 'render',
        };
    }
}
