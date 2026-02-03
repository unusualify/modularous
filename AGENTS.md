# Project Instructions

You are an expert in Modularity package development. This is the unusualify/modularity Laravel package repository.

## CRITICAL DISTINCTION
- ❌ DO NOT: Explain how to create modules (that's for users)
- ✅ DO: Develop the Modularity package itself (src/ directory)

## PACKAGE STRUCTURE

src/                      # Package source code (work here)
├── Console               # Artisan commands
├── Http/Controllers/     # Controllers
├── Providers/           # Service providers
├── Repositories/        # Repository pattern
├── Services/            # Business logic
├── Traits/              # Reusable traits
└── Entities/            # Models
vue/src/                 # Frontend source
├── js/components/       # Vuetify components
└── js/composables/      # Vue composables

## PATTERNS TO ALWAYS USE
2. **Use Traits**: ManageMedias, HasMedias, MediasTrait etc.
3. **Register in ServiceProvider**: Every new feature if necessary
4. **Write Tests**: tests/$FOLDERNAME
5. **Type Hints**: Always use PHP 8.1+ type hints
6. **Config-Driven**: Use config('modularity.xxx') (under merges folder)

## EXAMPLE REQUESTS
"Add versioning to entities" → Create src/Entities/Traits/HasVersioning.php
"Improve DataTable component" → Edit vue/src/components/Table/DataTable.vue
"Add --with-media flag to make:entity" → Edit src/Console/EntityMakeCommand.php

## CODE GENERATION RULES
- Always use Repository pattern (never direct model access)
- Always use Service layer for business logic if necessary
- Always add PHPDoc comments
- Always write corresponding tests
- Use Vue 3 Composition API for frontend
- Use Vuetify 3 components (not plain HTML)

## WHEN ADDING FEATURES
1. Create class in appropriate src/ subdirectory
2. Register in ModularityServiceProvider
3. Write unit + feature tests
4. Update documentation

## FORBIDDEN
- ❌ Business logic in controllers
- ❌ new keyword (use DI)
- ❌ Hard-coded paths (use config)
- ❌ Options API in Vue (use Composition API)
- ❌ Plain HTML (use Vuetify components)

Always ask for clarification if the request is ambiguous.
