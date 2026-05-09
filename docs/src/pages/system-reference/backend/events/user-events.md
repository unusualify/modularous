---
sidebarPos: 4
sidebarTitle: User Events
---

# User Events

Modularous fires four events during the user registration and verification flow. All four use `SerializesModels` so they are safe to queue. None extend `ModelEvent`; they are standalone event classes.

## Registration Flow

```
HTTP POST /register
        │
        ▼
  ModularousUserRegistering   ← fired before user is created
        │
        ▼
  [user record created]
        │
        ▼
  ModularousUserRegistered    ← fired after user is created
        │
        ├─ standard flow ──► (done)
        │
        └─ email-verified ──► VerifiedEmailRegister
```

Separately, when a user requests email verification:

```
POST /email/verify
        │
        ▼
  ModularousUserVerification  ← fired on verification request
```

---

## ModularousUserRegistering

`Unusualify\Modularous\Events\ModularousUserRegistering`

Fired just before a new user is persisted. Use this event to validate or enrich the registration request before the record is written.

### Constructor

```php
public function __construct(public $request, bool $isOauth = false)
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$request` | `Illuminate\Http\Request` | The incoming registration request |

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `isOauth()` | `bool` | `true` when registration comes from an OAuth provider |

### Example Listener

```php
public function handle(ModularousUserRegistering $event): void
{
    if ($event->isOauth()) {
        // OAuth pre-registration logic
    }

    // Access request data
    $email = $event->request->input('email');
}
```

---

## ModularousUserRegistered

`Unusualify\Modularous\Events\ModularousUserRegistered`

Fired immediately after the user record is created. Use this event to send welcome emails, assign default roles, create related records, etc.

### Constructor

```php
public function __construct($user, Request $request, bool $isOauth = false)
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$user` | `Illuminate\Contracts\Auth\Authenticatable` | The newly created user |
| `$request` | `Illuminate\Http\Request` | The registration request |

### Methods

| Method | Returns | Description |
|--------|---------|-------------|
| `isOauth()` | `bool` | `true` when the user registered via OAuth |

### Example Listener

```php
public function handle(ModularousUserRegistered $event): void
{
    $user = $event->user;

    if ($event->isOauth()) {
        // Skip verification email for OAuth users
        return;
    }

    // Send welcome notification
    $user->notify(new WelcomeNotification());
}
```

---

## ModularousUserVerification

`Unusualify\Modularous\Events\ModularousUserVerification`

Fired when a user initiates email verification. Use this event to log verification attempts or trigger secondary verification flows.

### Constructor

```php
public function __construct(public $request)
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$request` | `Illuminate\Http\Request` | The verification request |

### Example Listener

```php
public function handle(ModularousUserVerification $event): void
{
    // Log or audit the verification attempt
    logger('Verification initiated from IP: ' . $event->request->ip());
}
```

---

## VerifiedEmailRegister

`Unusualify\Modularous\Events\VerifiedEmailRegister`

Fired after a user completes the verified-email registration path (i.e., the user confirmed ownership of their email address during sign-up).

### Constructor

```php
public function __construct($user)
```

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `$user` | `Illuminate\Contracts\Auth\Authenticatable` | The user who completed verified registration |

### Example Listener

```php
public function handle(VerifiedEmailRegister $event): void
{
    $event->user->markEmailAsVerified();
    // Finalize account setup
}
```

---

## Registering Listeners

Wire up listeners in your module's `EventServiceProvider` (or the application's `App\Providers\EventServiceProvider`):

```php
use Unusualify\Modularous\Events\ModularousUserRegistering;
use Unusualify\Modularous\Events\ModularousUserRegistered;
use Unusualify\Modularous\Events\ModularousUserVerification;
use Unusualify\Modularous\Events\VerifiedEmailRegister;

protected $listen = [
    ModularousUserRegistering::class  => [YourPreRegisterListener::class],
    ModularousUserRegistered::class   => [YourPostRegisterListener::class],
    ModularousUserVerification::class => [YourVerificationListener::class],
    VerifiedEmailRegister::class      => [YourVerifiedRegisterListener::class],
];
```
