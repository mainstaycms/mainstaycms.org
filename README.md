# mainstay-site

Mainstay's own project site, and the host its phases are tried against. Every page is an entry,
written by the seeders `database/seeders/ContentSeeder.php` calls and served by Mainstay's catch-all; there is no
route of its own.

It needs `mainstay` checked out beside it: `composer.json` takes `mainstay/cms` and `mainstay/ui`
from `../mainstay/packages` as symlinked path repositories.

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan vendor:publish --tag=mainstay-assets
composer content      # a fresh database: migrate, mainstay:sync, seed
php artisan serve
```

The site is at http://mainstay.localhost:8000. A `.localhost` name resolves only on the machine
running the server; to reach the site from another device, set `MAINSTAY_URL_EN` in `.env` to a host
that resolves there.
