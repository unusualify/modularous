# Modularous package — context

What this package is
- A Laravel package providing infrastructure and conventions for building modular applications (module discovery, service providers, Vite integration, Inertia/Vue conventions, coverage tooling).

What problems it solves
- Standardizes how self-contained modules are structured, discovered and bootstrapped.
- Provides utilities for assets (Vite), routing, views, translations, migrations and module-level service providers.
- Offers tooling for test coverage generation, analysis and CI checks.

Core concepts
- Module: self-contained folder under `modules/` with its own providers, routes, views, migrations, and assets.
- ServiceProvider-centric bootstrap: each module registers a provider that merges configs, routes and bindings.
- Vite/Asset bridge: centralized helpers to expose module asset entrypoints to the host app.
- Inertia/Vue conventions: modules provide components/pages following consistent locations and naming.
- Coverage tooling: console commands and services that generate and analyze PHP coverage for modules.

What must NEVER be done
- Modify global application behavior outside module boundaries (no changes to host app core files).
- Introduce cross-module tight coupling (direct class references across modules without contracts).
- Replace or remove established module discovery/registering conventions.
- Upgrade PHP or framework compatibility requirements silently (must be explicit in composer).

What the AI is allowed to do
- Propose, add or modify code strictly inside this package (`packages/modularous`) and its `tests` and `coverage-xml` artifacts.
- Create, update and run unit/integration tests under the package's `tests/` and `workbench/`.
- Add CI workflow files and test/coverage helpers that operate only on this package.
- Add documentation, context files, JSON rules and patterns under `.ai/` inside the package.

