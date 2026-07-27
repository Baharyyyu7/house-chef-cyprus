@props(['variant' => 'info'])
<div {{ $attributes->class(['hc-v3-alert', 'hc-v3-alert--'.$variant]) }} role="{{ $variant === 'danger' ? 'alert' : 'status' }}"><x-ui.icon :name="$variant === 'danger' ? 'warning' : ($variant === 'success' ? 'check' : 'info')" size="18" /> <div>{{ $slot }}</div></div>
