<?php

namespace Hlw\Collect\Dy\Support;

class Options
{
    public static function normalize(array|string $options, ?string $ua = null): array
    {
        $normalized = is_string($options) ? ['cookies' => $options] : $options;

        if (isset($normalized['ua']) && !isset($normalized['userAgent'])) {
            $normalized['userAgent'] = $normalized['ua'];
        }
        unset($normalized['ua']);

        if ($ua !== null) {
            $normalized['userAgent'] = $ua;
        }

        return $normalized;
    }

    public static function merge(array|string $base, array|string $override, ?string $ua = null): array
    {
        return [...self::normalize($base), ...self::normalize($override, $ua)];
    }
}
