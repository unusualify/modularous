<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

trait TableEager
{
    /**
     * Cache defined relations by model class for fast header parsing.
     *
     * @var array<string, array<int, string>>
     */
    protected $definedRelationsCache = [];

    protected function isFormatItemEagerEnabled(): bool
    {
        $routeValue = $this->getConfigFieldsByRoute('use_format_item_eager', null);
        if ($routeValue !== null) {
            return (bool) $routeValue;
        }

        $moduleValue = data_get($this->module ? $this->module->getRawConfig() : [], 'use_format_item_eager');
        if ($moduleValue !== null) {
            return (bool) $moduleValue;
        }

        return (bool) config('modularity.use_format_item_eager', false);
    }

    protected function resolveHeaderWiths($with, Model $model): array
    {
        $with = $this->normalizeWithValue($with);
        $resolved = [];

        if (Arr::isAssoc($with)) {
            $plainNumeric = [];

            foreach ($with as $relationshipName => $mappings) {
                if (is_int($relationshipName)) {
                    if (is_string($mappings) && $this->isValidRelationPath($mappings, $model)) {
                        $plainNumeric[] = $mappings;
                    }

                    continue;
                }

                if (! $this->isValidRelationPath((string) $relationshipName, $model)) {
                    continue;
                }

                $mappings = $this->normalizeWithMapping($mappings);

                if (is_array($mappings) && isset($mappings['functions'])) {
                    $functions = is_array($mappings['functions']) ? $mappings['functions'] : [$mappings['functions']];
                    $resolved[$relationshipName] = fn ($query) => array_reduce($functions, fn ($query, $function) => $query->$function(), $query);
                } else {
                    $resolved[$relationshipName] = $mappings;
                }
            }

            return $this->mergeIndexWiths($resolved, $plainNumeric);
        }

        foreach ($with as $withItem) {
            if (! is_string($withItem)) {
                continue;
            }

            if (! $this->isValidRelationPath($withItem, $model)) {
                continue;
            }

            $resolved[] = $withItem;
        }

        return $resolved;
    }

    protected function deriveHeaderWithsFromDotNotation(array $header, Model $model): array
    {
        $candidates = $this->extractRelationCandidatesFromHeader($header);
        $derived = [];

        foreach ($candidates as $candidate) {
            $path = $this->deriveRelationPathFromCandidate($candidate, $model);
            if ($path === null) {
                continue;
            }

            $derived[] = $path;
        }

        return array_values(array_unique($derived));
    }

    protected function extractRelationCandidatesFromHeader(array $header): array
    {
        $candidates = [];

        foreach (['key', 'searchKey'] as $keyName) {
            $value = $header[$keyName] ?? null;
            if (is_string($value) && $value !== '') {
                $candidates[] = $value;
            }
        }

        return $candidates;
    }

    protected function normalizeWithValue($with): array
    {
        if (is_string($with)) {
            return [$with];
        }

        if (is_object($with)) {
            $with = (array) $with;
        }

        if (! is_array($with)) {
            return [];
        }

        return $with;
    }

    protected function normalizeWithMapping($mapping)
    {
        if (is_object($mapping)) {
            return (array) $mapping;
        }

        return $mapping;
    }

    protected function deriveRelationPathFromCandidate(string $candidate, Model $rootModel): ?string
    {
        $tokens = explode('.', $candidate);
        $model = $rootModel;
        $relations = [];

        foreach ($tokens as $token) {
            if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $token)) {
                break;
            }

            if (! $this->isRelationNameDefined($model, $token)) {
                break;
            }

            $relations[] = $token;
            $model = $this->getRelatedModelForRelation($model, $token);

