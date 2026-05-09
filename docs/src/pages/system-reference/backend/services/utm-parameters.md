---
sidebarPos: 21
sidebarTitle: UtmParameters
---

# UtmParameters

**File**: `src/Services/UtmParameters.php`  
**Facade**: `Unusualify\Modularous\Facades\Utm`

Captures and persists the five standard UTM marketing tracking parameters from incoming HTTP requests to the PHP session. `UtmMiddleware` triggers capture automatically on every request.

## Tracked Parameters

| Parameter | Description |
|-----------|-------------|
| `utm_source` | Traffic source (e.g. `google`, `newsletter`) |
| `utm_medium` | Marketing medium (e.g. `cpc`, `email`) |
| `utm_campaign` | Campaign name |
| `utm_term` | Paid keyword |
| `utm_content` | Differentiates ads/links in the same campaign |

## Environment Variables

| Variable | Default | Effect |
|----------|---------|--------|
| `MODULAROUS_UTM_DISABLED` | `false` | Disable UTM capture entirely — all methods become no-ops |
| `MODULAROUS_UTM_TEMPORARY` | `false` | Do not persist parameters between requests — session is reset on every boot |
| `MODULAROUS_UTM_HANDLE_REQUEST` | `false` | Parse UTM params from the current HTTP request automatically on service construction |

## Key Methods

### Capture

| Method | Description |
|--------|-------------|
| `handleRequest()` | Parse UTM params from the current request and store them in the session. If `isPersisted()` is true, merges with existing values; otherwise overwrites. |

### Read

| Method | Description |
|--------|-------------|
| `getParameters()` | Return all five parameters as an associative array |
| `$utm->utm_source` | Direct property access for any individual parameter |
| `$utm->getUtmSourceParameter()` | Magic getter — `get{StudlyParam}Parameter()` works for any of the five params |

### Write

| Method | Description |
|--------|-------------|
| `setParameters(array $data)` | Overwrite all stored UTM parameters |
| `mergeParameters(array $data)` | Update only the provided parameters, leaving others intact |
| `resetParameters()` | Clear all UTM parameters from the session |

## Middleware Integration

`UtmMiddleware` is registered automatically by Modularous and calls `handleRequest()` on every request when `MODULAROUS_UTM_HANDLE_REQUEST=true`.

To enable automatic capture, add to your `.env`:

```dotenv
MODULAROUS_UTM_HANDLE_REQUEST=true
```

## Usage Example

```php
use Unusualify\Modularous\Facades\Utm;

// Read all parameters
$params = Utm::getParameters();
// ['utm_source' => 'google', 'utm_medium' => 'cpc', ...]

// Access individual parameter
$source = Utm::getUtmSourceParameter();

// Attach UTM data to a model on checkout
$order->utm_source   = Utm::utm_source;
$order->utm_campaign = Utm::utm_campaign;
$order->save();

// Clear after use
Utm::resetParameters();
```

## Session Storage

Parameters are stored in the session under `utm_parameters.*`:

```
utm_parameters.utm_source   = 'google'
utm_parameters.utm_medium   = 'cpc'
utm_parameters.utm_campaign = 'summer-sale'
utm_parameters.utm_term     = null
utm_parameters.utm_content  = null
```
