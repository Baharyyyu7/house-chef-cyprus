# AI Kitchen Architecture

AI Kitchen is a database-grounded decision-support layer. It never replaces the verified recipe catalogue and never changes publication or verification state. Every response has a public audit ID, provider/model record, safe request/response summary, latency, token/cost fields, cache state, fallback state and safety events.

The initial runtime is the deterministic local `mock` provider. Gemini, OpenAI, Claude, OpenRouter and Ollama are provider seams; they report configuration state only until a separately reviewed live adapter is enabled. NVIDIA NIM is the first reviewed OpenAI-compatible live adapter, but remains disabled by default and must be activated explicitly with `NVIDIA_AI_KITCHEN_ENABLED=true`. API keys and raw provider payloads are neither persisted nor rendered.

## NVIDIA NIM

- A single `NVIDIA_API_KEY` grants access to a changing model catalogue; models do not require separate API keys.
- `php artisan nvidia:models --json` discovers the models accessible to the current key without printing the key.
- `NVIDIA_AI_KITCHEN_MODEL` selects exactly one runtime model. Discovery never enables or calls every model.
- `NVIDIA_AI_KITCHEN_ENABLED=false` is the safe default. Catalogue discovery is read-only; chat completion calls require explicit activation.
- Requests exclude common personal-data fields, have bounded prompt/token sizes and timeouts, and return classified safe error codes.
- Provider/model and token counts are written to the existing AI interaction audit trail. The adapter cannot publish or verify recipes, blogs, translations, or media.

## Modules

- Recipe assistant and smart-query interpretation
- Pantry and leftover suggestions backed by published/verified recipes only
- 7/14/30-day draft meal plans and draft shopping lists
- Portion conversion, nutrition explanation, substitution and cooking-coach seams
- Per-user conversation memory, response cache, rate limit, safety events and audit

All plans and lists are drafts. A user must make the final cooking, shopping and dietary decision.
