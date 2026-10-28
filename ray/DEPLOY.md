# Veridian — cPanel / CyberPanel Deployment Guide

This app is a standard Laravel 12 project. On shared hosting you **do not need
npm, Vite, or SSH** — all CSS/JS/images are plain static files already committed
under `public/`.

There are two supported layouts. **Layout A is strongly recommended.**

---

## Layout A (recommended): app outside the web root

```
/home/CPUSER/
├── veridian/              <- the whole app EXCEPT public/  (not web-reachable)
└── public_html/           <- the CONTENTS of public/       (the web root)
```

If the domain's docroot cannot be changed, you can instead do:

```
/home/CPUSER/
├── veridian_app/          <- whole app
└── public_html/
    └── (a small index.php + .htaccess that forwards to veridian_app/public)
```

See **"Forwarding index.php"** below if `veridian_app` is used.

---

## Step 1 — Create the database (cPanel / CyberPanel UI)

1. Create a MySQL database (e.g. `cpuser_veridian`).
2. Create a MySQL user with a strong password, and **add that user to the database
   with ALL PRIVILEGES**.
3. Note down: DB name, DB user, DB password. Host is almost always `127.0.0.1`.

## Step 2 — Upload files

Upload via cPanel **File Manager** (or FTP):

- Put the app files (everything **except** `public/`) into `/home/CPUSER/veridian/`.
  - Do **NOT** upload `.env` from your local machine — create a fresh one (Step 4).
  - `vendor/` IS required. It is huge (~thousands of files); upload it as a single
    **zip** and extract on the server for speed.
  - You can skip: `node_modules/`, `.git/`, `tests/` (optional).
- Put the **contents** of `public/` into `/home/CPUSER/public_html/`.
  - This means `public/index.php`, `public/.htaccess`, `public/css`, `public/js`,
    `public/images`, `public/videos`, `public/avatars`, etc. go directly in `public_html/`.

> Tip: zip the project on your PC, upload the zip, then use File Manager → Extract.

## Step 3 — Point `public_html/index.php` at the app (Layout A, split folders)

Because the app now lives in `/home/CPUSER/veridian/` (sibling of `public_html`),
open `public_html/index.php` and edit the two `require` paths near the top:

```php
// FROM:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// TO:
require __DIR__.'/../veridian/vendor/autoload.php';
$app = require_once __DIR__.'/../veridian/bootstrap/app.php';
```

Leave `public/.htaccess` as-is — it already routes everything to `index.php`.

### Forwarding index.php (only for the `veridian_app/` variant)

If you cannot split folders and the whole app sits in `veridian_app/` while
`public_html/` is empty, create `public_html/index.php`:

```php
<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../veridian_app/public/index.php';
```

## Step 4 — Create the `.env` file

In `/home/CPUSER/veridian/`, create `.env` (use cPanel File Manager → New File).
Copy the contents of **`.env.production.example`** shipped in the repo and fill in:

- `APP_URL` = `https://yourdomain.com`
- `APP_KEY` = leave blank for now (filled in Step 6)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from Step 1
- `APP_DEBUG=false`, `APP_ENV=production`
- `INSTALL_TOKEN=` a long random string (e.g. 40 random chars)

## Step 5 — Import the database

1. Open **phpMyAdmin** for the DB from Step 1.
2. Select the database → **Import** → choose `database/veridian-export.sql`
   (shipped in the repo) → **Go**.

   That dump contains all tables and your content (traders, users, settings).
   It intentionally excludes sessions/cache/jobs, which are rebuilt automatically.

   *Alternative (no dump):* skip to Step 6 and the installer's migration step will
   build empty tables instead.

## Step 6 — Run the web installer (no SSH needed)

1. Temporarily ensure `INSTALL_TOKEN` is set in `.env`.
2. Visit, in your browser:

   ```
   https://yourdomain.com/install?token=YOUR_TOKEN
   ```

   To also (re)create a super admin in the same step:

   ```
   https://yourdomain.com/install?token=YOUR_TOKEN&admin_email=you@domain.com&admin_password=ChangeThis123
   ```

3. The page runs: DB check → migrations → directory permissions → cache clears →
   optional admin creation → production caches. Fix anything marked **fail** and
   re-run.

   > If `APP_KEY` is empty the app cannot decrypt sessions. The installer does not
   > generate the key (it must not be exposed in a URL). See Step 7 for the key.

