# Media Optimization

The bounded `media:optimize` command generates deterministic JPEG fallbacks and, when supported by the installed GD build, WebP and AVIF derivatives. Widths and quality are centralised in `config/media_excellence.php`. Processing is idempotent and intended for small shared-hosting batches.

Only approved local real media is optimized. Official Unsplash hotlinks remain provider-managed and are not copied by this command.
