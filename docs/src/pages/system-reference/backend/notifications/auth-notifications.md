---
sidebarPos: 1
sidebarTitle: Auth Notifications
---

# Auth Notifications

`Unusualify\Modularity\Notifications\`

Three transactional email notifications that cover the user registration and password management flows. Each class extends Laravel's `Notification` directly and delivers via the `mail` channel only.

---

## EmailVerification

`Unusualify\Modularity\Notifications\EmailVerification`

Sent when a new user needs to verify their email address before completing registration.

### Constructor

```php
public function __construct(string $token, array $parameters = [])
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$token` | `string` | Verification token embedded in the action URL |
| `$parameters` | `array` | Extra route parameters merged into the verification URL |

### Mail content

| Field | Value |
|-------|-------|
| Subject | `"Email Verification"` |
| Action label | `"Verify Email Address"` |
| Action URL | `route('complete.register.form', [...$parameters, 'token' => $token, 'email' => $notifiable->email])` |
| Expiry line | Uses `config('auth.passwords.users.expire', 60)` minutes |

### Usage

```php
$user->notify(new EmailVerification($token, ['invite' => $inviteId]));
```

### Notes

- The verification URL is built via `Route::hasAdmin('complete.register.form')`, so the route resolves through the admin route group.
- `$parameters` lets callers embed extra context (e.g. an invitation ID) into the URL for retrieval after verification.

---

## GeneratePasswordNotification

`Unusualify\Modularity\Notifications\GeneratePasswordNotification`

Sent to new users who were created without a password (e.g. admin-invited accounts). The email contains a link where the user sets their password for the first time.

### Constructor

```php
public function __construct(string $token)
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$token` | `string` | Password-generation token |

### Mail content

| Field | Value |
|-------|-------|
| Subject | `"Generate Your Password For New Account"` |
| Action label | `"Generate Password"` |
| Action URL | `route('admin.register.password.generate.form', ['token' => $token, 'email' => $notifiable->getEmailForPasswordGeneration()])` |

### Static hooks

Two static callbacks let you replace the URL or the full mail message without subclassing:

```php
// Override the action URL
GeneratePasswordNotification::createUrlUsing(function ($notifiable, $token) {
    return route('my.custom.generate', ['token' => $token]);
});

// Override the entire MailMessage
GeneratePasswordNotification::toMailUsing(function ($notifiable, $token) {
    return (new MailMessage)->subject('Set Up Your Account')->action('Get Started', '...');
});
```

| Method | Callback signature | Effect |
|--------|--------------------|--------|
| `createUrlUsing(callable $callback)` | `fn($notifiable, $token): string` | Replaces `generatePasswordUrl()` |
| `toMailUsing(callable $callback)` | `fn($notifiable, $token): MailMessage` | Replaces the entire `toMail()` output |

### Notes

- The notifiable must implement `getEmailForPasswordGeneration()` — typically the user's email attribute.
- The class is **not** queued. Wrap in a queued job if needed.

---

## ResetPasswordNotification

`Unusualify\Modularity\Notifications\ResetPasswordNotification`

Overrides Laravel's built-in `Illuminate\Auth\Notifications\ResetPassword` with an app-branded mail template. The password-reset URL and token logic are inherited from the parent class.

### Mail content

| Field | Value |
|-------|-------|
| Subject | `"Reset your {app.name} password"` |
| Greeting | `"Hi {user.name},"` |
| Body | Explains the reset request and links to the reset form |
| Action label | `"Reset Password"` |
| Expiry line | Uses `config('auth.passwords.{defaults.passwords}.expire')` |
| Salutation | `"Regards, {app.name} Support"` |

### Static hook

The parent class exposes a `toMailUsing` callback; this class honours it:

```php
ResetPasswordNotification::toMailUsing(function ($notifiable, $token) {
    return (new MailMessage)
        ->subject('Custom Reset Subject')
        ->action('Reset Now', url('/reset/' . $token));
});
```

### Notes

- No constructor override — the token is passed by the parent's `ResetPassword` contract.
- All text is wrapped in `Lang::get()` so translation files in `resources/lang/` can override the copy.
