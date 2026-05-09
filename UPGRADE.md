# Upgrade Guide

## Upgrading To 11 (or your specific version) from 0.x or 10.x

To upgrade Modularous to the latest version, please follow these steps:

### 1. Update Dependencies
Update your `composer.json` to require the new version and run:
```bash
composer update unusualify/modularous:^11
```

### 2. Run the Upgrade Script
We have provided an automated script to handle breaking changes and architectural updates. Run the following command in your terminal:

```bash
php vendor/unusualify/modularous/upgrades/v11.php
```

### 3. Clear Cache
After running the script, it is highly recommended to clear your application cache:

```bash
php artisan optimize:clear
```
