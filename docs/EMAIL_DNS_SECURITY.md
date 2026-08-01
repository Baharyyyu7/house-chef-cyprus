# Email DNS Security

Production mail requires an approved provider, domain verification, SPF, DKIM, DMARC and MX where inbound mail is needed. Set SMTP credentials only in host secret storage. `app:mail-health --no-send --json` verifies configuration without sending; a real test mail requires explicit operational approval.
