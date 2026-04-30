<?php

namespace Hlw\Collect;

use Hlw\Collect\Ks\Mini\Mini;
use Hlw\Collect\Ks\Mini\Sig3;

class Ks
{
    public static function Mini(array|string $options = []): Mini
    {
        return new Mini($options);
    }

    public static function sig3(string $arg): string
    {
        return (new Sig3())->generate($arg);
    }
}
