---
sidebarPos: 7
sidebarTitle: Input OTP
---

# InputOtp <Badge type="warning" text="stub" />

`InputOtp` is a placeholder for a one-time password input field. The component renders a stub `<div>OTP</div>` and has no functional implementation yet.

> [!WARNING]
> This component is not usable in production. It is listed here as a tracking entry for a planned input type.

## Schema usage (planned)

```php
[
  'type'   => 'otp',
  'name'   => 'verification_code',
  'label'  => 'Verification Code',
  'length' => 6,
]
```

## Status

Implementation is pending. When ready, `InputOtp` will integrate with `useInput` and emit standard form events compatible with `ue-form`.
