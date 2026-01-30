# Entities — Cursor AGENTS

Purpose
- Quick guidance for Cursor AI when working on the `src/Entities` folder.

Conventions
- Location: place entity classes under `src/Entities` and related Traits under `src/Entities/Traits`.
- Naming: use PascalCase for classes (e.g. `Article`, `UserProfileEntity`).
- Patterns: always use Repository + Service layers; avoid direct model queries in controllers.
- Traits: factor reusable behavior into Traits (e.g. `HasVersioning`, `HasMedias`).

Testing & Registration
- Add unit tests under `tests/Entities`.

Style
- Follow PHP 8.1+ type hints, PSR-12, and include PHPDoc on public methods.
