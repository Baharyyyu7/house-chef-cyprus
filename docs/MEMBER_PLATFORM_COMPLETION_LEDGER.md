# Member Platform Completion Ledger

The member platform must be treated as a security surface. Local tests and
configuration inspection do not prove SMTP delivery, active-release browser
behaviour, or production cookie flags.

| Task ID | Route / module | State | Evidence and tests | Deployment / public state | Blocker and next action |
| --- | --- | --- | --- | --- | --- |
| MEMBER-001 | registration | CODE_COMPLETE | Server validation, transaction, normalized identity fields and registration throttling are implemented. | ARTIFACT_READY | Use a controlled non-production account on the active release; do not create uncontrolled accounts. |
| MEMBER-002 | login, logout and remember-me | CODE_COMPLETE | Laravel authentication, session regeneration, throttling and suspended-account handling are implemented. | ARTIFACT_READY | Browser-test with a controlled account after deployment. |
| MEMBER-003 | verify-email | TESTED | Signed verification route exists; resend requests are limited to three attempts per member/IP/hour. | ARTIFACT_READY | BLOCKED_SMTP until one controlled delivery and signed-link verification succeeds. |
| MEMBER-004 | Password reset and confirmation | CODE_COMPLETE | Framework routes and noindex guest surfaces exist. | ARTIFACT_READY | BLOCKED_SMTP until a controlled reset delivery test succeeds. |
| MEMBER-005 | Profile, favourites, collections and pantry | CODE_COMPLETE | Auth/verified middleware and ownership policies protect private data. | ARTIFACT_READY | Browser-test member isolation and IDOR denial on the active release. |
| MEMBER-006 | New-member admin notification | TESTED | Queued, after-commit and idempotent notification record; no password or token in the mail model. | ARTIFACT_READY | BLOCKED_SMTP until controlled delivery evidence exists. |
| MEMBER-007 | Contact requests | CODE_COMPLETE | Public request rate limiting, CAPTCHA/validation and restricted admin routes are implemented. | ARTIFACT_READY | Confirm delivery and admin inbox on the active release. |
| MEMBER-008 | Cookies, session and queue | IN_PROGRESS | Repository reads environment keys without exposing values. | BLOCKED_PRODUCTION_ACCESS | Inspect production secure/HttpOnly/SameSite settings, worker freshness and failed jobs through SSH/admin operations. |

## Required active-release checks

1. Run the production environment and mail-health commands without displaying
   secrets.
2. Use a controlled member fixture to verify register, email verification,
   login, logout, favourites, collections and profile ownership.
3. Confirm one welcome/admin/contact notification delivery with provider-side
   evidence, then remove or anonymize the fixture when it is no longer needed.
