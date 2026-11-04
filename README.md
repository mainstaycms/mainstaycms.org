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

## Deploying

A push to `main` deploys to https://mainstaycms.org through `.github/workflows/deploy.yml`.

1. The tests run, and the migrations are checked against `app/Content` on MySQL 8.4 with
   `mainstay:schema:check`. Production never runs `mainstay:sync`, so a field added to a content type
   needs a migration in `database/migrations` before it deploys.
2. The release is built on the runner: `composer install --no-dev`, the Mainstay assets published,
   and the lot packed into one tarball. The server needs neither composer nor node.
3. The runner joins NetBird as a temporary peer in the `deploy` group, which may reach the server's
   SSH and nothing else, and pipes the tarball to the deploy key. That key can only run
   `deploy/receive-release`, installed root-owned on the server as
   `/usr/local/sbin/receive-mainstaycms-org-release`. It unpacks the release beside the earlier
   ones, links the shared `.env` and `storage/`, migrates, caches, switches `current` to it and
   reloads PHP-FPM. A release that fails before going live is removed; the five newest are kept.

What it needs outside this repository:

- GitHub environment `production`, limited to `main`: secrets `NETBIRD_SETUP_KEY` (a reusable,
  ephemeral key for the `deploy` group) and `DEPLOY_SSH_KEY`, variable `DEPLOY_KNOWN_HOSTS` (the
  server's ed25519 host key).
- NetBird policy "Deploy to web": `deploy` to `web`, TCP 22.
- `shared/.env` sets `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and `MAINSTAY_URL_EN` to
  https://mainstaycms.org, and MySQL over its socket as the site's user (`auth_socket`, no password).
  `www-data` may read `shared/storage/app/public`, which `public/storage` links to.
- On the server, root's `authorized_keys` holds the deploy key as
  `restrict,from="10.100.0.0/16",command="/usr/local/sbin/receive-mainstaycms-org-release"`, and
  `/srv/www/mainstaycms.org/sites/mainstaycms.org` holds `releases/`, `shared/.env`,
  `shared/storage/` with Laravel's `app/public`, `app/private`, `framework/cache/data`,
  `framework/sessions`, `framework/views` and `logs` in it, and `current`, which Apache's DocumentRoot points into as `current/public`.

A change to `deploy/receive-release` reaches the server by hand, never through CI.

A deploy only migrates, so what is edited in the admin stays. The content was seeded once, by
hand, after the first deploy. The seeders read `.env`, which a cached config hides, so the cache is
cleared for the seed and rebuilt after it:

```bash
cd /srv/www/mainstaycms.org/sites/mainstaycms.org/current
runuser -u mainstaycms_org -- sh -c 'php artisan config:clear && php artisan db:seed --force && php artisan optimize'
```

To roll back, point `current` at an earlier release and reload PHP-FPM:

```bash
cd /srv/www/mainstaycms.org/sites/mainstaycms.org
runuser -u mainstaycms_org -- ln -sfn "$PWD/releases/<earlier>" current.next
runuser -u mainstaycms_org -- mv -T current.next current
systemctl reload php8.5-fpm
```

A rollback leaves the database as the newer release migrated it.
