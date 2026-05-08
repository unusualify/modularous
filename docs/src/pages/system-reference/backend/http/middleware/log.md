---
sidebarPos: 10
sidebarTitle: LogMiddleware
---

# LogMiddleware

**File**: `src/Http/Middleware/LogMiddleware.php`  
**Alias**: `modularity.log`  
**Part of**: default stack (applied to ALL Modularous routes)

Assigns a unique UUID to every request, attaches it to the `ModularityLog` context, and adds it to the HTTP response headers.

## What It Does

```
Request in  →  generate UUID  →  attach to log context  →  [next]  →  set Response header  →  Response out
```

1. Generates a UUID v4 string: `$requestId = Str::uuid()`.
2. Calls `ModularityLog::withContext(['request_id' => $requestId])` — all log entries emitted during this request will include the `request_id` field.
3. After the request is handled, sets `Request-Id: {uuid}` on the response headers.

## Response Header

```
Request-Id: 550e8400-e29b-41d4-a716-446655440000
```

## Usage

The `Request-Id` header allows:

- **Log correlation** — filter all log lines for a single request by searching for the UUID.
- **Distributed tracing** — pass the header downstream to external services.
- **Debugging** — copy the header value from browser DevTools and grep the log file.

## Notes

- `LogMiddleware` is the first middleware in the default stack (`defaultMiddlewares`), so the UUID is available from the very first log call in any subsequent middleware.
- The context is attached via `ModularityLog::withContext()` which typically wraps a Laravel `Log::withContext()` call, making the UUID available in all Laravel log channels (file, Slack, etc.).
