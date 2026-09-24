<?php

declare(strict_types=1);

/*
 * English recipe editions, keyed by the Turkish recipe slug.
 * Imported by `php artisan content:import-translations --locale=en`.
 */
return array_merge(
    require __DIR__.'/recipes/batch-1.php',
    require __DIR__.'/recipes/batch-2.php',
    require __DIR__.'/recipes/batch-3.php',
    require __DIR__.'/recipes/batch-4.php',
);
