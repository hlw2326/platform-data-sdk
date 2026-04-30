<?php

namespace Hlw\Collect\Dy\Web\Aweme;

use Hlw\Collect\Dy\Support\InputParser;
use Hlw\Collect\Dy\Support\Options;
use Hlw\Collect\Dy\Web\Request;
use Hlw\Collect\Dy\Web\Support\WebParams;

class Aweme
{
    public function __construct(private Request $client, private array $defaultOptions = [])
    {
    }

    public function post(string $input, array|string $options = []): PostResponse
    {
        $options = Options::merge($this->defaultOptions, $options);
        $secUid = InputParser::secUid($input, (int)($options['timeout'] ?? 8000));
        $params = [
            'channel' => 'channel_pc_web',
            'sec_user_id' => $secUid,
            'max_cursor' => $options['cursor'] ?? '0',
            'locate_query' => 'false',
            'show_live_replay_strategy' => '1',
            'need_time_list' => '1',
            'time_list_query' => '0',
            'whale_cut_token' => '',
            'cut_version' => '1',
            'count' => $options['count'] ?? '18',
            'publish_video_strategy_type' => '2',
            'from_user_page' => '1',
            ...WebParams::build(),
        ];

        foreach (['webid', 'uifid', 'msToken', 'verifyFp', 'fp'] as $key) {
            if (isset($options[$key])) {
                $params[$key] = $options[$key];
            }
            unset($options[$key]);
        }

        unset($options['cursor'], $options['count']);
        $options['params'] = [...$params, ...($options['params'] ?? [])];
        $options['headers'] = ['Referer' => "https://www.douyin.com/user/{$secUid}", ...($options['headers'] ?? [])];

        return new PostResponse($this->client->get('https://www.douyin.com/aweme/v1/web/aweme/post/', $options));
    }
}
