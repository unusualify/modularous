---
sidebarPos: 1
sidebarTitle: Overview
sidebarGroupTitle: Recipes
---

# Recipes & Common Patterns

End-to-end walkthroughs for the tasks developers do most often on Modularous. Each recipe is self-contained and cross-references the reference docs rather than duplicating them.

Unlike [Module Features](/guide/module-features/overview) (which describe **what** a trait does) or [Generics](/guide/generics/overview) (which describe **how** a helper works), recipes describe **how to accomplish a goal** — the sequence of commands, files, and config changes from zero to working.

## Available Recipes

| Recipe | Goal | Key concepts |
|--------|------|-------------|
| [CRUD Module](./crud-module) | Ship a complete CRUD module with list, create, edit, delete | `make:module`, Repository, Hydrate |
| [File Uploads](./file-uploads) | Let users upload and retrieve files (avatar, attachments) | `HasFileponds`, `input-filepond`, Media Library |
| [State Machine Workflow](./state-machine) | Model records that move through named states with history | `HasStateable`, `Processable`, State events |
| [Custom Form Input](./custom-input) | Add a new input type that plugs into the form schema | Hydrate, `registerInputType`, Vue input component |

## When to Read a Recipe vs Reference

- **Recipe** — you know what you want to *build* but not the exact sequence of steps.
- **Reference** — you already know the pattern and need method signatures, options, or edge cases.

## Recipe Structure

Every recipe follows the same outline so you can skim:

1. **Goal** — what you'll have at the end
2. **Prerequisites** — assumed knowledge / required setup
3. **Steps** — numbered, copy-pasteable
4. **Verification** — how to confirm it works
5. **Next steps** — where to go for customisation

## Contributing a Recipe

Recipes are most useful when they come from real tasks. If you solve a non-trivial problem with Modularous that required stitching several docs together, consider contributing it here. Keep it:

- **Goal-oriented** — titled with the outcome, not the mechanism
- **Minimal** — the shortest path that actually works
- **Cross-referenced** — link to the reference docs instead of explaining what they already cover
