---
sidebarPos: 12
sidebarTitle: Staging Guard
---

# Staging Guard

A staging app usually runs against a copy of production data. The staging guard keeps that copy read-only and silent: with `MODULAROUS_STAGING=true` every side effect is locked, and each one is unlocked explicitly through `.env`.

Nothing is registered while `MODULAROUS_STAGING` is `false`, so production and local environments are unaffected.

## What is locked

| Side effect | Where it is enforced | Unlocked by |
|---|---|---|
| Repository writes (`create`, `update`, `delete`, `restore`, bulk variants, `duplicate`, `updateBasic`) | `Repository` checks the model table before writing | `MODULAROUS_STAGING_WRITABLE_TABLES` |
| Any SQL write during an HTTP request (`INSERT`, `UPDATE`, `DELETE`, `REPLACE`, `TRUNCATE`, `CREATE/ALTER/DROP TABLE`) | `beforeExecuting` hook on every database connection | `MODULAROUS_STAGING_WRITABLE_TABLES` |
| Non-GET panel requests | `modularous.staging` middleware on the default panel stack | `MODULAROUS_STAGING_WRITABLE_ROUTES` |
| Notifications | `NotificationSending` listener | `MODULAROUS_STAGING_NOTIFICATIONS` |
| Mail | `MessageSending` listener | `MODULAROUS_STAGING_MAIL` |
| Queued jobs, listeners, notifications, mailables, broadcasts | Every queue connection uses the `staging` driver | `MODULAROUS_STAGING_QUEUE_JOBS` |
| Scheduled tasks | `schedule:run` / `schedule:work` skip them | `MODULAROUS_STAGING_SCHEDULE_COMMANDS` |
| Laravel HTTP client requests | Global request middleware | `MODULAROUS_STAGING_HTTP_HOSTS` |
| Artisan commands | `CommandStarting` listener | `MODULAROUS_STAGING_COMMANDS` |

Locked writes, routes, hosts and commands throw `StagingLockedException` (HTTP 423). JSON requests receive `{ message, variant: "error", staging: true }`; the message names the env key that unlocks the action. Skipped notifications, mails and queued jobs are written to the log.

## Keys

All list values are comma-separated and accept `*` globs.

| Key | Default | Meaning |
|---|---|---|
| `MODULAROUS_STAGING` | `false` | Turns the guard on |
| `MODULAROUS_STAGING_LABEL` | empty | Text shown in the panel banner, e.g. `PR #73` |
| `MODULAROUS_STAGING_WRITABLE_TABLES` | empty | Tables that may be written. List related tables too (`press_release*,mediables`) |
| `MODULAROUS_STAGING_WRITABLE_ROUTES` | empty | Route names that accept POST/PUT/PATCH/DELETE (`admin.press-release.*`) |
| `MODULAROUS_STAGING_NOTIFICATIONS` | empty | Notification channels that may send (`database`, `mail`, `*`) |
| `MODULAROUS_STAGING_MAIL` | `false` | Whether the mailer may send |
| `MODULAROUS_STAGING_QUEUE_JOBS` | empty | Classes that may run; they run synchronously (`App\Jobs\GeneratePdf`) |
| `MODULAROUS_STAGING_SCHEDULE_COMMANDS` | empty | Scheduled commands or task descriptions that may run (`modularous:fileponds:*`) |
| `MODULAROUS_STAGING_HTTP_HOSTS` | empty | Hosts the HTTP client may call (`*.zoho.com`) |
| `MODULAROUS_STAGING_COMMANDS` | empty | Extra Artisan commands on top of the defaults |
| `MODULAROUS_STAGING_GUARD_CONSOLE_QUERIES` | `false` | Also apply the SQL guard to console commands |

## Always allowed

- **Tables:** `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `activity_log`.
- **Routes without guards:** sign-in, two-factor, step-up and sign-out (`*login`, `*login-2fa`, `*logout`, `*step-up.verify`, `*step-up.resend`) run with table and SQL guards lifted so users can sign in.
- **Commands:** `list`, `help`, `about`, `env`, `migrate`, `migrate:status`, `operations:process`, `package:discover`, `vendor:publish`, `storage:link`, `key:generate`, `optimize`, `optimize:clear`, `config:*`, `route:*`, `view:*`, `event:*`, `schedule:run`, `schedule:work`, `schedule:list`, `modularous:staging:status`.

The SQL guard only applies to HTTP requests by default, so deploy commands (`migrate`, `operations:process`) can still change the schema and data.

## Example

Lock everything except editing press releases, their translations, slugs and media:

```dotenv
MODULAROUS_STAGING=true
MODULAROUS_STAGING_LABEL="PR #73"
MODULAROUS_STAGING_WRITABLE_TABLES=press_release*,mediables
MODULAROUS_STAGING_WRITABLE_ROUTES=admin.press-release.*
MODULAROUS_STAGING_NOTIFICATIONS=database
```

When a write fails with "insert on table \"x\" is locked", add `x` to `MODULAROUS_STAGING_WRITABLE_TABLES`.

## Diagnostics

```bash
php artisan modularous:staging:status
```

Prints whether the guard is on and what each lock currently allows. Inertia pages also receive a shared `staging` prop (`{ enabled, label }`), and the panel layouts show a small "Staging: changes are locked" banner.

## Limits

- The HTTP lock covers the Laravel HTTP client (`Http::`), not raw Guzzle or cURL clients.
- Jobs dispatched with `dispatchSync` bypass the queue, so only the SQL and HTTP guards apply to them.
- For a hard guarantee, connect the staging app with a database user that only has `SELECT` on business tables.
