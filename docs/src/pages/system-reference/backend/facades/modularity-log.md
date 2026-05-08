---
sidebarPos: 11
sidebarTitle: ModularityLog
---

# ModularityLog

**Facade**: `Unusualify\Modularity\Facades\ModularityLog`  
**Accessor**: `modularity.log`  
**Underlying**: `Illuminate\Log\LogManager` (dedicated `modularity` channel)

A dedicated logging facade that writes to the Modularous log channel instead of the default application log. Exposes the same interface as Laravel's built-in `Log` facade.

## Methods

All standard PSR-3 log levels are available:

| Method | Description |
|--------|-------------|
| `emergency(string $message, array $context = [])` | System is unusable |
| `alert(string $message, array $context = [])` | Action must be taken immediately |
| `critical(string $message, array $context = [])` | Critical conditions |
| `error(string $message, array $context = [])` | Runtime errors |
| `warning(string $message, array $context = [])` | Exceptional occurrences that are not errors |
| `notice(string $message, array $context = [])` | Normal but significant events |
| `info(string $message, array $context = [])` | Informational messages |
| `debug(string $message, array $context = [])` | Detailed debug information |
| `log(string $level, string $message, array $context = [])` | Log with arbitrary level |

## Usage

```php
use Unusualify\Modularity\Facades\ModularityLog;

ModularityLog::info('Module booted', ['module' => 'Blog']);

ModularityLog::error('Cache write failed', [
    'key' => $cacheKey,
    'exception' => $e->getMessage(),
]);
```

## Notes

- Writes to the `modularity` log channel defined in `config/logging.php`.
- Use this instead of `Log::` for internal Modularous events to keep application logs clean.
- The `modularity.log` middleware also uses this channel to log each incoming request.
