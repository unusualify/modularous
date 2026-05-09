<?php

namespace Unusualify\Modularous\Contracts;

interface ModuleableInterface
{
    public function getModuleName(): ?string;

    public function getRouteName(): ?string;

    public function setModuleName(string $moduleName): static;

    public function setRouteName(string $routeName): static;
}
