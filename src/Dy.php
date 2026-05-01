<?php

namespace Hlw\Collect;

use Hlw\Collect\Dy\H5\H5;
use Hlw\Collect\Dy\Live\Live;
use Hlw\Collect\Dy\Web\Signature\ABogus;
use Hlw\Collect\Dy\Web\Web;

class Dy
{
    public static function H5(array|string $options = []): H5
    {
        return new H5($options);
    }

    public static function Live(array|string $options = [], ?string $ua = null): Live
    {
        return new Live($options, $ua);
    }

    public static function Web(array|string $options = [], ?string $ua = null): Web
    {
        return new Web($options, $ua);
    }

    public static function aBogus(string $params, string $ua): string
    {
        return ABogus::generate($params, $ua);
    }
}
