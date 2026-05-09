---
sidebarPos: 4
sidebarTitle: component
---

# component

**File**: `src/Helpers/component.php`

Helpers that produce Vue component configuration arrays for Modularous modal and recursive-content patterns.

## Functions

### `modularous_response_modal_body_component`

```php
modularous_response_modal_body_component(string $component, array $props = []): array
```

Returns a config array describing a `ue-recursive-stuff` Vue component to be used as a modal body. Merges `$props` into the component descriptor.

---

### `modularous_modal_service`

```php
modularous_modal_service(string $component, array $props = []): array
```

Builds a `ue-recursive-stuff` modal service config — the structure that drives Modularous's dynamic modal system. Returns:

```php
[
    'type' => 'ue-recursive-stuff',
    'component' => $component,
    ...$props,
]
```

---

### `modularous_modal_service_form`

```php
modularous_modal_service_form(string $component, array $props = []): array
```

Variant of `modularous_modal_service` that wraps a `ue-form` component inside the modal body. Used when the modal content is a form.

---

### `modularous_new_modal_service`

```php
modularous_new_modal_service(string $component, array $props = []): array
```

Builds a new-style modal service config using the updated modal component structure introduced in a later Modularous version. Use this for new modals; prefer `modularous_modal_service` only for legacy modals.

---

### `modularous_new_response_modal_body_component`

```php
modularous_new_response_modal_body_component(string $component, array $props = []): array
```

New-style modal body component descriptor. Pair with `modularous_new_modal_service` when building modals with the updated component structure.
