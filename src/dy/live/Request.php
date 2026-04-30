<?php

namespace Hlw\Collect\Dy\Live;

use Hlw\Collect\Request\BaseRequest;

class Request extends BaseRequest
{
    public function __construct(array $defaultParams = [], array $defaultHeaders = [])
    {
        parent::__construct([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
            'Referer' => 'https://live.douyin.com/',
            'Origin' => 'https://live.douyin.com',
            ...$defaultHeaders,
        ], [
            'aid' => '6383',
            'app_name' => 'douyin_web',
            'live_id' => '1',
            'device_platform' => 'web',
            'language' => 'zh-CN',
            'cookie_enabled' => 'true',
            'screen_width' => '2560',
            'screen_height' => '1440',
            'browser_language' => 'zh-CN',
            'browser_platform' => 'Win32',
            'browser_name' => 'Chrome',
            'browser_version' => '146.0.0.0',
            ...$defaultParams,
        ]);
    }
}
