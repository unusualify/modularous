---
sidebarPos: 6
sidebarTitle: Layout Presets
---

# Layout Presets

Layout presets define structural flags (e.g. single vs split column). They do not contain content; content comes from `attributes`.

## Available Presets

| Preset | noSecondSection | noDivider | Use Case |
|--------|----------------|-----------|----------|
| `banner` | false | false | Split layout with banner/description section |
| `minimal` | true | false | Single card, no banner |
| `minimal_no_divider` | false | true | Split layout, no divider (e.g. OAuth password) |

## Preset Definitions

```php
// config/merges/auth_pages.php
'layoutPresets' => [
    'banner' => [
        'noSecondSection' => false,
    ],
    'minimal' => [
        'noSecondSection' => true,
    ],
    'minimal_no_divider' => [
        'noSecondSection' => false,
        'noDivider' => true,
    ],
],
```

## How Presets Work

1. Each page references a preset via `layoutPreset`:

```php
'pages' => [
    'login' => [
        'layoutPreset' => 'banner',
        // ...
    ],
    'forgot_password' => [
        'layoutPreset' => 'minimal',
        // ...
    ],
],
```

2. `buildAuthViewData` merges the preset into `attributes`:

```php
$attributes = array_merge(
    $layoutConfig,
    $layoutPreset,  // e.g. noSecondSection: false
    modularityConfig('auth_pages.attributes', []),
    $pageConfig['attributes'] ?? [],
    $overrides['attributes'] ?? []
);
```

3. The auth component receives `noSecondSection`, `noDivider` as props.

## Custom Presets

Add your own in `modularity/auth_pages.php`:

```php
'layoutPresets' => [
    'my_custom' => [
        'noSecondSection' => false,
        'noDivider' => true,
    ],
],
```

Then use `'layoutPreset' => 'my_custom'` in page definitions.
