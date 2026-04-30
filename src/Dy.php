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

    public static function Live(array|string $options = []): Live
    {
        return new Live($options);
    }

    public static function Web(array|string $options = []): Web
    {
        return new Web($options);
    }

    public static function aBogus(string $params, string $ua): string
    {
        return ABogus::generate($params, $ua);
    }
}
