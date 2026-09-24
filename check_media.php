<?php

use App\Models\BlogPost;
use App\Models\Recipe;

$recipesCount = Recipe::where('status', 'published')->count();
$recipesWithMediaCount = Recipe::where('status', 'published')->get()->filter(fn ($r) => $r->media->first(fn ($m) => method_exists($m, 'isApprovedRealMedia') ? $m->isApprovedRealMedia() : false) !== null)->count();

$blogsCount = BlogPost::where('status', 'published')->count();
$blogsWithMediaCount = BlogPost::where('status', 'published')->get()->filter(fn ($b) => $b->media->first(fn ($m) => method_exists($m, 'isApprovedRealMedia') ? $m->isApprovedRealMedia() : false) !== null)->count();

echo 'Published recipes: '.$recipesCount."\n";
echo 'Recipes with media (approx): '.$recipesWithMediaCount."\n";
echo 'Published blogs: '.$blogsCount."\n";
echo 'Blogs with media: '.$blogsWithMediaCount."\n";
