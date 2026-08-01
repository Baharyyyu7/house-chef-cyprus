# Design System Audit — Milestone G

## Current contract

House Chef Cyprus uses a semantic CSS-variable layer in `resources/css/app.css`. Public, member and admin primitives share `--hc-*` tokens for page/surface/text/border/brand/status colors, spacing, radius, shadow and motion. Light and dark values are paired under `:root` and `html.dark`.

## Findings and decisions

- Shared primitives: `hc-v3-card`, `hc-v3-btn`, `hc-v3-input`, `hc-v3-badge`, `hc-v3-alert`, `hc-v3-empty` and table shells.
- Public shell, member journeys and admin shell consume the same semantic surface, border, focus and motion tokens.
- Older utility-class fragments remain in legacy templates. `ux:theme-audit --json` records their count so replacement is deliberate rather than hidden.
- A static audit does not certify contrast, keyboard order or responsive layout. Those remain browser QA work.

## Guardrail

New UI must use semantic variables/primitives first. A hard-coded color is permitted only for an intentional media overlay, chart palette or status treatment and must retain light/dark contrast.
