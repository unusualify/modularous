<?php

namespace Unusualify\Modularous\Facades;

use Illuminate\Support\Facades\Facade;
use Unusualify\Modularous\Services\CacheRelationshipGraph;

/**
 * @method static bool isEnabled()
 * @method static array getAffectedSubmodules(string $modelClass)
 * @method static array getAffectedSubmodulesByTable(string $tableName)
 * @method static array getGraph()
 * @method static array buildGraph()
 * @method static array rebuildGraph()
 * @method static void clearGraph()
 * @method static array getStats()
 * @method static array getVisualGraph()
 * @method static bool isCached()
 * @method static array analyzeImpact(string $modelOrTable)
 *
 * @see CacheRelationshipGraph
 */
class RelationshipGraph extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'modularous.relationship.graph';
    }
}
