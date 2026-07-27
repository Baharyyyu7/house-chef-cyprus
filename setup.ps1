Copy-Item .env.example .env -ErrorAction SilentlyContinue
New-Item -ItemType File database/database.sqlite -Force | Out-Null
composer install; php artisan key:generate --force; php artisan migrate --seed; npm ci; npm run build