## Step 7 — Generate the APP_KEY (no SSH)

Since `APP_KEY` must be set and you have no terminal, use **cPanel → Cron Jobs**
to run it once (or temporarily):

```
/usr/local/bin/php /home/CPUSER/veridian/artisan key:generate --force
```

Set the schedule to every minute, save, wait for it to run once, then **delete the
cron job**. Confirm `.env` now contains a `base64:...` `APP_KEY`.

If your host blocks cron, generate a key locally (`php artisan key:generate --show`
on your PC) and paste the printed `base64:...` value into the server `.env`.

## Step 8 — Permissions

In File Manager (or Step 6 already created the dirs), ensure these are writable
(`0755` folders is usually enough on cPanel; some hosts need `0775`):

```
veridian/storage/                          and all subfolders
veridian/storage/framework/cache
veridian/storage/framework/sessions
veridian/storage/framework/views
veridian/storage/logs
veridian/bootstrap/cache
public_html/images/traders                 (trader avatar uploads)
public_html/avatars                        (user avatar uploads)
```

## Step 9 — HTTPS + cookies

1. Enable AutoSSL / Let's Encrypt in cPanel so `https://yourdomain.com` works.
2. Once HTTPS is confirmed, add this to `.env` and re-run the installer (or clear
   config cache):

   ```
   SESSION_SECURE_COOKIE=true
   ```

## Step 10 — Lock down the installer

After a successful run:

1. **Remove `INSTALL_TOKEN`** from `.env` (this alone disables `/install` → 404).
2. Optionally delete the route line and `app/Http/Controllers/InstallController.php`
   plus `resources/views/install.blade.php`.

---

## Cron (recommended) — Laravel scheduler & queue

Even with `QUEUE_CONNECTION=database`, background jobs run only if the queue
worker is invoked. On cPanel add **one** cron job:

```
* * * * * /usr/local/bin/php /home/CPUSER/veridian/artisan schedule:run >> /dev/null 2>&1
```

If you rely on queued jobs, also import/inspect `jobs` table and either switch
`QUEUE_CONNECTION=sync` (runs inline, no worker) or add a worker cron:

```
* * * * * /usr/local/bin/php /home/CPUSER/veridian/artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

For a mostly-synchronous app on shared hosting, setting `QUEUE_CONNECTION=sync`
in `.env` is the simplest and needs no cron.

---

## CyberPanel notes

- CyberPanel sites use `/home/DOMAIN/public_html` as the docroot by default.
  Use the same Layout A split: app in `/home/DOMAIN/veridian/`, public contents in
  `/home/DOMAIN/public_html/`.
- PHP version: choose **PHP 8.2+** in CyberPanel → Websites → Manage → PHP.
- Cron is available under Websites → Manage → Cron Jobs.
- Use the built-in **phpMyAdmin** or `vHost` database tools for the import.

---

## Quick checklist

- [ ] DB created, user granted ALL PRIVILEGES
- [ ] App files uploaded to `veridian/`, public contents to `public_html/`
- [ ] `index.php` require paths edited (split-folder setup)
- [ ] `.env` created with real DB creds, `APP_ENV=production`, `APP_DEBUG=false`
- [ ] SQL dump imported (or installer migrations run)
- [ ] `/install?token=...` run successfully
- [ ] `APP_KEY` generated (cron `key:generate` or pasted from local)
- [ ] storage + bootstrap/cache + upload dirs writable
- [ ] HTTPS enabled and `SESSION_SECURE_COOKIE=true` set
- [ ] `INSTALL_TOKEN` removed from `.env`
- [ ] Cron `schedule:run` added (and queue worker or `QUEUE_CONNECTION=sync`)

---

## Troubleshooting

**500 error, blank page** — set `APP_DEBUG=true` in `.env` temporarily and reload to
see the real error. Remember to set it back to `false`.

**"No application encryption key"** — `APP_KEY` is empty; do Step 7.

**CSS/JS 404s** — the contents of `public/` were not placed correctly. `public_html/`
should contain `css/`, `js/`, `images/` as direct subfolders.

**Sessions not persisting / logged out immediately** — confirm `sessions` table was
created (run installer migrations) OR set `SESSION_DRIVER=file` in `.env`.

**"Please provide a valid cache path"** — `storage/framework/views` or
`bootstrap/cache` missing/not writable; re-run the installer or chmod them.

**Images/uploads fail** — `public/images/traders` and `public/avatars` not writable.
