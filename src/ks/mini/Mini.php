<?php

namespace Hlw\Collect\Ks\Mini;

use Hlw\Collect\Ks\Support\Options;

class Mini
{
    private array $defaultOptions;

    public function __construct(array|string $defaultOptions = [])
    {
        $this->defaultOptions = Options::normalize($defaultOptions);
    }

    public function v1(array|string $options = []): V1
    {
        return new V1(null, null, Options::merge($this->defaultOptions, $options));
    }
}
