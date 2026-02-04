# Http Instructions

Concise prompts for agents modifying `src/Http/` (controllers, middleware, requests, responses).

Format:
- Action | Path | Constraints | Tests

Examples:
- Add controller | src/Http/Controllers/ReportController.php | Use DI, form requests, return Inertia or JSON | tests/Http/ReportControllerTest.php
- Add middleware | src/Http/Middleware/EnsureModuleEnabled.php | PSR-12, register alias in Module::createMiddlewareAliases() | tests/Http/Middleware/EnsureModuleEnabledTest.php
- Create request | src/Http/Requests/StoreReportRequest.php | Use validated(), typed rules | tests/Http/Requests/StoreReportRequestTest.php

Always include the target path and a test expectation. Keep prompts short.