---
sidebarPos: 9
sidebarTitle: Relationship Traits
sidebarGroupTitle: Relationship Traits
---

# Relationship Traits

These traits wire up assignment, authorization, creator tracking, and chat threads via morph relationships. They are independent of each other and can be used selectively.

| Trait | Description |
|-------|-------------|
| [Assignable](./assignable) | User/role assignment via `Assignment` morph with status tracking |
| [Chatable](./chatable) | Auto-created `Chat` thread with messages, read status, and notifications |
| [HasAuthorizable](./has-authorizable) | Per-record `Authorization` morph for fine-grained ownership control |
| [HasCreator](./has-creator) | `CreatorRecord` morph that tracks which user created the record |
