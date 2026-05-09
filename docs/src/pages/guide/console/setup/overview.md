---
sidebarPos: 13
sidebarTitle: Overview
sidebarGroupTitle: Setup
---

# Setup Commands

Installation and initial configuration commands. Run these once when setting up a new project or developer environment.

| Command | Signature | Description |
|---------|-----------|-------------|
| [install](./install) | `modularous:install` | Full Modularous installation (publishes config, runs migrations, sets up auth) |
| [setup:development](./setup-development) | `modularous:setup:development` | Configure a local dev environment (symlinks, env, permissions) |
| [create:database](./create-database) | `modularous:create:database` | Create the application database if it does not exist |
| [create:superadmin](../generators/create-superadmin) | `modularous:create:superadmin` | Create the initial superadmin user account |

::: tip
Run `modularous:install` first, then `modularous:create:superadmin` to bootstrap a fresh project.
:::

## Common Workflows

### First install on a fresh project

```bash
composer require unusualify/modularous
php artisan modularous:create:database       # optional — only if the DB doesn't exist yet
php artisan modularous:install               # publishes config + runs migrations + sets up auth
php artisan modularous:create:superadmin     # creates the first admin user
php artisan modularous:build                 # build frontend assets
```

### Bootstrapping a dev machine after a fresh clone

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan modularous:setup:development     # symlinks, storage perms, optional seeders
php artisan modularous:migrate
php artisan modularous:build
```

### Re-running install safely

`modularous:install` is **idempotent** — it detects existing config and migrations and skips them. Re-run it when you adopt Modularous in an existing Laravel app, or after upgrading to pick up new publishable assets.

## Related

- [Installation Guide](/get-started/installation-guide) — the full first-time setup tutorial
- [Upgrading](/get-started/upgrading) — what to run when moving between versions
- [Update commands](../update/overview) — config patchers invoked during install / upgrade
