---
sidebarPos: 8
sidebarTitle: AbstractParamsProcessor
---

# AbstractParamsProcessor

**File**: `src/Services/MediaLibrary/AbstractParamsProcessor.php`

`AbstractParamsProcessor` is the base class for image transformation parameter translators. It provides a dispatch loop that routes each incoming parameter to a named handler method, then calls `finalizeParams()` to produce the driver-specific output.

This class was introduced alongside the TwicPics driver to provide a compatibility layer for the minimum set of standard image parameters (`w`, `h`, `fm`, `q`, `fit`).

## Compatible Parameters

```php
const COMPATIBLE_PARAMS = [
    'w'   => 'width',
    'h'   => 'height',
    'fm'  => 'format',
    'q'   => 'quality',
    'fit' => 'fit',
];
```

Parameters in this map are extracted into typed properties. All other parameters remain in `$this->params` as-is.

## Properties

| Property | Type | Set from param |
|----------|------|---------------|
| `$width` | mixed | `w` |
| `$height` | mixed | `h` |
| `$format` | mixed | `fm` |
| `$quality` | mixed | `q` |
| `$fit` | mixed | `fit` |
| `$params` | array | Remaining / pass-through params |

## Methods

| Method | Description |
|--------|-------------|
| `process(array $params): array` | Entry point — dispatches each param to a handler, then calls `finalizeParams()` |
| `handleParam(string $key, mixed $value): void` | Default handler — maps `COMPATIBLE_PARAMS` keys to properties; leaves unknown keys in `$params` |
| `finalizeParams(): array` | **Abstract** — must be implemented by concrete processors; returns the final params array |

## Custom Handler Convention

Override parameter handling by defining a method named `handleParam{KEY}`:

```php
// Override handling for the 'fit' parameter
protected function handleParamFit(string $key, mixed $value): void
{
    if ($value === 'crop') {
        $this->cropFit = true;
        unset($this->params[$key]);
    }
}
```

If `handleParam{KEY}` exists, the generic `handleParam()` is skipped for that key.

## Implementing a Custom Processor

```php
class CloudflareParamsProcessor extends AbstractParamsProcessor
{
    public function finalizeParams(): array
    {
        $output = [];

        if ($this->width)   $output['width']   = $this->width;
        if ($this->height)  $output['height']  = $this->height;
        if ($this->quality) $output['quality'] = $this->quality;

        return array_merge($output, $this->params);
    }
}
```

## Known Implementations

| Class | Used by |
|-------|---------|
| [TwicPicsParamsProcessor](/system-reference/backend/services/media-library/twicpics-params-processor) | `TwicPics` driver |
