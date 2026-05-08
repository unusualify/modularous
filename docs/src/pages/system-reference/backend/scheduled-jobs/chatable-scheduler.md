---
sidebarPos: 2
sidebarTitle: ChatableScheduler
---

# ChatableScheduler

`Unusualify\Modularity\Schedulers\ChatableScheduler`

Artisan command that polls every minute for unread chat messages and dispatches `ChatableUnreadNotification` to the relevant parties. It is the heartbeat of the in-app chat notification system.

## Signature

```
modularity:scheduler:chatable
```

No options or arguments.

## What It Does

On each run the command:

1. **Discovers all `Chatable` models** via `ModularityFinder::getModelsWithTrait(Chatable::class)` — this returns every Eloquent model in the application that uses the `Chatable` trait.

2. **For each model class**, queries instances that have an actionable unread message using the `hasNotifiableMessage` scope (in chunks of 100).

3. **For each matching instance**, calls `$item->handleChatableNotification()` which decides whether to fire a notification and to whom.

## The `hasNotifiableMessage` Scope

`Unusualify\Modularity\Entities\Scopes\ChatableScopes::scopeHasNotifiableMessage`

Finds model instances where the **latest chat message**:
- is **not read** (`is_read = false`)
- has **not yet been notified** (`notified_at IS NULL`)
- was created at least `$minuteOffset` minutes ago (when provided)

```sql
-- Simplified view of what the scope produces:
SELECT chatable.*
FROM chatable
WHERE EXISTS (
    SELECT 1 FROM chats
    WHERE chats.chatable_id = chatable.id
    AND EXISTS (
        SELECT 1 FROM chat_messages
        WHERE chat_messages.chat_id = chats.id
          AND chat_messages.is_read = 0
          AND chat_messages.notified_at IS NULL
          AND chat_messages.created_at = (
              SELECT MAX(m2.created_at) FROM chat_messages m2
              WHERE m2.chat_id = chat_messages.chat_id
                AND m2.deleted_at IS NULL
          )
    )
)
```

## Notification Logic (`handleChatableNotification`)

`Chatable::handleChatableNotification()` decides **who** gets notified:

```
latestChatMessage exists?
    AND is_read = false?
    AND created_at older than $chatableNotificationInterval minutes?
    AND notified_at IS NULL?
        │
        ├── dispatch UnreadChatMessage event
        │
        ├── resolve chatableCreator (via HasCreator)
        ├── resolve chatableAuthorizedUser (via HasAuthorizable, if is_authorized)
        │
        └── messageCreator exists?
                │
                ├── chatableCreator != messageCreator → notify chatableCreator
                └── chatableAuthorizedUser != messageCreator → notify chatableAuthorizedUser
```

The notification sent is `ChatableUnreadNotification`. See [System Notifications](/system-reference/backend/notifications/system-notifications#chat).

## Notification Interval

The guard `created_at older than $chatableNotificationInterval minutes` prevents spamming notifications for very recent messages. The interval defaults to **60 minutes** and can be overridden per model:

```php
class Order extends Model
{
    use Chatable;

    // Notify only if the message is older than 30 minutes
    protected static int $chatableNotificationInterval = 30;
}
```

## Schedule

Registered as `->everyMinute()` in `BaseServiceProvider`:

```php
$schedule->command('modularity:scheduler:chatable')->everyMinute();
```

Running every minute ensures the notification is sent as close to the interval boundary as possible without requiring a separate queue worker for polling.

## Manual Usage

```bash
php artisan modularity:scheduler:chatable
```

Useful for testing the notification pipeline or recovering from a scheduler outage.

## Error Handling

The entire `handle()` body is wrapped in a `try/catch`:

```php
try {
    // discovery + chunked processing
} catch (\Throwable $th) {
    Log::channel('scheduler')->error('Modularity: Chatable scheduler error', [
        'error' => $th->getMessage(),
        'trace' => $th->getTraceAsString(),
    ]);
}
```

A failure in one model's processing does **not** surface to the scheduler — the error is logged and the run completes silently. Check `storage/logs/scheduler.log` if notifications stop firing.

## Related

- `Chatable` entity trait — adds chat relationships and `handleChatableNotification` to a model.
- [ChatableUnreadNotification](/system-reference/backend/notifications/system-notifications#chat) — the notification class dispatched per unread chat.
- `ChatableScopes` — the query scopes used to find notifiable models.
