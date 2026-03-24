<?php

namespace Unusualify\Modularity\Traits;

use Unusualify\Modularity\Services\Connector;

trait ResolveConnector
{
    /**
     * @param string $connector
     * @return Connector
     */
    protected function findConnectorRepository($connector)
    {
        $parsedConnector = find_module_and_route($connector);

        return $parsedConnector['module']->getRepository($parsedConnector['route']);
    }

    /**
     * @param string $connector
     * @return Connector
     */
    protected function findNewConnectorRepository($connector)
    {
        $connector = new Connector($connector);

        return $connector->getRepository();
    }
}
