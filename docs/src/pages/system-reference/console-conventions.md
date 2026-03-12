---
sidebarPos: 11
sidebarTitle: Console Conventions
---

# Console Command Conventions

Class names must reflect their command signature. Convert signature parts to PascalCase and append `Command`.

## Naming Rules

| Signature Part | Class Name Part | Example |
|----------------|-----------------|---------|
| `modularity:make:module` | MakeModuleCommand | make + module |
| `modularity:cache:clear` | CacheClearCommand | cache + clear |
| `modularity:route:disable` | RouteDisableCommand | route + disable |

## Semantic Rules

### `modularity:make:*` — Artifact generators

Commands that scaffold or generate files. All live in `Console/Make/`.

- **Class:** `Make*Command` (e.g. `MakeModuleCommand`, `MakeControllerCommand`)
- **Examples:** `make:module`, `make:controller`, `make:migration`

### `modularity:create:*` — Runtime creation

Commands that create runtime records (DB entries, users).

- **Class:** `Create*Command` (e.g. `CreateSuperAdminCommand`)
- **Examples:** `create:superadmin`

### Other namespaces

| Namespace | Pattern | Example |
|-----------|---------|---------|
| `modularity:cache:*` | Cache*Command | CacheClearCommand |
| `modularity:migrate:*` | Migrate*Command | MigrateCommand |
| `modularity:flush:*` | Flush*Command | FlushCommand |
| `modularity:route:*` | Route*Command | RouteDisableCommand |
| `modularity:sync:*` | Sync*Command | SyncTranslationsCommand |
| `modularity:replace:*` | Replace*Command | ReplaceRegexCommand |

## Class Naming by Folder

| Folder | Pattern | Example |
|--------|---------|---------|
| Console/ (root) | *Command | BuildCommand, ReplaceRegexCommand |
| Make/ | Make*Command | MakeModuleCommand |
| Cache/ | Cache*Command | CacheClearCommand |
| Migration/ | Migrate*Command | MigrateCommand |
| Module/ | *Command | RouteDisableCommand |
| Roles/ | Roles*Command | RolesLoadCommand |
| Setup/ | *Command | InstallCommand, CreateSuperAdminCommand |
| Seed/ | Seed*Command | SeedPaymentCommand |
| Sync/ | Sync*Command | SyncTranslationsCommand |
| Operations/ | *Command | ProcessOperationsCommand |
| Flush/ | Flush*Command | FlushCommand |
| Update/ | Update*Command | UpdateLaravelConfigsCommand |
| Docs/ | Generate*Command | GenerateCommandDocsCommand |
| Schedulers/ | *Command | (package root) |

## Command Mapping

| Signature | Class |
|-----------|-------|
| modularity:make:* | Make*Command |
| modularity:create:superadmin | CreateSuperAdminCommand |
| modularity:create:database | CreateDatabaseCommand |
| modularity:install | InstallCommand |
| modularity:setup:development | SetupModularityDevelopmentCommand |
| modularity:cache:list | CacheListCommand |
| modularity:cache:clear | CacheClearCommand |
| modularity:cache:versions | CacheVersionsCommand |
| modularity:cache:graph | CacheGraphCommand |
| modularity:cache:stats | CacheStatsCommand |
| modularity:cache:warm | CacheWarmCommand |
| modularity:flush | FlushCommand |
| modularity:flush:sessions | FlushSessionsCommand |
| modularity:flush:filepond | FlushFilepondCommand |
| modularity:route:disable | RouteDisableCommand |
| modularity:route:enable | RouteEnableCommand |
| modularity:fix:module | FixModuleCommand |
| modularity:remove:module | RemoveModuleCommand |
| modularity:replace:regex | ReplaceRegexCommand |
| modularity:db:check-collation | CheckDatabaseCollationCommand |
