# AI Kitchen Architecture

AI Kitchen is a database-grounded decision-support layer. It never replaces the verified recipe catalogue and never changes publication or verification state. Every response has a public audit ID, provider/model record, safe request/response summary, latency, token/cost fields, cache state, fallback state and safety events.

The initial runtime is the deterministic local `mock` provider. Gemini, OpenAI, Claude, OpenRouter and Ollama are provider seams; they report configuration state only until a separately reviewed live adapter is enabled. API keys and raw provider payloads are neither persisted nor rendered.

## Modules

- Recipe assistant and smart-query interpretation
- Pantry and leftover suggestions backed by published/verified recipes only
- 7/14/30-day draft meal plans and draft shopping lists
- Portion conversion, nutrition explanation, substitution and cooking-coach seams
- Per-user conversation memory, response cache, rate limit, safety events and audit

All plans and lists are drafts. A user must make the final cooking, shopping and dietary decision.
