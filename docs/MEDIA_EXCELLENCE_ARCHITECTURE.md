# Media Excellence Architecture

Milestone D separates candidate discovery, private import, human review and public delivery. Candidate metadata and imported files remain private until a reviewer completes the fidelity, licence and attribution decisions. `approved_real_media` is the only public eligibility state.

## State boundary

- Candidate scans create or update private review metadata only.
- A private import checks HTTPS host allowlists, response size, image signature, decodes/re-encodes the file, removes original EXIF payload and records a checksum.
- Review is explicit and audited. A candidate cannot become public through an audit, optimisation, dashboard view or scheduled task.
- A public asset must be verified, non-placeholder, fidelity-reviewed, licence-approved and attribution-approved.

The media health score is a real operational score. It is capped when scans, licences, human approval, fidelity or responsive variants are incomplete.
