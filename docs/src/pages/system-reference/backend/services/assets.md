---
sidebarPos: 8
sidebarTitle: Assets
---

# Assets

**File**: `src/Services/Assets.php`

Resolves frontend asset URLs for both **production** (compiled manifest lookup) and **local development** (Vite dev server). Used internally by Blade views to load the compiled Vue application scripts and stylesheets.

## How It Works

In **production**, `asset()` reads the compiled `unusual-manifest.json` from the public directory and returns the hashed/versioned asset path.

In **local development** (when `modularity.is_development = true` and the environment is `local` or `development`), it fetches the manifest from the running Vite dev server and returns the dev server URL.

## Key Methods

| Method | Description |
|--------|-------------|
| `asset($file)` | Primary entry point. Returns dev server URL in development mode, manifest URL in production. |
| `prodAsset($file)` | Look up the file in the compiled manifest. Falls back to `/{public_dir}/{file}` if not found. |
| `devAsset($file)` | Return the dev server URL for a file. Returns `null` if not in dev mode. |
| `getManifestFilename()` | Resolve the absolute path to the manifest file (checks public dir first, then vendor path). |

## Configuration Keys

| Config key | Default | Description |
|------------|---------|-------------|
| `modularity.public_dir` | `'unusual'` | Public directory name under `public/` |
| `modularity.manifest` | `'unusual-manifest.json'` | Manifest filename |
| `modularity.development_url` | `'http://localhost:8080'` | Vite dev server base URL |
| `modularity.is_development` | `false` | Enable dev mode asset resolution |
| `modularity.vendor_path` | — | Path to the package vendor dir (used as fallback for manifest) |

## Dev Mode Detection

Dev mode is active when **both** conditions are true:
1. `app()->environment('local', 'development')` is `true`
2. `modularity.is_development` config is `true`

To enable local development asset serving:

```php
// config/modularity.php
'is_development' => env('MODULARITY_DEV', false),
'development_url' => env('MODULARITY_DEV_URL', 'http://localhost:8080'),
```

```dotenv
# .env
MODULARITY_DEV=true
MODULARITY_DEV_URL=http://localhost:8080
```
