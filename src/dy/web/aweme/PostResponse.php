<?php

namespace Hlw\Collect\Dy\Web\Aweme;

class PostResponse
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

        return array_map(fn($aweme) => self::parseAweme($aweme), $this->raw['aweme_list'] ?? []);
    }

    public static function parseAweme(array $aweme): array
    {
        $statistics = $aweme['statistics'] ?? [];

        return [
            'aweme_id' => $aweme['aweme_id'] ?? '',
            'desc' => $aweme['desc'] ?? '',
            'cover' => $aweme['video']['cover']['url_list'][0] ?? '',
            'like_count' => $statistics['digg_count'] ?? 0,
            'comment_count' => $statistics['comment_count'] ?? 0,
            'share_count' => $statistics['share_count'] ?? 0,
            'collect_count' => $statistics['collect_count'] ?? 0,
            'play_count' => $statistics['play_count'] ?? 0,
            'recommend_count' => $statistics['recommend_count'] ?? 0,
            'admire_count' => $statistics['admire_count'] ?? 0,
            'tags' => array_map(fn($tag) => ['tag_id' => $tag['tag_id'] ?? '', 'tag_name' => $tag['tag_name'] ?? '', 'level' => $tag['level'] ?? 0], $aweme['video_tag'] ?? []),
            'share_url' => $aweme['share_url'] ?? '',
            'duration' => $aweme['duration'] ?? 0,
            'is_top' => ($aweme['is_top'] ?? 0) === 1,
            'create_time' => $aweme['create_time'] ?? 0,
        ];
    }
}
