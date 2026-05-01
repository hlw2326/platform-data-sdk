<?php

namespace Hlw\Collect\Dy\Live;

use Hlw\Collect\Dy\Support\Options;

class Live
{
    private array $defaultOptions;

    public function __construct(array|string $defaultOptions = [], ?string $ua = null)
    {
        $this->defaultOptions = Options::normalize($defaultOptions, $ua);
    }

    public function v1(array|string $options = [], ?string $ua = null): V1
    {
        return new V1(new Request(), Options::merge($this->defaultOptions, $options, $ua));
    }
}
