@php
    $workspace = collect(config('admin_workspaces'))->first(fn ($area) => collect($area['tabs'])->contains(fn ($tab) => request()->routeIs(...$tab['matches'])));
@endphp
@if($workspace)
    <nav aria-label="{{ __('admin_workspaces.tabs') }}" data-admin-workspace-tabs class="flex flex-wrap gap-x-5 gap-y-2 border-b px-4 py-3 sm:px-6" style="border-color:var(--hc-border-subtle)">
        @foreach($workspace['tabs'] as $label => $tab)
            @php($selected = request()->routeIs(...$tab['matches']))
            <a href="{{ route($tab['route']) }}" @if($selected) aria-current="page" @endif
               @class(['border-b-2 py-2 text-sm transition-colors', 'border-current font-bold' => $selected, 'border-transparent text-muted hover:underline' => ! $selected])>
                {{ __('admin_workspaces.'.$label) }}
            </a>
        @endforeach
    </nav>
@endif
