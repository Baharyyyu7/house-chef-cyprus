# Private Media Import

Imports use configured HTTPS hosts only. They reject untrusted URLs before network access, disable redirects, apply a bounded timeout and byte limit, verify the image signature, then decode and re-encode into private storage. SVG and executable payloads are not accepted. Duplicate checks use checksum and perceptual-hash review queues.

Private assets are not valid public, OG, schema or image-sitemap media.
