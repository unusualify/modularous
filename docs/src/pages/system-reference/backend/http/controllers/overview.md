---
sidebarPos: 1
sidebarTitle: Controllers Overview
---

# Controllers

**Namespace**: `Unusualify\Modularity\Http\Controllers`  
**Location**: `src/Http/Controllers/`

All HTTP controllers in the Modularous package. They form a four-level hierarchy rooted at `Controller`, with specialized controllers for admin panel, REST APIs, authentication, and utility endpoints.

## Inheritance Hierarchy

```
Illuminate\Routing\Controller
└── Controller
    ├── CoreController
    │   ├── PanelController
    │   │   └── BaseController          ← standard CRUD admin controllers
    │   ├── ApiController               ← REST API base
    │   └── FrontController             ← public-facing frontend base
    └── (Auth\Controller → Auth\LoginController, RegisterController, …)
```

## Base Classes

| Class | File | Purpose |
|-------|------|---------|
| [Controller](./controller) | `Controller.php` | Root controller — middleware utilities, exception handler binding |
| [CoreController](./core-controller) | `CoreController.php` | Module discovery, repository init, route config |
| [PanelController](./panel-controller) | `PanelController.php` | Authorization, scoping, pagination for admin panel |
| [BaseController](./base-controller) | `BaseController.php` | Full CRUD (index/create/store/edit/update/destroy) + Inertia support |
| [ApiController](./api-controller) | `ApiController.php` | REST API base — versioning, rate-limiting, includes, bulk ops |
| [FrontController](./front-controller) | `FrontController.php` | Abstract base for public-facing routes |

## Specialized Controllers

| Class | File | Purpose |
|-------|------|---------|
| [ChatController](./chat-controller) | `ChatController.php` | Chat messages, attachments, pinned messages |
| [CurrencyExchangeController](./currency-exchange-controller) | `CurrencyExchangeController.php` | Exchange rates and currency conversion |
| [DashboardController](./dashboard-controller) | `DashboardController.php` | Admin dashboard with configurable block items |
| [FileLibraryController](./file-library-controller) | `FileLibraryController.php` | File uploads, local and cloud (S3/Azure) |
| [FilepondController](./filepond-controller) | `FilepondController.php` | Temporary Filepond upload/revert/preview |
| [GlideController](./glide-controller) | `GlideController.php` | On-the-fly image transformation via Glide |
| [ImpersonateController](./impersonate-controller) | `ImpersonateController.php` | Admin user impersonation |
| [MediaLibraryController](./media-library-controller) | `MediaLibraryController.php` | Image uploads with dimensions, alt text, captions |
| [MetricController](./metric-controller) | `MetricController.php` | Metric items with connectors and date-range filtering |
| [PasswordController](./password-controller) | `PasswordController.php` | Password reset and generation |
| [ProcessController](./process-controller) | `ProcessController.php` | Process workflow status and field updates |
| [ProfileController](./profile-controller) | `ProfileController.php` | User profile — info, security, company |
| [TagController](./tag-controller) | `TagController.php` | Tag search and creation |
| [UIPreferencesController](./ui-preferences-controller) | `UIPreferencesController.php` | Persist sidebar/topbar/navigation preferences |
| [VerificationController](./verification-controller) | `VerificationController.php` | Email address verification |

## Authentication Controllers

All under `src/Http/Controllers/Auth/` (namespace `…\Auth`).

| Class | File | Purpose |
|-------|------|---------|
| [Auth\Controller](./auth/controller) | `Auth/Controller.php` | Base for auth workflows, guest middleware |
| [LoginController](./auth/login-controller) | `Auth/LoginController.php` | Login form + 2FA |
| [RegisterController](./auth/register-controller) | `Auth/RegisterController.php` | User registration with optional company |
| [ForgotPasswordController](./auth/forgot-password-controller) | `Auth/ForgotPasswordController.php` | Send password-reset email |
| [PreRegisterController](./auth/pre-register-controller) | `Auth/PreRegisterController.php` | Email verification before registration |
| [CompleteRegisterController](./auth/complete-register-controller) | `Auth/CompleteRegisterController.php` | Finish registration after token validation |
| [ResetPasswordController](./auth/reset-password-controller) | `Auth/ResetPasswordController.php` | Password reset after token validation |

## API Controllers

Under `src/Http/Controllers/API/`.

| Class | File | Purpose |
|-------|------|---------|
| [API\LanguageController](./api-language-controller) | `API/LanguageController.php` | Serve translation strings via JSON API |
