---
sidebarPos: 3
sidebarTitle: Input Range
---

# InputRange <Badge type="warning" text="experimental" />

`InputRange` wraps Vuetify's `v-range-slider` inside the `ue-form` schema system. It produces a two-handle slider for selecting a numeric range.

## Schema usage

```php
[
  'type'  => 'range',
  'name'  => 'price_range',
  'label' => 'Price Range',
  'min'   => 0,
  'max'   => 10000,
  'step'  => 100,
]
```

## Value format

The model value is an array with exactly two numbers: `[min, max]`.

```js
// example stored value
[200, 4500]
```

## Schema keys

All keys on the schema object (besides `type` and `name`) are forwarded to `v-range-slider`. Refer to the [Vuetify v-range-slider documentation](https://vuetifyjs.com/en/components/range-sliders/) for the full list of supported props such as `min`, `max`, `step`, `thumb-label`, `color`, and `track-color`.

## Notes

- Initialise the model value as a two-element array; an uninitialised value (e.g. `null` or `[]`) will cause the slider to render with both handles at position 0.
- The `label` key is explicitly extracted and passed as the `:label` prop on the slider — it does not need to be nested inside any sub-object.
