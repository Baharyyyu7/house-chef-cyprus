@props(['eyebrow' => 'Yönetim', 'title', 'description' => null, 'back' => null])

<header class="hc-admin-crud-header hc-v3-reveal">
    <div>
        @if($back)
            <a class="hc-admin-back" href="{{ $back['url'] }}"><x-ui.icon name="arrow-right" size="14" class="rotate-180" /> {{ $back['label'] }}</a>
        @endif
        <p class="hc-admin-eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
    @if(isset($actions))<div class="hc-admin-page-actions">{{ $actions }}</div>@endif
</header>
