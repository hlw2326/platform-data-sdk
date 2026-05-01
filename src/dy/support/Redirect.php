<?php

namespace Hlw\Collect\Dy\Support;

use Hlw\Collect\Support\Redirect as BaseRedirect;

class Redirect
{
    private const USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';

    public static function follow(string $url, int $timeout = 8000, int $maxRedirects = 5): string
    {
        return BaseRedirect::follow($url, self::USER_AGENT, $timeout, $maxRedirects);
    }
}
