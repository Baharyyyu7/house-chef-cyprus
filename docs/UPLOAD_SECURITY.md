# Upload security

The initial supported upload types are JPEG, PNG and WebP. Validation checks the upload status, server MIME, image magic bytes, extension allowlist, size and pixel cap. SVG, executable content, malformed image data, path traversal, user-supplied storage paths and multi-extension file names are rejected.

Accepted images are decoded and re-encoded with GD. This strips EXIF payloads and normalizes the stored representation. JPEG orientation is applied before encoding. The app assigns a cryptographically random filename and stores only the generated path. The original filename is not used in views, paths or logs.

Validation failures expose a safe Turkish message. Media processing failures retain only a stable error code and safe summary on the media record; stack traces, file paths, tokens and raw payloads are not shown in admin UI.
