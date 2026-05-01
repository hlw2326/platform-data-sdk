<?php

namespace Hlw\Collect\Ks\Mini\Feed;

/**
 * @phpstan-import-type FeedItemArray from \Hlw\Collect\Types\FeedItemType
 * @psalm-import-type FeedItemArray from \Hlw\Collect\Types\FeedItemType
 */
class ListResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    /**
     * @return list<FeedItemArray>
     */
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

    /**
     * @return FeedItemArray
     */
    public static function parseFeed(array $feed): array
    {
        return [
            'platform' => 'ks',
            'type' => 'feed',
            'item_id' => (string)($feed['photoId'] ?? $feed['id'] ?? ''),
            'desc' => (string)($feed['caption'] ?? $feed['title'] ?? ''),
            'create_time' => (int)floor(($feed['timestamp'] ?? 0) / 1000),
            'duration' => (int)($feed['duration'] ?? 0),
            'cover_url' => self::firstUrl($feed['coverUrls'] ?? []) ?: self::firstUrl($feed['webpCoverUrls'] ?? []),
            'video_url' => self::firstUrl($feed['mainMvUrls'] ?? []),
            'share_url' => (string)($feed['shareUrl'] ?? $feed['share_url'] ?? $feed['webShareInfo']['shareUrl'] ?? ''),
            'width' => (int)($feed['width'] ?? 0),
            'height' => (int)($feed['height'] ?? 0),
            'is_top' => (bool)($feed['isTop'] ?? $feed['is_top'] ?? false),
            'total' => [
                'like_count' => (int)($feed['likeCount'] ?? 0),
                'comment_count' => (int)($feed['commentCount'] ?? 0),
                'share_count' => (int)($feed['forwardCount'] ?? $feed['shareCount'] ?? 0),
                'collect_count' => (int)($feed['collectCount'] ?? 0),
                'play_count' => (int)($feed['viewCount'] ?? 0),
            ],
            'author' => [
                'user_id' => (string)($feed['userId'] ?? ''),
                'sec_user_id' => (string)($feed['userEid'] ?? ''),
                'display_id' => (string)($feed['kwaiId'] ?? ''),
                'nickname' => (string)($feed['userName'] ?? ''),
                'avatar_url' => (string)($feed['headUrl'] ?? self::firstUrl($feed['headUrls'] ?? [])),
            ],
            'tags' => [],
        ];
    }

    private static function firstUrl(mixed $urls): string
    {
        if (!is_array($urls)) {
            return '';
        }

        if (isset($urls['url']) && is_string($urls['url'])) {
            return $urls['url'];
        }

        if (isset($urls['url_list'][0]) && is_string($urls['url_list'][0])) {
            return $urls['url_list'][0];
        }

        foreach ($urls as $item) {
            if (is_array($item) && isset($item['url']) && is_string($item['url'])) {
                return $item['url'];
            }
            if (is_string($item)) {
                return $item;
            }
        }

        return '';
    }
}
