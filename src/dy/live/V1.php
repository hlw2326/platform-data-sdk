<?php

namespace Hlw\Collect\Dy\Live;

use Hlw\Collect\Dy\Live\User\Profile;
use Hlw\Collect\Dy\Support\Options;

class V1
{
    public readonly Profile $user;

    public function __construct(?Request $client = null, array|string $defaultOptions = [])
    {
        $this->user = new Profile($client ?? new Request(), Options::normalize($defaultOptions));
    }
}
