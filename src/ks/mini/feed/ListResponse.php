<?php

namespace Hlw\Collect\Ks\Mini\Feed;

class ListResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function toArray(): array
    {
        if (!is_array($this->raw)) {
            return [];
        }

        $feeds = $this->raw['feeds'] ?? $this->raw['list'] ?? $this->raw['photoList'] ?? [];
        if (!is_array($feeds)) {
            return [];
        }

        $result = [];
        foreach ($feeds as $feed) {
            if (is_array($feed)) {
                $result[] = self::parseFeed($feed);
            }
        }

        return $result;
    }

    public static function parseFeed(array $feed): array
    {
        return [
            'id' => $feed['photoId'] ?? '',
            'title' => $feed['caption'] ?? '',
            'cover' => $feed['coverUrls'][0]['url'] ?? $feed['webpCoverUrls'][0]['url'] ?? '',
            'video_url' => $feed['mainMvUrls'][0]['url'] ?? '',
            'duration' => $feed['duration'] ?? 0,
            'like_count' => $feed['likeCount'] ?? 0,
            'comment_count' => $feed['commentCount'] ?? 0,
            'share_count' => $feed['forwardCount'] ?? 0,
            'collect_count' => $feed['collectCount'] ?? 0,
            'view_count' => $feed['viewCount'] ?? 0,
            'width' => $feed['width'] ?? 0,
            'height' => $feed['height'] ?? 0,
            'create_time' => (int)floor(($feed['timestamp'] ?? 0) / 1000),
        ];
    }
}
