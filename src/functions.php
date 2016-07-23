<?php

namespace WyriHaximus\ApiClient;

use ReflectionClass;
use ReflectionProperty;
use WyriHaximus\ApiClient\Resource\ResourceInterface;

/**
 * @param ResourceInterface $resource
 */
function debug_print(ResourceInterface $resource)
{
    $printResource = clone $resource;

    call_method($printResource, 'unsetTransport');

    var_export($printResource);
}

/**
 * @param ResourceInterface $resource
 * @param string $method
 * @return ResourceInterface
 */
function call_method(ResourceInterface $resource, string $method): ResourceInterface
{
    if (method_exists($resource, $method)) {
        $resource->$method();
    }

    $properties = get_properties($resource);
    call_method_properties($resource, $properties, $method);

    return $resource;
}

/**
 * @param ResourceInterface $resource
 * @param array $properties
 * @param string $method
 */
function call_method_properties(ResourceInterface $resource, array $properties, string $method)
{
    foreach ($properties as $property) {
        call_method_property($resource, $property->getName(), $method);
        call_method_array_property($resource, $property->getName(), $method);
    }
}

/**
 * @param ResourceInterface $resource
 * @param string $propertyName
 * @param string $method
 */
function call_method_property(ResourceInterface $resource, string $propertyName, string $method)
{
    $property = get_property($resource, $propertyName);
    if (!($property->getValue($resource) instanceof ResourceInterface)) {
        return;
    }

    $property->setValue(
        $resource,
        call_method(
            $property->getValue($resource),
            $method
        )
    );
}

/**
 * @param ResourceInterface $resource
 * @param string $propertyName
 * @param string $method
 */
function call_method_array_property(ResourceInterface $resource, string $propertyName, string $method)
{
    $property = get_property($resource, $propertyName);
    if (!is_array($property->getValue($resource))) {
        return;
    }
    $newCollection = [];

    foreach ($property->getValue($resource) as $key => $value) {
        if (!($value instanceof ResourceInterface)) {
            continue;
        }

        $newCollection[$key] = call_method($value, $method);
    }

    $property->setValue($resource, $newCollection);
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
