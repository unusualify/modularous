---
sidebarPos: 17
sidebarTitle: Profile
---

# Profile

**File**: `src/Entities/Profile.php`  
**Namespace**: `Unusualify\Modularity\Entities`  
**Extends**: `Model`

Extended profile data for a user. Provides a dedicated record for personal details that sit alongside the core `User` model.

## Fillable Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `user_id` | `int` | Owning user |
| `name` | `string` | Display name |
| `surname` | `string` | Surname |
| `phone` | `string` | Phone number |
| `country` | `string` | Country |
| `language` | `string` | Preferred language |
| `timezone` | `string` | Preferred timezone |

## Relationships

### `user(): BelongsTo`

The user this profile belongs to.

## Related

- [User](./user) — core user model
- [ProfileController](/system-reference/backend/http/controllers/profile-controller) — profile edit UI
