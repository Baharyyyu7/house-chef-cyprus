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
        'nutrition' => ['label' => 'Beslenme', 'weight' => 12],
        // Bu üç kategoriyi üretecek hiçbir kontrol yok: report() içinde
        // "allergen", "dietary" ya da operasyonel QA türünde bir bulgu
        // hiç oluşmuyor. Ceza alamadıkları için skorları sonsuza kadar
        // %100 kalıyordu ve ağırlığın %22'siyle genel skoru şişiriyorlardı.
        // Ölçülmeyen bir şeye tam not vermek, veri yokluğundan olumlu
        // sonuç çıkarmaktır. Kontrol yazıldığında measured true olur.
        'allergens' => ['label' => 'Alerjenler', 'weight' => 9, 'measured' => false],
        'dietary' => ['label' => 'Diyet uygunluğu', 'weight' => 8, 'measured' => false],
        'media' => ['label' => 'Medya', 'weight' => 14],
        'provenance' => ['label' => 'Kaynak / provenance', 'weight' => 10],
        'operational_confidence' => ['label' => 'Operasyonel QA', 'weight' => 5, 'measured' => false],
    ],
    'thresholds' => [
        'conversion_low_confidence' => 70,
        'nutrition_low_confidence' => 80,
        'placeholder_repetition' => 2,
    ],
];
