<?php

namespace Modules\Cms\Services;

use Modules\Cms\Contracts\RedirectValidationServiceInterface;
use Modules\Cms\Support\CmsPathLocale;

class RedirectValidationService implements RedirectValidationServiceInterface
{
    public function __construct(
        protected CanonicalUrlResolver $canonicalUrlResolver,
        protected CmsUrlRouteRegistry $urlRouteRegistry,
    ) {}

    public function validate(string $fromPath, string $toPath, array $options = []): array
    {
        $errors = [];
        $warnings = [];

        $from = $this->canonicalUrlResolver->normalizePath($fromPath);
        $to = $this->canonicalUrlResolver->normalizePath($toPath);

        if (! empty($from) && ! empty($to) && $from === $to) {
            $errors[] = 'Redirect cannot point to itself.';
        }

        $activePaths = $this->resolveActivePublicPaths($options);

        if (in_array($from, $activePaths, true)) {
            $errors[] = 'Active page route takes precedence. Redirect source conflicts with an active page path.';
        }

        $existingRedirects = (array) ($options['existing_redirects'] ?? []);

        $graph = [];
        foreach ($existingRedirects as $source => $target) {
            $graph[$this->canonicalUrlResolver->normalizePath((string) $source)] = $this->canonicalUrlResolver->normalizePath((string) $target);
        }
        $graph[$from] = $to;

        if ($this->followPathDetectsCycle($graph, $from)) {
            $errors[] = 'Redirect loop detected.';
        }

        if ($this->isCrossLocaleRedirect($from, $to)) {
            $warnings[] = 'Cross-locale redirect detected. Verify locale strategy before publishing.';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
            'normalized' => [
                'from' => $from,
                'to' => $to,
            ],
            'suggestion' => count($errors) === 0 ? null : $from . '-1',
        ];
    }

    /**
     * Prefer {@see CmsUrlRouteRegistry::activePublicPagePathsForLocale()} when `locale` is set; otherwise use
     * precomputed `active_paths` (e.g. tests or callers without locale context).
     *
     * @param array{locale?: string, active_paths?: list<string>|array<int, string>} $options
     * @return list<string>
     */
    protected function resolveActivePublicPaths(array $options): array
    {
        if (isset($options['locale'])) {
            return $this->urlRouteRegistry->activePublicPagePathsForLocale((string) $options['locale']);
        }

        return array_map(
            fn ($path) => $this->canonicalUrlResolver->normalizePath((string) $path),
            (array) ($options['active_paths'] ?? [])
        );
    }

    /**
     * Follow single-target edges from {@see $start} (the new/updated redirect source).
     */
    protected function followPathDetectsCycle(array $graph, string $start): bool
    {
        $visited = [];
        $current = $start;

        while (isset($graph[$current])) {
            if (isset($visited[$current])) {
                return true;
            }

            $visited[$current] = true;
            $current = $graph[$current];
        }

        return false;
    }

    protected function isCrossLocaleRedirect(string $from, string $to): bool
    {
        $fromLocale = $this->firstSegment($from);
        $toLocale = $this->firstSegment($to);

        $locales = CmsPathLocale::pathSegmentLocales();
        $fromIsLocale = in_array($fromLocale, $locales, true);
        $toIsLocale = in_array($toLocale, $locales, true);

        return $fromIsLocale && $toIsLocale && $fromLocale !== $toLocale;
    }

    protected function firstSegment(string $path): string
    {
        return explode('/', ltrim($path, '/'))[0] ?? '';
    }
}
