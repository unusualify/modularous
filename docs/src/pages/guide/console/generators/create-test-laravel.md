---
sidebarPos: 9
sidebarTitle: Create Test Laravel
---

# Create Test Laravel

> Generate a PHPUnit feature or unit test stub inside a module.

## Command Information

- **Signature:** `modularous:make:laravel:test {module} {test} [--unit]`
- **Aliases:** `modularous:create:laravel:test`
- **Category:** Generators

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `module` | Yes | The module name the test belongs to (e.g. `Blog`) |
| `test` | Yes | The test class name (e.g. `PostCreationTest`) |

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--unit` | `false` | Generate a unit test instead of a feature test |

## Examples

```bash
# Generate a feature test
php artisan modularous:make:laravel:test Blog PostCreationTest

# Generate a unit test
php artisan modularous:make:laravel:test Blog PostSlugTest --unit
```

## Output

Generates the test file inside the target module:

```
Modules/Blog/Tests/Feature/PostCreationTest.php
# or with --unit:
Modules/Blog/Tests/Unit/PostSlugTest.php
```

The stub extends `Tests\TestCase` and includes a single placeholder `test_example()` method.

## Related

- [create:vue-test](./create-vue-test) — generate a Vitest (Vue) test stub
- [coverage:analyze](/guide/console/coverage/coverage-analyze) — find which module files need tests
