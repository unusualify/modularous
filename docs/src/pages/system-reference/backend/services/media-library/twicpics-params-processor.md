---
sidebarPos: 9
sidebarTitle: TwicPicsParamsProcessor
---

# TwicPicsParamsProcessor

**File**: `src/Services/MediaLibrary/TwicPicsParamsProcessor.php`  
**Extends**: `AbstractParamsProcessor`

`TwicPicsParamsProcessor` translates standard Modularous image params (`w`, `h`, `fm`, `q`, `fit`) into TwicPics transformation syntax. It is instantiated internally by the [TwicPics](/system-reference/backend/services/media-library/twicpics) driver.

## Parameter Translation

| Input param | TwicPics output param | Notes |
|-------------|----------------------|-------|
| `fm` | `output` | Format conversion |
| `q` | `quality` | Quality (1–100) |
| `w` + `h` (no crop) | `resize={w}x{h}` | Responsive resize; `-` used when only one dimension is set |
| `w` + `h` + `fit=crop` | `crop={w}x{h}` | Hard crop to exact dimensions |
| `fit=crop` alone | sets `$cropFit = true` | Signals that a `crop` directive should be used instead of `resize` |

## `finalizeParams()` Logic

1. If `$format` is set → `$params['output'] = $format`
2. If `$quality` is set → `$params['quality'] = $quality`
3. If `$width` or `$height` is set:
   - Missing dimension is replaced with `-` (TwicPics wildcard)
   - If `$cropFit` is `true` → `$params['crop'] = "{w}x{h}"`
   - Otherwise → `$params['resize'] = "{w}x{h}"`
4. Returns merged `$params`

## `handleParamFit` Override

```php
protected function handleParamFit($key, $value)
{
    if ($value !== 'crop') return;      // ignore non-crop fit values
    if (isset($this->params['crop'])) return;  // don't override explicit crop

    $this->cropFit = true;
    unset($this->params[$key]);         // consume the 'fit' key
}
```

## Example Transformations

| Input | Output |
|-------|--------|
| `['w' => 300, 'h' => 200]` | `resize=300x200` |
| `['w' => 300, 'h' => 200, 'fit' => 'crop']` | `crop=300x200` |
| `['w' => 300]` | `resize=300x-` |
| `['fm' => 'webp', 'q' => 80]` | `output=webp&quality=80` |

The resulting params array is appended to the TwicPics URL as `?twic=v1/{key}={value}/{key}={value}`.
