<?php

namespace Hlw\Collect\Dy\H5;

use Hlw\Collect\Request\BaseRequest;

class Request extends BaseRequest
{
    public function __construct(array $defaultHeaders = [])
    {
        parent::__construct([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1',
            'Referer' => 'https://m.douyin.com/',
            ...$defaultHeaders,
        ]);
    }
}
