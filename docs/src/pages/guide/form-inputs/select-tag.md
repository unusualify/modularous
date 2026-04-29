---
sidebarTitle: Select Tag
sidebarPos: 32
---

# Select Tag

`VSelectTag` is a multi-select autocomplete for tags backed by a remote endpoint. Users can select existing tags (displayed as chips) and optionally type new values that don't yet exist in the list. There is no corresponding PHP hydrate — see [Tagger](/guide/form-inputs/input-tagger) for the hydrate-backed alternative.

## Vue Component

**Registered as:** `VSelectTag`
**File:** `vue/src/js/components/inputs/SelectTag.vue`

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `Array` | — | Array of selected tag values (v-model) |
| `endpoint` | `String` | — | API URL to fetch tag options from (paginated) |
| `selected` | `Array` | — | Initial selected items (synced to `modelValue`) |

Plus all standard `makeInputProps()` props (`label`, `name`, `rules`, `disabled`, etc.).

### Emits

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `String` | Comma-separated selected values |
| `change` | `String` | Same as `update:modelValue` |

### Usage

```vue
<VSelectTag
  v-model="form.tag_ids"
  endpoint="/api/tags"
  label="Tags"
/>
```

### Behaviour

- Tags are loaded from `endpoint` with `?page=N` pagination. Subsequent pages are fetched automatically if the currently selected values aren't found in the initial page.
- The component renders a hidden `<input name="tags">` that is kept in sync with the selection, for standard form submission compatibility.
- **Custom values**: When the user types a value that doesn't exist in the fetched list, it is temporarily added as a selectable option. On selection it is committed; if the user clears the search without selecting, the temporary entry is removed.
- The emitted `modelValue` is a comma-separated **string** of selected values (not an array), suitable for query string or hidden field use.

## Difference from Tagger

| Feature | SelectTag | [Tagger](/guide/form-inputs/input-tagger) |
|---------|-----------|--------|
| Hydrate | None | `TaggerHydrate` |
| Tag creation | Client-side temporary | Saves to DB on type |
| Colour chips | No | Yes (server-side colours) |
| Rename inline | No | Yes |

## See Also

- [Tagger](/guide/form-inputs/input-tagger) — Hydrate-backed tag creator with DB persistence
- [Tag](/guide/form-inputs/input-tag) — Read-only tag selector from a pre-existing namespace
- [Forms overview](/guide/form-inputs/overview) — Schema-driven form architecture
