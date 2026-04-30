<?php

namespace Hlw\Collect\Types;

/**
 * @phpstan-type FeedItemArray array{
 *     platform: 'dy'|'ks',
 *     type: 'feed',
 *     item_id: string,
 *     desc: string,
 *     create_time: int,
 *     duration_ms: int,
 *     cover_url: string,
 *     video_url: string,
 *     share_url: string,
 *     width: int,
 *     height: int,
 *     is_top: bool,
 *     total: array{
 *         like_count: int,
 *         comment_count: int,
 *         share_count: int,
 *         collect_count: int,
 *         play_count: int
 *     },
 *     author: array{
 *         user_id: string,
 *         sec_user_id: string,
 *         display_id: string,
 *         nickname: string,
 *         avatar_url: string
 *     },
 *     tags: list<array{
 *         tag_id: string,
 *         tag_name: string,
 *         level: int
 *     }>
 * }
 * @psalm-type FeedItemArray = array{
 *     platform: 'dy'|'ks',
 *     type: 'feed',
 *     item_id: string,
 *     desc: string,
 *     create_time: int,
 *     duration_ms: int,
 *     cover_url: string,
 *     video_url: string,
 *     share_url: string,
 *     width: int,
 *     height: int,
 *     is_top: bool,
 *     total: array{
 *         like_count: int,
 *         comment_count: int,
 *         share_count: int,
 *         collect_count: int,
 *         play_count: int
 *     },
 *     author: array{
 *         user_id: string,
 *         sec_user_id: string,
 *         display_id: string,
 *         nickname: string,
 *         avatar_url: string
 *     },
 *     tags: list<array{
 *         tag_id: string,
 *         tag_name: string,
 *         level: int
 *     }>
 * }
 */
final class FeedItemType
{
    public const TYPE = 'feed';

    /**
     * @return array<string, string|array<string, string>>
     */
    public static function schema(): array
    {
        return [
            'platform' => 'string:dy|ks',
            'type' => 'string:feed',
            'item_id' => 'string',
            'desc' => 'string',
            'create_time' => 'int',
            'duration_ms' => 'int',
            'cover_url' => 'string',
            'video_url' => 'string',
            'share_url' => 'string',
            'width' => 'int',
            'height' => 'int',
            'is_top' => 'bool',
            'total' => [
                'like_count' => 'int',
                'comment_count' => 'int',
                'share_count' => 'int',
                'collect_count' => 'int',
                'play_count' => 'int',
            ],
            'author' => [
                'user_id' => 'string',
                'sec_user_id' => 'string',
                'display_id' => 'string',
                'nickname' => 'string',
                'avatar_url' => 'string',
            ],
            'tags' => 'list<array{tag_id:string,tag_name:string,level:int}>',
        ];
    }
}
