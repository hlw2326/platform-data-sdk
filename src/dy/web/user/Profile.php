<?php

namespace Hlw\Collect\Dy\Web\User;

use Hlw\Collect\Dy\Support\InputParser;
use Hlw\Collect\Dy\Support\Options;
use Hlw\Collect\Dy\Web\Request;

class Profile
{
    public function __construct(private Request $client, private array $defaultOptions = [])
    {
    }

    public function info(string $input, array|string $options = []): ProfileResponse
    {
        $options = Options::merge($this->defaultOptions, $options);
        $secUid = InputParser::secUid($input, (int)($options['timeout'] ?? 8000));
        $options['headers'] = ['Referer' => "https://www.douyin.com/user/{$secUid}", ...($options['headers'] ?? [])];
        return new ProfileResponse($this->client->get("https://www.douyin.com/user/{$secUid}", $options));
    }
}
