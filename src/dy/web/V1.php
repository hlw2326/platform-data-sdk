<?php

namespace Hlw\Collect\Dy\Web;

use Hlw\Collect\Dy\Support\Options;
use Hlw\Collect\Dy\Web\Aweme\Aweme;
use Hlw\Collect\Dy\Web\User\Profile;

class V1
{
    public readonly Profile $user;
    public readonly Aweme $aweme;

    public function __construct(?Request $client = null, array|string $defaultOptions = [])
    {
        $client ??= new Request();
        $defaultOptions = Options::normalize($defaultOptions);

        $this->user = new Profile($client, $defaultOptions);
        $this->aweme = new Aweme($client, $defaultOptions);
    }
}
