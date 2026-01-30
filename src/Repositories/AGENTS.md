# Repository Instructions

Short guidelines for Modularity repositories.

- Purpose: Encapsulate data access and query logic for entities.
- Location: `src/Repositories/` — name classes `SomethingRepository`.
- Keep business logic in Services; Repositories only handle persistence.
- Tests: add unit tests under `tests/Repositories` using factories.

- Location: place repository classes under `src/Repositories/` and related Traits under `src/Repositories/Traits`.
- Naming: use PascalCase for classes (e.g. `ArticleRepository`, `UserProfileRepository`).
- Naming Feature Traits: use Trait suffix for feature traits (e.g. `VersioningTrait`, `MediasTrait`).
