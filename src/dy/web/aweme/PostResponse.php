<?php

namespace Hlw\Collect\Dy\Web\Aweme;

/**
 * @phpstan-import-type FeedItemArray from \Hlw\Collect\Types\FeedItemType
 * @psalm-import-type FeedItemArray from \Hlw\Collect\Types\FeedItemType
 */
class PostResponse
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

        return array_map(fn($aweme) => self::parseAweme($aweme), $this->raw['aweme_list'] ?? []);
    }

    /**
     * @return FeedItemArray
     */
    public static function parseAweme(array $aweme): array
    {
        $statistics = $aweme['statistics'] ?? [];
        if (!is_array($statistics)) {
            $statistics = [];
        }
        $video = $aweme['video'] ?? [];
        if (!is_array($video)) {
            $video = [];
        }
        $author = $aweme['author'] ?? [];
        if (!is_array($author)) {
            $author = [];
        }
        $tags = $aweme['video_tag'] ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        return [
            'platform' => 'dy',
            'type' => 'feed',
            'item_id' => (string)($aweme['aweme_id'] ?? $aweme['group_id'] ?? ''),
            'desc' => (string)($aweme['desc'] ?? $aweme['caption'] ?? ''),
            'create_time' => (int)($aweme['create_time'] ?? 0),
            'duration_ms' => (int)($aweme['duration'] ?? $video['duration'] ?? 0),
            'cover_url' => self::firstUrl($video['cover'] ?? []) ?: self::firstUrl($video['origin_cover'] ?? []),
            'video_url' => self::firstUrl($video['play_addr'] ?? []) ?: self::firstUrl($video['play_addr_h264'] ?? []),
            'share_url' => (string)($aweme['share_url'] ?? $aweme['share_info']['share_url'] ?? ''),
            'width' => (int)($video['width'] ?? 0),
            'height' => (int)($video['height'] ?? 0),
            'is_top' => ($aweme['is_top'] ?? 0) === 1,
            'total' => [
                'like_count' => (int)($statistics['digg_count'] ?? 0),
                'comment_count' => (int)($statistics['comment_count'] ?? 0),
                'share_count' => (int)($statistics['share_count'] ?? 0),
                'collect_count' => (int)($statistics['collect_count'] ?? 0),
                'play_count' => (int)($statistics['play_count'] ?? 0),
            ],
            'author' => [
                'user_id' => (string)($author['uid'] ?? $author['id_str'] ?? $author['id'] ?? ''),
                'sec_user_id' => (string)($author['sec_uid'] ?? ''),
                'display_id' => (string)($author['unique_id'] ?? $author['short_id'] ?? $author['display_id'] ?? ''),
                'nickname' => (string)($author['nickname'] ?? ''),
                'avatar_url' => self::firstUrl($author['avatar_thumb'] ?? []),
            ],
            'tags' => array_map(
                fn($tag) => [
                    'tag_id' => (string)($tag['tag_id'] ?? ''),
                    'tag_name' => (string)($tag['tag_name'] ?? ''),
                    'level' => (int)($tag['level'] ?? 0),
                ],
                array_values(array_filter($tags, 'is_array'))
            ),
        ];
    }

    private static function firstUrl(mixed $image): string
    {
        if (!is_array($image)) {
            return '';
        }

        if (isset($image['url']) && is_string($image['url'])) {
            return $image['url'];
        }

        if (isset($image['url_list'][0]) && is_string($image['url_list'][0])) {
            return $image['url_list'][0];
        }

        return '';
    }
}
