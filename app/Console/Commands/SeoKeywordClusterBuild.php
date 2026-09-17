<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Recipe;
use Illuminate\Console\Command;

/**
 * SEO Keyword Cluster Build — Semantik konu kümelerini ve Hub-Spoke
 * iç bağlantı mimarisini inşa eder.
 *
 * Sitedeki 900+ tarif ve 40+ blog yazısını tematik anahtar kelime kümelerine
 * gruplar, eksik iç bağlantıları tespit eder ve site otoritesini maksimuma çıkarır.
 */
final class SeoKeywordClusterBuild extends Command
{
    protected $signature = 'seo:keyword-cluster-build
                            {--export-json : Kümeleri storage/app/seo-clusters/ dizinine kaydeder}
                            {--audit-links : Mevcut gövdelerdeki eksik bağlantıları analiz eder}';

    protected $description = 'Tarifler ve bloglar arasında semantik anahtar kelime kümelerini ve Hub-Spoke bağlantı haritasını oluşturur.';

    /** @var list<array{cluster_id: string, name: string, pillar_slug: string, pillar_type: string, keywords: list<string>}> */
    private const CLUSTER_DEFINITIONS = [
        [
            'cluster_id' => 'cluster_hellim_peynir',
            'name' => 'Kıbrıs Peynirleri ve Hellim Mimarisi',
            'pillar_slug' => 'hellim-peyniri-rehberi',
            'pillar_type' => 'blog',
            'keywords' => ['hellim', 'halloumi', 'peynir', 'anari', 'lor', 'tulum', 'bitta', 'tost'],
        ],
        [
            'cluster_id' => 'cluster_kleftiko_et',
            'name' => 'Geleneksel Fırın, Tandır ve Kleftiko Mirası',
            'pillar_slug' => 'kleftiko-tarifi-rehberi',
            'pillar_type' => 'blog',
            'keywords' => ['kleftiko', 'kuzu', 'tandır', 'seftali-kebab', 'incik', 'güveç', 'yahni'],
        ],
        [
            'cluster_id' => 'cluster_akdeniz_diyeti',
            'name' => 'Akdeniz Diyeti ve Zeytinyağlılar',
            'pillar_slug' => 'akdeniz-diyeti-30-gun',
            'pillar_type' => 'blog',
            'keywords' => ['akdeniz', 'zeytinyağ', 'enginar', 'börülce', 'barbunya', 'pırasa', 'bakla', 'vegan'],
        ],
        [
            'cluster_id' => 'cluster_kibris_meze',
            'name' => 'Kıbrıs Meze Kültürü ve Paylaşım Sofraları',
            'pillar_slug' => 'kibris-meze-rehberi',
            'pillar_type' => 'blog',
            'keywords' => ['meze', 'humus', 'talatturi', 'cacık', 'haydari', 'fava', 'ezme', 'çakıstes'],
        ],
        [
            'cluster_id' => 'cluster_sarap_gastronomi',
            'name' => 'Kıbrıs Şarapları ve Bağcılık Gastronomisi',
            'pillar_slug' => 'kibris-saraplari-rehberi',
            'pillar_type' => 'blog',
            'keywords' => ['şarap', 'wine', 'commandaria', 'zivania', 'bağ', 'düğün', 'festival'],
        ],
        [
            'cluster_id' => 'cluster_hizmetler',
            'name' => 'Özel Şef, Catering ve Mutfak Atölyeleri',
            'pillar_slug' => 'private-chef-cyprus-rehberi',
            'pillar_type' => 'blog',
            'keywords' => ['private-chef', 'şef', 'catering', 'kurs', 'etkinlik', 'davet', 'menü'],
        ],
    ];

    public function handle(): int
    {
        $this->info('Semantik Anahtar Kelime Kümeleri ve Hub-Spoke Mimarisi İnşa Ediliyor...');

        $clusters = [];
        $recipes = Recipe::query()
            ->where('status', 'published')
            ->get(['id', 'slug', 'title', 'category_id', 'cuisine_id']);

        $blogs = BlogPost::query()
            ->where('status', 'published')
            ->orWhere('status', 'draft')
            ->get(['id', 'slug', 'title', 'body', 'status']);

        foreach (self::CLUSTER_DEFINITIONS as $def) {
            $pillar = $blogs->firstWhere('slug', $def['pillar_slug']);
            $matchedRecipes = [];

            foreach ($recipes as $recipe) {
                $slugLower = strtolower($recipe->slug);
                $titleLower = strtolower($recipe->title);

                foreach ($def['keywords'] as $kw) {
                    if (str_contains($slugLower, $kw) || str_contains($titleLower, $kw)) {
                        $matchedRecipes[] = [
                            'id' => $recipe->id,
                            'slug' => $recipe->slug,
                            'title' => $recipe->title,
                        ];
                        break;
                    }
                }
            }

            $matchedBlogs = [];
            foreach ($blogs as $blog) {
                if ($blog->slug === $def['pillar_slug']) {
                    continue;
                }

                $blogSlug = strtolower($blog->slug);
                $blogTitle = strtolower($blog->title);

                foreach ($def['keywords'] as $kw) {
                    if (str_contains($blogSlug, $kw) || str_contains($blogTitle, $kw)) {
                        $matchedBlogs[] = [
                            'id' => $blog->id,
                            'slug' => $blog->slug,
                            'title' => $blog->title,
                            'status' => $blog->status,
                        ];
                        break;
                    }
                }
            }

            $clusters[] = [
                'cluster_id' => $def['cluster_id'],
                'cluster_name' => $def['name'],
                'pillar' => [
                    'slug' => $def['pillar_slug'],
                    'title' => $pillar?->title ?? $def['pillar_slug'],
                    'url' => url('/blog/' . $def['pillar_slug']),
                ],
                'spokes_count' => count($matchedRecipes) + count($matchedBlogs),
                'spoke_recipes' => $matchedRecipes,
                'spoke_blogs' => $matchedBlogs,
            ];

            $this->line("  ✦ [{$def['name']}]");
            $this->line("    Pillar: " . ($pillar?->title ?? $def['pillar_slug']));
            $this->line("    Spokes: " . count($matchedRecipes) . " tarif + " . count($matchedBlogs) . " blog");
        }

        // Export JSON
        $dir = storage_path('app/seo-clusters');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = "{$dir}/keyword-clusters.json";
        file_put_contents($filePath, json_encode($clusters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info("✓ Toplam " . count($clusters) . " semantik küme inşa edildi ve kaydedildi: {$filePath}");

        return self::SUCCESS;
    }
}
