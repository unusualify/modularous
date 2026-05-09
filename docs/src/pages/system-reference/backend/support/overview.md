---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Support Classes
---

# Support Classes

The `src/Support/` directory contains internal utility classes that power scaffolding, routing, asset compilation, coverage analysis, and file manipulation. They are not meant to be extended directly by module authors but understanding them helps when debugging or contributing to Modularous internals.

## Class Reference

| Class | Namespace | Purpose |
|-------|-----------|---------|
| [CommandDiscovery](./command-discovery) | `Support` | Scans glob paths and returns instantiable `Command` FQCNs |
| [CoverageAnalyzer](./coverage-analyzer) | `Support` | Parses Clover XML and reports per-method coverage |
| [FileLoader](./file-loader) | `Support` | Extends Laravel's translation `FileLoader` with multi-path support |
| [Finder](./finder) | `Support` | Resolves model and repository classes by table or route name |
| [HostRouting / HostRouteRegistrar](./host-routing) | `Support` | Fluent API for registering multi-tenant host-based route groups |
| [ModularousRoutes](./modularous-routes) | `Support` | Defines route group options and registers all middleware aliases |
| [ModularousVite](./modularous-vite) | `Support` | Extends Laravel `Vite` for Modularous asset manifest |
| [RegexReplacement](./regex-replacement) | `Support` | Batch regex find-and-replace across a directory tree |
| [Decomposers](./decomposers) | `Support\Decomposers` | Parse schema/relation/validation strings into arrays for generators |
| [Migrations\SchemaParser](./migrations-schema-parser) | `Support\Migrations` | Renders migration `$table->…` PHP from a schema definition string |

## Sub-namespaces

```
src/Support/
├── CommandDiscovery.php
├── CoverageAnalyzer.php
├── FileLoader.php
├── Finder.php
├── HostRouteRegistrar.php
├── HostRouting.php
├── ModularousRoutes.php
├── ModularousVite.php
├── RegexReplacement.php
├── Decomposers/
│   ├── ModelRelationParser.php
│   ├── SchemaParser.php
│   └── ValidatorParser.php
└── Migrations/
    └── SchemaParser.php
```
