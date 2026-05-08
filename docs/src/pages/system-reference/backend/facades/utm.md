---
sidebarPos: 18
sidebarTitle: Utm
---

# Utm

**Facade**: `Unusualify\Modularity\Facades\Utm`  
**Accessor**: `modularity.utm`  
**Underlying**: `Unusualify\Modularity\Services\UtmParameters`

Captures and persists UTM tracking parameters from the request. See [UtmParameters](/system-reference/backend/services/utm-parameters) for implementation details.

## Usage

```php
use Unusualify\Modularity\Facades\Utm;

// Capture UTM params from the current request and store in session
Utm::getParameters();

// Retrieve stored UTM parameters
$params = Utm::getParameters();
// → ['utm_source' => 'google', 'utm_medium' => 'cpc', ...]
```

## Notes

- Called automatically by `UtmMiddleware` on every request. Direct usage is rarely needed outside of that middleware.
- Parameters are persisted in the session for a configurable TTL and exposed to Blade layouts via a view composer.
