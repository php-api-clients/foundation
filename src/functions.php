<?php

namespace ApiClients\Foundation;

use ReflectionClass;
use ReflectionProperty;
use ApiClients\Foundation\Resource\ResourceInterface;

/**
 * @param ResourceInterface $resource
 * @param int $indentLevel
 * @param bool $resourceIndent
 */
function resource_pretty_print(ResourceInterface $resource, int $indentLevel = 0, bool $resourceIndent = false)
{
    $indent = str_repeat("\t", $indentLevel);
    $propertyIndent = str_repeat("\t", $indentLevel + 1);

    if ($resourceIndent) {
        echo $indent;
    }
    echo get_class($resource), PHP_EOL;

    foreach (get_properties($resource) as $property) {
        echo $propertyIndent, $property->getName(), ': ';

        $propertyValue = get_property($resource, $property->getName())->getValue($resource);

        if ($propertyValue instanceof ResourceInterface) {
            resource_pretty_print($propertyValue, $indentLevel + 1);
            continue;
        }

        if (is_array($propertyValue)) {
            echo '[', PHP_EOL;
            foreach ($propertyValue as $arrayValue) {
                resource_pretty_print($arrayValue, $indentLevel + 2, true);
            }
            echo $propertyIndent, ']', PHP_EOL;
            continue;
        }

        echo $propertyValue, PHP_EOL;
    }
}

/**
 * @param ResourceInterface $resource
 * @return array
 */
function get_properties(ResourceInterface $resource): array
{
    $class = new ReflectionClass($resource);
    return $class->getProperties();
}

/**
 * @param ResourceInterface $resource
 * @param string $property
 * @return ReflectionProperty
 */
function get_property(ResourceInterface $resource, string $property)
{
    $class = new ReflectionClass($resource);
    $prop = $class->getProperty($property);
    $prop->setAccessible(true);
    return $prop;
}
