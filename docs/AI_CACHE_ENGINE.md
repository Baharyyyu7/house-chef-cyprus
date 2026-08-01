# AI Cache Engine

Responses use a user-scoped hashed request key and a bounded TTL. Pantry, conversation and other user context is never shared through another user's cache key. Cache hits are recorded as separate audit interactions for observability. Cached values omit contact, address and free-form note fields.
