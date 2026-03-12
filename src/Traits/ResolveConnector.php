<?php

namespace Unusualify\Modularity\Traits;

use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Services\Connector;

trait ResolveConnector
{
    /**
     * @param string $connector
     * @return \Unusualify\Modularity\Services\Connector
     */
    protected function findConnectorRepository($connector)
    {
        $parsedConnector = find_module_and_route($connector);

        return $parsedConnector['module']->getRepository($parsedConnector['route']);
    }

    /**
     * @param string $connector
     * @return \Unusualify\Modularity\Services\Connector
     */
    protected function findNewConnectorRepository($connector)
    {
        $connector = new Connector($connector);

        return $connector->getRepository();
    }
}
