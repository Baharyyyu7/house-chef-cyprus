# Media storage strategy

House Chef Cyprus stores published recipe and ingredient images on the `public` disk. Private uploads, temporary processing files, exports and backups remain on the `local` private disk and are never linked directly into the web root.

`MediaStorageInterface` keeps domain callers independent from the local disk. The current `LocalMediaStorage` validates and re-encodes images; an S3-compatible implementation can replace it without changing controllers or jobs.

Every media record uses a public ULID, a random generated storage name, visibility, dimensions, processing state and optional thumbnail paths. Original client file names are not persisted as paths. Private media uses an authenticated admin download route; it is not exposed through `storage:link`.

## Thumbnail lifecycle

`GenerateMediaThumbnailsJob` runs on the `media` queue after commit, is unique per media public ID, and has a bounded timeout/retry/backoff policy. It produces aspect-preserving JPEG small (320), medium (768), and large (1440) variants. New paths replace the record only on success, so valid previous thumbnails remain available after a failed regeneration.

## Retention and cleanup

`media:scan-orphans` and `media:cleanup-temporary` are dry-run by default. Their `--delete` option accepts only safe prefixed paths, writes an audit entry, and is scheduled daily. Temporary retention defaults to 24 hours. Raw upload retention is deliberately not enabled until an import/media provenance policy requires it.
