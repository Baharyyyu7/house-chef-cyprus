# Mail Operations

Verification, reset, security and operational alerts must use queued delivery with rate limits and safe logging. Keep secrets, recipients beyond operational need, tokens and message bodies out of health output. Mail remains `not_configured` until the real SMTP contract is supplied and approved.

The admin-only `/yonetim/e-posta-operasyonlari` page exposes a safe operational snapshot: SMTP field presence, queue mode, failed mail jobs, registration-notification delivery counts, DNS readiness flags and HTML/plain-text template coverage. It never renders SMTP passwords, mailbox credentials, reset tokens, recipient lists or message bodies.

Each owned template has an admin-only synthetic preview. Preview routes never send mail and never load a real member or contact record. The operations surface also reports pending/failed mail jobs, delivery attempts, latest success/failure timestamps and the idempotency contract.

Hostinger DNS discovery on 2026-08-03 confirmed MX, SPF, three DKIM selectors and a DMARC record. The DMARC policy is observation-only (`p=none`) and must not be promoted without delivery monitoring and owner approval. The Hostinger Mail connector did not expose callable mailbox, alias, forward, bounce or complaint APIs in this session; these states remain `NOT_AVAILABLE` instead of being inferred.

Set `MAIL_SPF_VERIFIED`, `MAIL_DKIM_VERIFIED` and `MAIL_DMARC_VERIFIED` only after an authorized DNS audit. These values are read through cached Laravel configuration so `config:cache` remains safe.
