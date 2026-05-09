<?php

namespace Unusualify\Modularous\Hydrates;

use Unusualify\Modularous\Traits\ResponsiveVisibility;

class HeaderHydrator
{
    use ResponsiveVisibility;

    public function __construct(
        protected $header,
        protected $module,
        protected $routeName,
    ) {}

    public function hydrate(): array
    {
        $header = $this->header;
        // switch column
        if (isset($header['formatter']) && count($header['formatter']) && $header['formatter'][0] == 'switch') {
            $header['width'] = '20px';
            // $header['align'] = 'center';
        }

        $key = $header['key'] ?? $header['sourceKey'] ?? null;

        if (isset($header['sortable']) && $header['sortable']) {
            if (preg_match('/(.*)(_relation)/', $header['key'], $matches)) {
                $header['sortable'] = false;
            }

        }

        if ($key == 'actions') {
            $header['width'] ??= 100;
            $header['align'] ??= 'center';
            $header['sortable'] ??= false;
            $header['fixed'] ??= 'end';
        }

        if (! empty($header['groupable']) && $header['groupable'] === true) {
            $order = $header['groupOrder'] ?? 'asc';
            $header['groupOrder'] = in_array($order, ['asc', 'desc'], true) ? $order : 'asc';
        }

        if (isset($header['noMobile']) && $header['noMobile']) {
            $header['responsive'] ??= [];
            $header['responsive'] = [
                'hideBelow' => 'md',
            ];
        }

        if (isset($header['responsive']) && $header['responsive']) {
            $header = $this->applyResponsiveClasses($header, 'responsive', 'table-cell', 'cellProps.class');
        }

        $header = array_merge_recursive_preserve($this->defaultHeader(), $header);

        $header['visible'] ??= true;

        return $header;
    }

    protected function defaultHeader()
    {
        return modularousConfig('default_header');
    }
}
