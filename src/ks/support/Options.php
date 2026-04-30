<?php

namespace Hlw\Collect\Ks\Support;

class Options
{
    public static function normalize(array|string $options): array
    {
        return is_string($options) ? ['cookies' => $options] : $options;
    }

    public static function merge(array|string $base, array|string $override): array
    {
        return [...self::normalize($base), ...self::normalize($override)];
    }
}
