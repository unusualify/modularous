<?php

namespace Unusualify\Modularity\Traits;

trait Traitify
{
    /**
     * Get the methods for method name and current trait name.
     *
     * {methodName}{traitName}
     *
     * @param string|null $method
     * @return string[]
     */
    protected function traitsMethods(?string $method = null)
    {
        $method = $method ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $methods = array_map(function (string $trait) use ($method) {
            return $method . $trait;
        }, $uniqueTraits);

        return array_filter($methods, function (string $method) {
            return method_exists(get_called_class(), $method);
        });
    }

    /**
     * Get the properties for property name.
     *
     * {propertyName}{traitName}
     *
     * @param string|null $property
     * @return array
     */
    protected function traitProperties(?string $property = null)
    {
        $property = $property ?? debug_backtrace()[1]['function'];

        $traits = array_values(class_uses_recursive(get_called_class()));

        $uniqueTraits = array_unique(array_map('class_basename', $traits));

        $properties = array_map(function (string $trait) use ($property) {
            return $property . $trait;
        }, $uniqueTraits);

        return array_filter($properties, function (string $property) {
            return property_exists(get_called_class(), $property);
        });
    }
}
