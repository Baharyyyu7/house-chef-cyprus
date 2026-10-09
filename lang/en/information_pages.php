<?php

// English overlay for App\Http\Controllers\InformationPageController::PAGES.
// Same slugs, same section order and counts as the Turkish source.

return [
    'hakkimizda' => [
        'title' => 'About Us',
        'eyebrow' => 'House Chef Cyprus',
        'lead' => 'We are a platform inspired by Cyprus that brings reliable recipe data closer to everyday kitchen decisions.',
        'sections' => [
            [
                'title' => 'What do we do?',
                'body' => 'We organize recipes together with their ingredients, measurements, nutrition, allergen and source information.',
            ],
            [
                'title' => 'Our approach to trust',
                'body' => 'For published recipes, verification and provenance records are kept; uncertain information is not presented as if it were certain.',
            ],
        ],
    ],
    'nasil-calisir' => [
        'title' => 'How does it work?',
        'eyebrow' => 'Less uncertainty in the kitchen',
        'lead' => 'Choose a recipe, add your ingredients to your pantry, and discover suitable options through cautious matching.',
        'sections' => [
            [
                'title' => 'Explore recipes',
                'body' => 'Find recipes with filters for cooking time, cuisine, nutrition and allergens.',
            ],
            [
                'title' => 'Match with your pantry',
                'body' => 'Save the ingredients you have on hand; missing or uncertain matches are clearly marked.',
            ],
        ],
    ],
    'sss' => [
        'title' => 'Frequently asked questions',
        'eyebrow' => 'Help center',
        'lead' => 'Basic answers about recipe data, nutrition information and account features.',
        'sections' => [
            [
                'title' => 'Is nutrition information medical advice?',
                'body' => 'No. The information is for recipe planning; for health decisions, seek professional support.',
            ],
            [
                'title' => 'How should allergen information be used?',
                'body' => 'Labels are a helpful aid. Always separately check the packaging and the risk of cross-contamination.',
            ],
            [
                'title' => 'How do household measures work?',
                'body' => 'Recipes include household measures suited to everyday use. Gram and milliliter equivalents are shown separately where the source and confidence level allow it.',
            ],
            [
                'title' => 'What does portion scaling do?',
                'body' => 'It calculates quantities according to the portion you select; for some household measures, the result should be interpreted with caution.',
            ],
            [
                'title' => 'How do My Pantry and What Can I Cook? work?',
                'body' => 'It matches the recipes with the ingredients you have saved; it clearly separates missing, possible with substitutes, and fully suitable cases.',
            ],
            [
                'title' => 'Where do the images come from?',
                'body' => 'Approved provider images or a recipe-specific branded fallback are used. Pexels and AI images go through human review before they are published.',
            ],
            [
                'title' => 'How can I delete my account?',
                'body' => 'You can create a deletion request from your account settings. Applicable data retention and security rules apply to this process.',
            ],
        ],
    ],
    'editorial-ilkeler' => [
        'title' => 'Editorial principles',
        'eyebrow' => 'Content trust',
        'lead' => 'Our standards for producing content that is original, traceable and cautious.',
        'sections' => [
            [
                'title' => 'Originality',
                'body' => 'No text, recipes or images are copied from other sites.',
            ],
            [
                'title' => 'Corrections',
                'body' => 'Unclear or incorrect data is placed under review; change history is kept.',
            ],
        ],
    ],
    'tarif-dogrulama-politikasi' => [
        'title' => 'Recipe Verification Policy',
        'eyebrow' => 'Content trust',
        'lead' => 'Recipe identity, ingredient logic, measurements and source information are assessed together through editorial review.',
        'sections' => [
            [
                'title' => 'Publication threshold',
                'body' => 'For published recipes, the source/provenance and verification status are kept on record. Uncertain records are not presented as if they were confirmed.',
            ],
            [
                'title' => 'Corrections',
                'body' => 'After an error report or internal audit, a recipe may be placed under review; its change history and source context are not deleted.',
            ],
        ],
    ],
    'besin-degeri-aciklamasi' => [
        'title' => 'Nutritional Value Statement',
        'eyebrow' => 'Cautious calculation',
        'lead' => 'Nutritional values help with recipe planning; they are not a medical diagnosis, treatment or personal dietary advice.',
        'sections' => [
            [
                'title' => 'Confidence level',
                'body' => 'The scope of the calculation, the status of the sources and any missing ingredients affect how reliable the result is. Uncertain results are not presented as exact values.',
            ],
            [
                'title' => 'Personal needs',
                'body' => 'For allergies, illness, medication use or special dietary requirements, consult a health professional.',
            ],
        ],
    ],
    'alerjen-uyarisi' => [
        'title' => 'Allergen Warning',
        'eyebrow' => 'Safe use',
        'lead' => 'Allergen labels are supporting information only; product packaging, cross-contamination and personal circumstances should also be checked separately.',
        'sections' => [
            [
                'title' => 'Limits of certainty',
                'body' => 'In recipe information, \'contains\', \'may contain\' and cross-contamination risk are not treated as having the same meaning.',
            ],
            [
                'title' => 'Kitchen safety',
                'body' => 'Home equipment, brand differences and cross-contact can change personal risks. Do not base critical decisions on this label alone.',
            ],
        ],
    ],
    'gorsel-ve-medya-politikasi' => [
        'title' => 'Image and Media Policy',
        'eyebrow' => 'Transparent media',
        'lead' => 'Image sources, licence information and editorial review status are tracked separately from recipe data.',
        'sections' => [
            [
                'title' => 'Review chain',
                'body' => 'Externally sourced and AI-generated images are first moved to a private review area. They are not published in the public area without human approval.',
            ],
            [
                'title' => 'Fallback images',
                'body' => 'Where no suitable real image is available, a branded fallback specific to the recipe is used; this image is not presented as a real photo of the dish.',
            ],
        ],
    ],
    'icerik-duzeltme-politikasi' => [
        'title' => 'Content Correction Policy',
        'eyebrow' => 'Correction process',
        'lead' => 'Possible errors in recipe, blog, source or media information are referred for human editorial review.',
        'sections' => [
            [
                'title' => 'Review',
                'body' => 'Reports are assessed by source, data context and likely user impact; for critical updates, published content may be placed on review hold.',
            ],
            [
                'title' => 'Outcome',
                'body' => 'Each decision, whether a correction, a request for additional sources, removal from publication or a finding that no change is needed, is linked to an audit record.',
            ],
        ],
    ],
    'hata-bildir' => [
        'title' => 'Report an Error',
        'eyebrow' => 'Content and technical error reporting',
        'lead' => 'If you notice a problem with a recipe, source, allergen, media or technical behavior, you can report it so we can review it.',
        'sections' => [
            [
                'title' => 'What should you include?',
                'body' => 'Share the link to the relevant page, a short description of the problem and, if you have one, a safe source reference. Do not send passwords, payment details or personal health information.',
            ],
            [
                'title' => 'Channel status',
                'body' => 'Until our official support channel goes live, we do not publish verified contact details for account and content requests. This does not mean that review records are closed automatically.',
            ],
        ],
    ],
];
