<?php

namespace Hlw\Collect\Dy\Live\User;

use Hlw\Collect\Dy\Live\Request;
use Hlw\Collect\Dy\Support\InputParser;
use Hlw\Collect\Dy\Support\Options;

class Profile
{
    public function __construct(private Request $client, private array $defaultOptions = [])
    {
    }

    public function profile(string $input, array|string $options = []): ProfileResponse
    {
        $options = Options::merge($this->defaultOptions, $options);
        $secUid = InputParser::secUid($input, (int)($options['timeout'] ?? 8000));
        $params = ['sec_target_uid' => $secUid, 'enter_from' => 'web_homepage_discover'];

        foreach (['targetUid' => 'target_uid', 'anchorId' => 'anchor_id', 'secAnchorId' => 'sec_anchor_id', 'currentRoomId' => 'current_room_id', 'clickSource' => 'click_source'] as $optionKey => $paramKey) {
            if (isset($options[$optionKey])) {
                $params[$paramKey] = $options[$optionKey];
                unset($options[$optionKey]);
            }
        }

        $params['anchor_id'] = $params['anchor_id'] ?? '100001';
        $options['params'] = [...$params, ...($options['params'] ?? [])];
        return new ProfileResponse($this->client->get('https://live.douyin.com/webcast/user/profile/', $options));
    }
}
