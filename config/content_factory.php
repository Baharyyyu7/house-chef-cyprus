<?php

declare(strict_types=1);

return [
    'enabled' => filter_var(env('CONTENT_AUTOMATION_ENABLED', true), FILTER_VALIDATE_BOOL),
    'auto_publish' => false,
    'require_human_approval' => true,
    'recipe_batch_size' => 25,
    'blog_batch_size' => 10,
    'max_recipe_drafts_per_day' => (int) env('CONTENT_MAX_RECIPE_DRAFTS_PER_DAY', 25),
    'max_blog_drafts_per_day' => (int) env('CONTENT_MAX_BLOG_DRAFTS_PER_DAY', 10),
    'max_refresh_drafts_per_day' => (int) env('CONTENT_MAX_REFRESH_DRAFTS_PER_DAY', 5),
];
