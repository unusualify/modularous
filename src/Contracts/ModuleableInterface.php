<?php

namespace Unusualify\Modularity\Contracts;

interface ModuleableInterface
{
    public function getModuleName(): string|null;

    public function getRouteName(): string|null;

    public function setModuleName(string $moduleName): static;

    public function setRouteName(string $routeName): static;
}
