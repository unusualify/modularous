# Console Command Naming Conventions

## Rule: Class Name ↔ Signature Compatibility

**Class names must reflect their command signature.** Convert signature parts to PascalCase and append `Command`.

| Signature Part | Class Name Part | Example |
|----------------|-----------------|---------|
| `modularity:make:module` | MakeModuleCommand | make + module |
| `modularity:cache:clear` | CacheClearCommand | cache + clear |
| `modularity:route:disable` | RouteDisableCommand | route + disable |
| `modularity:replace:regex` | ReplaceRegexCommand | replace + regex |

## Semantic Rules

### `modularity:make:*` — Artifact generators
Commands that **scaffold or generate files**. All live in `Console/Make/`.

- **Class:** `Make*Command` (e.g. `MakeModuleCommand`, `MakeControllerCommand`)
- **Examples:** `make:module`, `make:controller`, `make:migration`

### `modularity:create:*` — Runtime creation
Commands that **create runtime records** (DB entries, users).

- **Class:** `Create*Command` (e.g. `CreateSuperAdminCommand`)
- **Examples:** `create:superadmin`

### Other namespaces
- `modularity:cache:*` → `Cache*Command` (CacheClearCommand, CacheListCommand)
- `modularity:migrate:*` → `Migrate*Command` (MigrateCommand, MigrateRefreshCommand)
- `modularity:flush:*` → `Flush*Command` (FlushCommand, FlushSessionsCommand)
- `modularity:route:*` → `Route*Command` (RouteDisableCommand, RouteEnableCommand)
- `modularity:sync:*` → `Sync*Command` (SyncTranslationsCommand, SyncStatesCommand)
- `modularity:replace:*` → `Replace*Command` (ReplaceRegexCommand)

## Class Naming Pattern by Folder

| Folder    | Pattern            | Example                     |
|-----------|--------------------|-----------------------------|
| Make/     | `Make*Command`     | MakeModuleCommand           |
| Setup/    | `*Command`         | CreateSuperAdminCommand, InstallCommand |
| Cache/    | `Cache*Command`    | CacheClearCommand           |
| Migration/| `Migrate*Command`  | MigrateCommand              |
| Flush/    | `Flush*Command`    | FlushCommand, FlushSessionsCommand |
| Module/   | `*Command`         | RouteDisableCommand, FixModuleCommand |
| Sync/     | `Sync*Command`     | SyncTranslationsCommand      |
| Docs/     | `Generate*Command` | GenerateCommandDocsCommand  |

## Full Command Mapping (Signature ↔ Class)

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
