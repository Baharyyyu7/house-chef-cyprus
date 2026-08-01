# Design Tokens

`resources/css/app.css` is the source of truth. Tokens include `--hc-background-*`, `--hc-surface-*`, `--hc-text-*`, `--hc-border-*`, `--hc-brand-*`, `--hc-status-*`, spacing, radius, shadow, duration, easing and chart variables.

- Light and dark themes are equivalent semantic mappings, not inverted screenshots.
- Focus uses the shared brand focus ring.
- Overlay, disabled, empty and error states inherit tokens; they do not rely on a page-only color.
- Breakpoints use Tailwind’s mobile-first utilities with explicit constrained table overflow.
