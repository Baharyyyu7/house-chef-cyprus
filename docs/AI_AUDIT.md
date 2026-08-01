# AI Audit

`ai_interactions` stores safe summaries, audit public IDs, provider/model, prompt version, token/cost/latency, cache/fallback state, safe error code and safety events. `audit_logs` records the authenticated user action. Raw prompts containing sensitive information, headers, credentials and raw provider responses are not displayed or retained by this layer.
