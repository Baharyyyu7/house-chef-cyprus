# Blockers

## External / human-controlled

- Production backup create, verify, and health commands need an authorized
  production SSH session and must be run only after production release gates
  pass. No result is inferred from local tests.
- SMTP acknowledgement and notification delivery need production mail settings
  and controlled mailbox verification.
- Editorial fact-check, recipe changes, and media approval require accountable
  human reviewers. Automation may prepare candidates but may not approve or
  publish them.
- Legal text changes require the supplied approved text and, where required,
  legal-owner review.

## Not blockers to local work

- 2,340 human review records remain operational backlog; they are not silently
  resolved or represented as automated approval.
