<?php

declare(strict_types=1);

return [
    'penalties' => [
        'critical' => 25,
        'error' => 12,
        'warning' => 4,
        'info' => 1,
    ],
    'categories' => [
        'content_completeness' => ['label' => 'İçerik bütünlüğü', 'weight' => 16],
        'data_trust' => ['label' => 'Veri güveni', 'weight' => 14],
        'measurements' => ['label' => 'Ölçümler', 'weight' => 12],
        'nutrition' => ['label' => 'Nutrition', 'weight' => 12],
        'allergens' => ['label' => 'Alerjenler', 'weight' => 9],
        'dietary' => ['label' => 'Diyet uygunluğu', 'weight' => 8],
        'media' => ['label' => 'Medya', 'weight' => 14],
        'provenance' => ['label' => 'Kaynak / provenance', 'weight' => 10],
        'operational_confidence' => ['label' => 'Operasyonel QA', 'weight' => 5],
    ],
    'thresholds' => [
        'conversion_low_confidence' => 70,
        'nutrition_low_confidence' => 80,
        'placeholder_repetition' => 2,
    ],
];
