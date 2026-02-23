---
sidebarPos: 3
sidebarTitle: Attributes & Custom Props
---

# Attributes & Custom Props

All attributes from config are passed to the auth component via `v-bind`. Custom auth components can declare any props they need and receive them automatically.

## Built-in Attributes

These are merged by `AuthFormBuilder::buildAuthViewData`:

| Attribute | Source | Description |
|-----------|--------|-------------|
| `noDivider` | layoutPreset | Hide divider between form and bottom slots |
| `noSecondSection` | layoutPreset | Single-column layout (no banner/second section) |
| `logoLightSymbol` | layout | SVG symbol for light background |
| `logoSymbol` | layout | SVG symbol for dark background |
| `redirectUrl` | attributes / auto | URL for redirect button (auto-set from `auth_guest_route` if not provided) |

## Custom Attributes (Custom Auth Only)

Add any attribute in `auth_pages.attributes` or `pages.[key].attributes`. The package Auth.vue does not use these; they are for your custom component.

### Common Custom Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `bannerDescription` | string | Main banner heading text |
| `bannerSubDescription` | string | Banner subtitle or description |
| `redirectButtonText` | string | Label for the redirect/link button |

### Example: Global Attributes

```php
// modularity/auth_pages.php
return [
    'attributes' => [
        'bannerDescription' => __('authentication.banner-description'),
        'bannerSubDescription' => __('authentication.banner-sub-description'),
        'redirectButtonText' => __('authentication.redirect-button-text'),
    ],
];
```

### Example: Per-Page Overrides

```php
'pages' => [
    'login' => [
        'pageTitle' => 'authentication.login',
        'layoutPreset' => 'banner',
        'attributes' => [
            'bannerDescription' => __('authentication.login-banner'),
        ],
    ],
    'register' => [
        'attributes' => [
            'bannerDescription' => __('authentication.register-banner'),
        ],
    ],
],
```

## Merge Order

Attributes are merged in this order (later overrides earlier):

1. `auth_pages.layout`
2. `layoutPreset` (e.g. `banner` → `noSecondSection: false`)
3. `auth_pages.attributes`
4. `pages.[pageKey].attributes`
5. Controller overrides (e.g. `CompleteRegisterController`)

## Custom Auth Component Props

In your custom `Auth.vue`, declare the props you need:

```vue
<script>
export default {
  props: {
    bannerDescription: { type: String, default: '' },
    bannerSubDescription: { type: String, default: '' },
    redirectUrl: { type: String, default: null },
    redirectButtonText: { type: String, default: '' },
    noDivider: { type: [Boolean, Number], default: false },
    noSecondSection: { type: [Boolean, Number], default: false },
    logoLightSymbol: { type: String, default: 'main-logo-light' },
    logoSymbol: { type: String, default: 'main-logo-dark' },
    // Add any custom props your layout needs
  },
}
</script>
```
