---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Generators
---

# Generators

Generators are the scaffolding engine behind Modularous `make:*` and `make:route` commands. They produce the full set of PHP and JS files that constitute a new module route, plus the test scaffolding for both the frontend and backend.

## Class Hierarchy

```
Generator (abstract)                ← NwidartGenerator + ReplacementTrait
├── RouteGenerator                  ← full-stack route scaffolding (primary generator)
├── StubsGenerator                  ← stub-only regeneration (fix/patch workflow)
├── VueTestGenerator                ← Vitest/Jest test file scaffolding
└── LaravelTestGenerator            ← PHPUnit test file scaffolding
```

## Generator Reference

| Generator | Source | Responsibility |
|-----------|--------|----------------|
| [`Generator`](./generator) | `Generators/Generator.php` | Abstract base — shared properties, module resolution, config path helpers |
| [`RouteGenerator`](./route-generator) | `Generators/RouteGenerator.php` | Creates the full set of files for a new module route (model, migration, controller, repository, request, translations, permissions) |
| [`StubsGenerator`](./stubs-generator) | `Generators/StubsGenerator.php` | Regenerates stub-based files only; supports selective overwrite via `only`/`except` lists |
| [`VueTestGenerator`](./vue-test-generator) | `Generators/VueTestGenerator.php` | Scaffolds a Vitest/Jest test file for a Vue component, composable, utility, or store |
| [`LaravelTestGenerator`](./laravel-test-generator) | `Generators/LaravelTestGenerator.php` | Scaffolds a PHPUnit Unit or Feature test file |

## How Generators Are Invoked

Generators are not called directly in application code — they are orchestrated by Artisan commands:

| Command | Generator |
|---------|-----------|
| `modularous:make:route` | `RouteGenerator` |
| `modularous:fix:route` | `RouteGenerator` (with `--fix`) |
| `modularous:make:stubs` | `StubsGenerator` |
| `modularous:make:test:vue` | `VueTestGenerator` |
| `modularous:make:test:laravel` | `LaravelTestGenerator` |
