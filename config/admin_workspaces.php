<?php

return [
    'catalogue' => [
        'icon' => 'database',
        'tabs' => [
            'ingredients' => ['route' => 'admin.ingredients.index', 'matches' => ['admin.ingredients.*']],
            'units' => ['route' => 'admin.measurements.units', 'matches' => ['admin.measurements.units*']],
            'conversions' => ['route' => 'admin.measurements.conversions', 'matches' => ['admin.measurements.conversions*']],
            'nutrition' => ['route' => 'admin.nutrition.index', 'matches' => ['admin.nutrition.*']],
            'quality' => ['route' => 'admin.data-quality.index', 'matches' => ['admin.data-quality.*']],
        ],
    ],
    'search' => [
        'icon' => 'search',
        'tabs' => [
            'overview' => ['route' => 'admin.seo.index', 'matches' => ['admin.seo.index', 'admin.seo.audit']],
            'coni_bot' => ['route' => 'admin.seo.coni-bot', 'matches' => ['admin.seo.coni-bot*']],
            'machina_loop' => ['route' => 'admin.seo.machina-loop', 'matches' => ['admin.seo.machina-loop*']],
            'links' => ['route' => 'admin.seo.growth', 'matches' => ['admin.seo.growth*']],
            'work' => ['route' => 'admin.seo.opportunities', 'matches' => ['admin.seo.opportunities*']],
            'issues' => ['route' => 'admin.seo.issues', 'matches' => ['admin.seo.issues*']],
            'ai_mentions' => ['route' => 'admin.seo.ai-mentions', 'matches' => ['admin.seo.ai-mentions*']],
            'citation_gaps' => ['route' => 'admin.seo.citation-gaps', 'matches' => ['admin.seo.citation-gaps*']],
            'llm_structure' => ['route' => 'admin.seo.llm-structure', 'matches' => ['admin.seo.llm-structure*']],
            'weekly_report' => ['route' => 'admin.seo.weekly-report', 'matches' => ['admin.seo.weekly-report*']],
        ],
    ],
    'experience' => [
        'icon' => 'sliders',
        'tabs' => [
            'interface' => ['route' => 'admin.product-experience.index', 'matches' => ['admin.product-experience.*']],
            'recommendations' => ['route' => 'admin.personalization.index', 'matches' => ['admin.personalization.*']],
        ],
    ],
];
