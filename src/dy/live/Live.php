<?php

namespace Hlw\Collect\Dy\Live;

use Hlw\Collect\Dy\Support\Options;

class Live
{
    private array $defaultOptions;

    public function __construct(array|string $defaultOptions = [])
    {
        $this->defaultOptions = Options::normalize($defaultOptions);
    }

    public function v1(array|string $options = []): V1
    {
        return new V1(new Request(), Options::merge($this->defaultOptions, $options));
    }
}
