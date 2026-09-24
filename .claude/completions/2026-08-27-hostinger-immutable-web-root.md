# Hostinger immutable web-root bootstrap

- Root cause: PHP-FPM retained the resolved target of `../current` after an atomic release switch, so dynamic requests continued using the previous release while CLI used the new release.
- Fix: deploy and rollback now write the resolved immutable release paths into `public_html/index.php`.
- Verification: Hostinger deploy script tests, Bash syntax, Pint, PHPStan, and `git diff --check` pass.
- Production verification must confirm the new Search Console meta token and the static verification file over HTTPS.
