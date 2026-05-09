# Backend Core Instructions

Concise prompts for agents working in `src/` (backend). Keep requests repository-scoped, state expected file/path, constraints, and tests.

Format:
- Action | Path | Constraints | Tests

Examples:
- Add trait | src/Entities/Traits/HasVersioning.php | PHP 8.1 types, PSR-12, register in ServiceProvider | tests/Entities/HasVersioningTest.php
- Implement service | src/Services/CoverageService.php | Use DI, return typed DTOs, no facades | tests/Services/CoverageServiceTest.php
- Update provider | src/Providers/ModularousProvider.php | Register bindings, publish config | tests/Providers/ModularousProviderTest.php

Keep prompts short and concrete — avoid high-level product requests. Always include a target path and a test expectation.

Core classes (short):
- `Modularous.php` | Module manager extending Nwidart FileRepository. Handles scanning, caching, vendor/app paths, auth names, and helpers (e.g. `scan()`, `allEnabled()`, `getVendorPath()`).
- `Module.php` | Represents a single module. Loads config/providers/commands, exposes route names, middleware aliases, paths and helpers (e.g. `getConfig()`, `getRouteNames()`, `getDirectoryPath()`).

When asking agents to change module behavior, reference these classes and a test file (e.g. `tests/Services/ModularousBehaviorTest.php`).