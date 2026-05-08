---
sidebarPos: 3
sidebarTitle: Coverage Test Generator
---

# Coverage Generate Tests

> Scaffold missing PHPUnit/Pest test stubs for uncovered files — optionally using an AI provider to write real test bodies.

## Command Information

- **Signature:** `coverage:generate-tests [--ai] [--model=] [--api-key=] [--template=phpunit] [--interactive] [--delay=0] [--dry-run] [--cloverName=] [--cloverDir=] [--files=*] [--threshold=0]`
- **Category:** Coverage

## Options

| Option | Default | Description |
|--------|---------|-------------|
| `--ai` | `false` | Use an AI provider to write test bodies |
| `--model` | — | AI model identifier (see table below) |
| `--api-key` | — | API key for the AI provider |
| `--template` | `phpunit` | Fallback template: `phpunit` or `pest` |
| `--interactive` | `false` | Prompt before writing each file |
| `--delay` | `0` | Seconds to wait between AI requests (rate-limit safety) |
| `--dry-run` | `false` | Show what would be generated without writing files |
| `--cloverName` | `clover.xml` | Clover XML file name |
| `--cloverDir` | `storage/app` | Directory containing the Clover file |
| `--files` | _(all)_ | Filter to specific file paths (repeatable) |
| `--threshold` | `0` | Only generate tests for files below this coverage % |

## Supported AI Providers

| Provider | Example `--model` value |
|----------|------------------------|
| Anthropic (Claude) | `claude-3-5-sonnet-20241022` |
| Google Gemini | `gemini-1.5-pro` |
| OpenAI | `gpt-4o` |
| Ollama (local) | `llama3.2` |

The provider is inferred from the model name. For Ollama no API key is required.

## Examples

### Scaffold stubs using the default PHPUnit template

```bash
php artisan coverage:generate-tests
```

### Use Claude to write real tests for uncovered files

```bash
php artisan coverage:generate-tests --ai --model=claude-3-5-sonnet-20241022 --api-key=sk-ant-...
```

### Preview output without writing files

```bash
php artisan coverage:generate-tests --ai --model=gpt-4o --api-key=sk-... --dry-run
```

### Generate Pest stubs with interactive confirmation

```bash
php artisan coverage:generate-tests --template=pest --interactive
```

### Only generate tests for files below 50 % coverage

```bash
php artisan coverage:generate-tests --threshold=50 --ai --model=gemini-1.5-pro --api-key=...
```

## Related

- [coverage:analyze](/guide/console/coverage/coverage-analyze) — identify which files need tests
- [coverage:report](/guide/console/coverage/coverage-report) — visualise results after running tests
