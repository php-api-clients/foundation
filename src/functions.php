<?php declare(strict_types=1);

namespace ApiClients\Foundation;

/**
 * @param  array $base
 * @param  array $options
 * @return array
 */
function options_merge(array $base, array $options): array
{
    $merge = true;
    foreach ($base as $key => $value) {
        if (is_numeric($key)) {
            $merge = false;
        }
    }
    foreach ($options as $name => $option) {
        if (is_numeric($name)) {
            $merge = false;
        }
    }

    if ($merge === false) {
        $new = [];

        foreach ($base as $key => $value) {
            if (in_array($value, $new, true)) {
                continue;
            }
            $new[] = $value;
        }
        foreach ($options as $name => $option) {
            if (in_array($option, $new, true)) {
                continue;
            }
            $new[] = $option;
        }

        return $new;
    }

    foreach ($base as $key => $value) {
        if (!isset($options[$key])) {
            continue;
        }

        $option = $options[$key];
        unset($options[$key]);

        if (is_array($value) && is_array($option)) {
            $base[$key] = options_merge($value, $option);
            continue;
        }

        $base[$key] = $option;
    }

    foreach ($options as $name => $option) {
        $base[$name] = $option;
    }

    return $base;
}
