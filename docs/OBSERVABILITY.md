# Observability

Every request receives a server-generated ULID request ID in `X-Request-ID`. Completion logs carry the request ID, public user identity where available, route, method, status, duration, privacy-preserving IP hash and bounded user-agent summary. Credentials, cookies, tokens and raw payloads are excluded.

`ErrorReporterInterface` currently uses local structured logs and safe database events. It can be replaced with Sentry, Bugsnag or an OpenTelemetry collector without changing domain services.
