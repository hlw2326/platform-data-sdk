<?php

namespace Hlw\Collect\Dy\H5\User;

use Hlw\Collect\Dy\H5\Request;
use Hlw\Collect\Dy\Support\InputParser;
use Hlw\Collect\Dy\Support\Options;

class Profile
{
    public function __construct(private Request $client, private array $defaultOptions = [])
    {
    }

    public function info(string $input, array|string $options = []): ProfileResponse
    {
        $options = Options::merge($this->defaultOptions, $options);
        $secUid = InputParser::secUid($input, (int)($options['timeout'] ?? 8000));
        return new ProfileResponse($this->client->get("https://m.douyin.com/share/user/{$secUid}", $options));
    }
}
