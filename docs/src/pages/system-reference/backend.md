---
sidebarPos: 5
sidebarTitle: Backend
---

# Backend

## Controllers

**Hierarchy**: CoreController → PanelController → BaseController

| Layer | Purpose |
|-------|---------|
| **CoreController** | Base HTTP controller |
| **PanelController** | Route/model resolution, index options, authorization, `$this->repository` |
| **BaseController** | View prefix, form schema, index/create/edit flow, `setupFormSchema()` |

**Key traits** (BaseController): ManageIndexAjax, ManageInertia, ManagePrevious, ManageSingleton, ManageTranslations

**Flow**: `preload()` → `addWiths()`, `setupFormSchema()` → `index()` / `create()` / `edit()` → `respondToIndexAjax()` for AJAX

## Console Commands

Discovered via `CommandDiscovery::discover()` in BaseServiceProvider.

| Category | Path | Examples |
|----------|------|----------|
| Make | Console/Make/ | make:model, make:controller, make:route, make:repository |
| Cache | Console/Cache/ | cache:clear, cache:warm, cache:list |
| Migration | Console/Migration/ | migrate, migrate:refresh, migrate:rollback |
| Module | Console/Module/ | route:enable, route:disable, route:status |
| Roles | Console/Roles/ | roles:load, roles:refresh, roles:list |
| Setup | Console/Setup/ | install, create-superadmin |
| Seed | Console/Seed/ | seed:payment, seed:pricing |
| Build | Console/ | build, refresh |

**Key commands**:
- `modularity:build` — rebuild Vue assets
- `modularity:route:enable` / `modularity:route:disable` — toggle routes
- `modularity:route:status` — list route status per module

## Entities

**Base**: `Model`, `Singleton`

**Core models**: User, UserOauth, Profile, Company, Setting, Tag, Tagged, Media, File, Filepond, Block, Repeater, RelatedItem, Revision, Process, ProcessHistory, Chat, ChatMessage, Assignment, Authorization, CreatorRecord, Feature, State, Stateable, Spread

**Entity traits** (examples): HasImages, HasFiles, HasFileponds, HasSlug, HasStateable, HasPriceable, HasPayment, HasPosition, HasCreator, HasRepeaters, HasProcesses, HasTranslation, IsTranslatable, Assignable, Chatable, Processable

**Enums**: Permission, UserRole, RoleTeam, ProcessStatus, PaymentStatus, AssignmentStatus

## Services

| Service | Purpose |
|---------|---------|
| Connector | Connector service |
| MigrationBackup | Migration backup |
| Currency/SystemPricingCurrencyProvider | Currency from system pricing |
| Currency/NullCurrencyProvider | No-op when no pricing module |
| Roles/AbstractRolesLoader | Base roles loader |
| Roles/CmsRolesLoader, CrmRolesLoader, ErpRolesLoader | Role definitions |
| FilepondManager | Filepond uploads |
| ModularityCacheService | Cache management |

## Support

| Class | Purpose |
|-------|---------|
| **Finder** | Resolve model/repository/controller from route name or table |
| **RouteGenerator** | Scaffold routes, migrations, controllers, repositories from module config |
| **CommandDiscovery** | Discover commands from glob paths |
| **FileLoader** | Translation file loader |
