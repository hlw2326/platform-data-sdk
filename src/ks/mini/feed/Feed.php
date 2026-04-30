<?php

namespace Hlw\Collect\Ks\Mini\Feed;

use Hlw\Collect\Ks\Mini\Endpoint;

class Feed extends Endpoint
{
    public function list(string $input, array|string $options = []): ListResponse
    {
        return $this->listByEid($this->eid($input, $options), $options);
    }

    public function listByEid(string $eid, array|string $options = []): ListResponse
    {
        $options = $this->options($options);
        $count = $options['count'] ?? 12;
        unset($options['count']);

        return new ListResponse($this->signedPost('/feed/profile', ['eid' => $eid, 'count' => $count], $options));
    }

    public function recommend(array|string $options = []): mixed
    {
        $options = $this->options($options);
        $count = $options['count'] ?? 10;
        $pcursor = $options['pcursor'] ?? '0';
        $userId = $options['userId'] ?? '';
        unset($options['count'], $options['pcursor'], $options['userId']);

        $data = [
            'count' => $count,
            'pcursor' => $pcursor,
            'portal' => 1,
            'needLivestream' => true,
            'supportUseNewFeedView' => true,
            'extraRequestInfo' => json_encode([
                'scene' => 1256,
                'curPhotoIndex' => 1,
                'adShow' => true,
                'fid' => '',
                'sharerUserId' => '',
                'page' => 1,
                'user_id' => $userId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'thirdPartyUserId' => $userId,
            'pageType' => 2,
            'sourceFrom' => 2,
        ];

        return $this->signedPost('/feed/recommend', $data, $options);
    }
}
