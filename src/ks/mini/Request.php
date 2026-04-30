<?php

namespace Hlw\Collect\Ks\Mini;

use Hlw\Collect\Request\BaseRequest;

class Request extends BaseRequest
{
    public function __construct(array $defaultHeaders = [])
    {
        parent::__construct([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            'Referer' => 'https://www.kuaishou.com/',
            'Origin' => 'https://www.kuaishou.com',
            ...$defaultHeaders,
        ]);
    }
}