            if (! $model) {
                break;
            }
        }

        return count($relations) > 0 ? implode('.', $relations) : null;
    }

    protected function isValidRelationPath(string $path, Model $model): bool
    {
        $tokens = explode('.', $path);
        $current = $model;

        foreach ($tokens as $token) {
            if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $token)) {
                return false;
            }

            if (! $this->isRelationNameDefined($current, $token)) {
                return false;
            }

            $current = $this->getRelatedModelForRelation($current, $token);
            if (! $current) {
                return false;
            }
        }

        return true;
    }

    protected function isRelationNameDefined(Model $model, string $relation): bool
    {
        if (method_exists($model, 'definedRelations')) {
            return in_array($relation, $this->getDefinedRelationsForModel($model), true);
        }

        if (method_exists($model, 'hasRelation')) {
            return (bool) $model->hasRelation($relation);
        }

        return method_exists($model, $relation);
    }

    protected function getDefinedRelationsForModel(Model $model): array
    {
        $class = get_class($model);
        if (! array_key_exists($class, $this->definedRelationsCache)) {
            $this->definedRelationsCache[$class] = method_exists($model, 'definedRelations')
                ? $model->definedRelations()
                : [];
        }

        return $this->definedRelationsCache[$class];
    }

    protected function getRelatedModelForRelation(Model $model, string $relation): ?Model
    {
        try {
            $relationObject = $model->{$relation}();

            return method_exists($relationObject, 'getRelated')
                ? $relationObject->getRelated()
                : null;
        } catch (\Throwable $th) {
            return null;
        }
    }

    protected function mergeIndexWiths(array $baseWiths, array $incomingWiths): array
    {
        [$assocWiths, $plainWiths] = $this->splitWiths($baseWiths);
        [$incomingAssocWiths, $incomingPlainWiths] = $this->splitWiths($incomingWiths);

        foreach ($incomingAssocWiths as $relation => $mapping) {
            $assocWiths[$relation] = $this->mergeIndexWiths($assocWiths[$relation] ?? [], $mapping);

            foreach (array_keys($plainWiths) as $path) {
                if ($path === $relation || str_starts_with($path, $relation . '.')) {
                    unset($plainWiths[$path]);
                }
            }
        }

        foreach (array_keys($incomingPlainWiths) as $path) {
            if ($this->mergeDotPathIntoAssocIfApplicable($path, $assocWiths)) {
                continue;
            }

            if ($this->isPathCoveredByAssoc($path, $assocWiths)) {
                continue;
            }

            $plainWiths[$path] = true;
        }

        $this->collapsePlainHierarchyPathsIntoAssoc($assocWiths, $plainWiths);

        return $assocWiths + array_keys($plainWiths);
    }

    /**
     * Promote plain paths that share a root (e.g. plain "creator" plus "creator.company",
     * "creator.roles") into a single assoc entry: "creator" => ["roles", "company"].
     *
     * Nested segment names are merged with {@see mergeIndexWiths} and ordered descending
     * for stable, predictable ordering (e.g. roles before company).
     */
    protected function collapsePlainHierarchyPathsIntoAssoc(array &$assocWiths, array &$plainWiths): void
    {
        $pathsByRoot = [];

        foreach (array_keys($plainWiths) as $path) {
            if (! str_contains($path, '.')) {
                continue;
            }

            $firstDot = mb_strpos($path, '.');
            $root = mb_substr($path, 0, $firstDot);
            $rest = mb_substr($path, $firstDot + 1);

            if ($root === '' || $rest === '') {
                continue;
            }

            $pathsByRoot[$root][$path] = $rest;
        }

        foreach ($pathsByRoot as $root => $pathToRest) {
            if (count($pathToRest) === 0) {
                continue;
            }

            $hasPlainRoot = isset($plainWiths[$root]);
            $dottedCount = count($pathToRest);

            if (! $this->shouldCollapsePlainPathsForRoot($root, $hasPlainRoot, $dottedCount, $plainWiths)) {
                continue;
            }

            $nested = array_values(array_unique(array_values($pathToRest)));
            rsort($nested, SORT_STRING);

            if (isset($assocWiths[$root])) {
                $mapping = $this->normalizeWithMapping($assocWiths[$root]);

                if (! is_array($mapping) || isset($mapping['functions'])) {
                    continue;
                }

                $assocWiths[$root] = $this->mergeIndexWiths(
                    is_array($assocWiths[$root]) ? $assocWiths[$root] : [],
                    $nested
                );
            } else {
                $assocWiths[$root] = $this->mergeIndexWiths([], $nested);
            }

            foreach (array_keys($pathToRest) as $path) {
                unset($plainWiths[$path]);
            }

            if ($hasPlainRoot) {
                unset($plainWiths[$root]);
            }
        }
    }

    /**
     * Decide whether dotted paths sharing the same first segment should fold into one assoc key.
     *
     * - Multiple "root.*" paths (e.g. creator.company + creator.roles) always collapse.
     * - Plain "root" plus any "root.*" collapses.
     * - A single "root.rest" collapses unless another single-segment plain path exists as a
     *   sibling (e.g. roles + company.logo — company.logo must stay one nested eager string).
     */
    protected function shouldCollapsePlainPathsForRoot(string $root, bool $hasPlainRoot, int $dottedCount, array $plainWiths): bool
    {
        if ($dottedCount >= 2) {
            return true;
        }

        if ($hasPlainRoot) {
            return true;
        }

        foreach (array_keys($plainWiths) as $path) {
            if (str_contains($path, '.')) {
                continue;
            }

            if ($path !== $root) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merge a dotted eager path (e.g. "creator.company") into an existing assoc entry
     * (e.g. "creator" => ["roles"]) so nested segments become sibling nested loads:
     * "creator" => ["roles", "company"].
     */
    protected function mergeDotPathIntoAssocIfApplicable(string $path, array &$assocWiths): bool
    {
        if (! str_contains($path, '.')) {
            return false;
        }

        $firstDot = mb_strpos($path, '.');
        $root = mb_substr($path, 0, $firstDot);
        $rest = mb_substr($path, $firstDot + 1);

        if ($root === '' || $rest === '') {
            return false;
        }

        if (! isset($assocWiths[$root])) {
            return false;
        }

        $mapping = $this->normalizeWithMapping($assocWiths[$root]);

        if (! is_array($mapping) || isset($mapping['functions'])) {
            return false;
        }

        $assocWiths[$root] = $this->mergeIndexWiths(
            is_array($assocWiths[$root]) ? $assocWiths[$root] : [],
            [$rest]
        );

        return true;
    }

    protected function splitWiths(array $withs): array
    {
        $assocWiths = [];
        $plainWiths = [];

        foreach ($withs as $key => $value) {
            if (is_string($key)) {
                $assocWiths[$key] = $value;
            } elseif (is_string($value)) {
                $plainWiths[$value] = true;
            }
        }

        return [$assocWiths, $plainWiths];
    }

    protected function isPathCoveredByAssoc(string $path, array $assocWiths): bool
    {
        $segments = explode('.', $path);
        $root = array_shift($segments);

        if (! isset($assocWiths[$root])) {
            return false;
        }

        if (count($segments) === 0) {
            return true;
        }

        $mapping = $this->normalizeWithMapping($assocWiths[$root]);

        if (! is_array($mapping)) {
            return true;
        }

        $remaining = implode('.', $segments);

        foreach ($mapping as $candidate) {
            if (is_string($candidate)) {
                if ($candidate === $remaining || str_starts_with($candidate, $remaining . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getLoadedRelationForFormatting($item, string $relation, bool $preferEager)
    {
        if (! $preferEager) {
            return null;
        }

        if (! $item instanceof Model) {
            return null;
        }

        if (! $item->relationLoaded($relation)) {
            return null;
        }

        return $item->getRelation($relation);
    }

    protected function isRelationLoadedForFormatting($item, string $relation, bool $preferEager): bool
    {
        return $preferEager
            && $item instanceof Model
            && $item->relationLoaded($relation);
    }

    protected function getRelatedItemForFormatting($item, string $relation, bool $preferEager)
    {
        $loaded = $this->getLoadedRelationForFormatting($item, $relation, $preferEager);
        if ($loaded !== null) {
            return $loaded;
        }

        return $item->{$relation};
    }
}
