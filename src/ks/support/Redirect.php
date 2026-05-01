<?php

namespace Hlw\Collect\Ks\Support;

use Hlw\Collect\Support\Redirect as BaseRedirect;

class Redirect
{
    private const USER_AGENT = 'Mozilla/5.0';

    public static function follow(string $url, int $timeout = 8000, int $maxRedirects = 5): string
    {
        return BaseRedirect::follow($url, self::USER_AGENT, $timeout, $maxRedirects);
    }
}
