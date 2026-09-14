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
            'links' => ['route' => 'admin.seo.growth', 'matches' => ['admin.seo.growth*']],
            'work' => ['route' => 'admin.seo.opportunities', 'matches' => ['admin.seo.opportunities*']],
            'issues' => ['route' => 'admin.seo.issues', 'matches' => ['admin.seo.issues*']],
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
