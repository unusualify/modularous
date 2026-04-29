---
sidebarPos: 6
sidebarTitle: Coverage Watch
---

# Coverage Watch

> Poll a Clover XML file and display a diff whenever coverage changes — useful for a live TDD feedback loop.

## Command Information

- **Signature:** `coverage:watch [--cloverName=] [--cloverDir=] [--interval=5]`
- **Category:** Coverage

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--cloverName` | `clover.xml` | Clover XML file name to watch |
| `--cloverDir` | `storage/app` | Directory containing the Clover file |
| `--interval` | `5` | Polling interval in seconds |

## What It Does

The command reads the Clover file on startup and saves the initial coverage snapshot. It then re-reads the file every `--interval` seconds. When coverage numbers change — either because tests were re-run or because the file was regenerated — it prints a diff showing which files improved or regressed.

Press `Ctrl+C` to stop watching.

## Examples

### Watch with default settings

```bash
php artisan coverage:watch
```

### Check every 2 seconds

```bash
php artisan coverage:watch --interval=2
```

### Watch a custom Clover file

```bash
php artisan coverage:watch --cloverDir=build --cloverName=coverage.xml
```

## TDD Workflow

Keep the watcher running in a terminal split while you write tests:

```bash
# Terminal 1 — run tests continuously
php artisan test --coverage-clover=storage/app/clover.xml --watch

# Terminal 2 — watch coverage change in real time
php artisan coverage:watch --interval=3
```

## Related

- [coverage:analyze](/guide/console/coverage/coverage-analyze) — one-shot analysis
- [coverage:generate-tests](/guide/console/coverage/coverage-generate-tests) — scaffold missing tests
