<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recipe->title }} · House Chef Cyprus</title>
    <link rel="canonical" href="{{ route('recipes.show', $recipe) }}">
    <meta name="robots" content="index,follow">
    <style>
        *, ::before, ::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #1f2937;
            padding: 12px;
            font-size: 14px;
            line-height: 1.4;
        }
        .recipe-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            max-width: 540px;
            margin: 0 auto;
            transition: border-color 0.2s;
        }
        .recipe-card:hover { border-color: #d1d5db; }
        .card-header {
            display: flex;
            gap: 12px;
            padding: 14px;
        }
        .card-img-wrap {
            position: relative;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            background: #f3f4f6;
        }
        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-body {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #d97706;
            margin-bottom: 4px;
        }
        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: #ecfdf5;
            color: #059669;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
        }
        .card-title {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.25;
            color: #111827;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-title:hover { color: #b45309; }
        .card-stats {
            display: flex;
            gap: 12px;
            margin-top: 6px;
            font-size: 12px;
            color: #4b5563;
        }
        .stat-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .card-ingredients {
            padding: 10px 14px;
            background: #fafafa;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            font-size: 12px;
        }
        .ing-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            display: block;
        }
        .ing-list {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            list-style: none;
        }
        .ing-tag {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 2px 7px;
            border-radius: 9999px;
            color: #4b5563;
            font-size: 11px;
            white-space: nowrap;
        }
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #ffffff;
            font-size: 12px;
        }
        .brand-link {
            font-weight: 700;
            color: #b45309;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .brand-link:hover { text-decoration: underline; }
        .action-btn {
            background: #f59e0b;
            color: #ffffff;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.15s;
        }
        .action-btn:hover { background: #d97706; }
    </style>
</head>
<body>
    <div class="recipe-card">
        <div class="card-header">
            @if($imageUrl)
                <div class="card-img-wrap">
                    <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="card-img" loading="lazy">
                </div>
            @endif
            <div class="card-body">
                <div>
                    <div class="card-meta">
                        <span>{{ $recipe->cuisine?->name ?? 'Kıbrıs' }}</span>
                        <span>•</span>
                        <span class="badge-verified">
                            <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            {{ $locale === 'en' ? 'Verified Recipe' : 'Doğrulanmış' }}
                        </span>
                    </div>
                    <a href="{{ route('recipes.show', $recipe) }}" target="_blank" rel="noopener" class="card-title">
                        {{ $recipe->title }}
                    </a>
                </div>
                <div class="card-stats">
                    <span class="stat-item" title="{{ $locale === 'en' ? 'Total Time' : 'Toplam Süre' }}">
                        ⏱️ {{ $recipe->totalMinutes() }} {{ $locale === 'en' ? 'min' : 'dk' }}
                    </span>
                    <span class="stat-item" title="{{ $locale === 'en' ? 'Servings' : 'Porsiyon' }}">
                        🍽️ {{ $recipe->servings }} {{ $locale === 'en' ? 'servings' : 'kişilik' }}
                    </span>
                </div>
            </div>
        </div>

        @if(count($ingredients) > 0)
            <div class="card-ingredients">
                <span class="ing-label">{{ $locale === 'en' ? 'Key Ingredients:' : 'Öne Çıkan Malzemeler:' }}</span>
                <ul class="ing-list">
                    @foreach($ingredients as $ing)
                        <li class="ing-tag">{{ $ing['formatted'] ?? $ing['name'] ?? '' }}</li>
                    @endforeach
                    @if($totalIngredients > count($ingredients))
                        <li class="ing-tag" style="background:#f3f4f6; color:#6b7280;">+{{ $totalIngredients - count($ingredients) }} {{ $locale === 'en' ? 'more' : 'daha' }}</li>
                    @endif
                </ul>
            </div>
        @endif

        <div class="card-footer">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="brand-link">
                🏛️ House Chef Cyprus
            </a>
            <a href="{{ route('recipes.show', $recipe) }}" target="_blank" rel="noopener" class="action-btn">
                {{ $locale === 'en' ? 'View Recipe & Steps →' : 'Tarife Git →' }}
            </a>
        </div>
    </div>
</body>
</html>
