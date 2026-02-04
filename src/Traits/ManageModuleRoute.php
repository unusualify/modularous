<?php

namespace Unusualify\Modularity\Traits;

use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Module;

trait ManageModuleRoute
{
    use Moduleable;

    protected ?Module $module = null;

    protected ?array $routeConfig = [];

    /**
     * @deprecated use Moduleable::getModuleName() instead
     *
     * @return string|null
     */
    public function moduleName()
    {
        return $this->getModuleName();
    }

    /**
     * @deprecated use Moduleable::getRouteName() instead
     *
     * @return string|null
     */
    public function routeName()
    {
        return $this->getRouteName();
    }

    /**
     * @return Module|null
     */
    public function getModule()
    {
        if ($this->module) {
            return $this->module;
        }

        $this->module = Modularity::find($this->getModuleName());

        return $this->module;
    }

    /**
     * @return $this
     */
    public function setModule(Module $module): static
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteConfig()
    {
        if ($this->routeConfig && ! empty($this->routeConfig)) {
            return $this->routeConfig;
        }

        $moduleName = $this->getModuleName();

        $routeName = $this->getRouteName();

        $module = $this->getModule();

        if ($module) {
            $this->routeConfig = $module->getRawRouteConfig($routeName);
        }

        return $this->routeConfig;
    }

    public function getRouteTitleColumnKey(): string
    {
        return ! empty($conf = $this->getRouteConfig()) ? ($conf['title_column_key'] ?? 'name') : 'name';
    }

    public function getRouteInputs(): array
    {
        return ! empty($conf = $this->getRouteConfig()) ? ($conf['inputs'] ?? []) : [];
    }

    public function getRouteHeaders(): array
    {

        return ! empty($conf = $this->getRouteConfig()) ? ($conf['headers'] ?? []) : [];
    }

    public function getRouteTableOptions(): array
    {

        return ! empty($conf = $this->getRouteConfig()) ? ($conf['table_options'] ?? []) : [];
    }
}
