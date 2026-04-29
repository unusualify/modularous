---
sidebarPos: 8
sidebarTitle: Process Traits
sidebarGroupTitle: Process Traits
---

# Process Traits

Two traits handle approval and confirmation workflows. `Processable` is for models that go through a **single** approval process (confirm/reject). `HasProcesses` is for parent models that aggregate **multiple** ongoing process records from related child models.

| Trait | Description |
|-------|-------------|
| [Processable](./processable) | Single-process workflow: preparing → waiting → confirmed / rejected |
| [HasProcesses](./has-processes) | Aggregates multiple `Process` records from configured child relationships |
