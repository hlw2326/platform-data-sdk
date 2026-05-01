<?php

namespace Hlw\Collect\Types;

use JsonSerializable;

/**
 * @phpstan-type FeedItemArray array{
 *     platform: 'dy'|'ks',
 *     type: 'feed',
 *     item_id: string,
 *     desc: string,
 *     create_time: int,
 *     duration: int,
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
 *     duration: int,
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
final class FeedItemType implements JsonSerializable
{
    public const TYPE = 'feed';

    public readonly string $platform;
    public readonly string $type;
    public readonly string $item_id;
    public readonly string $desc;
    public readonly int $create_time;
    public readonly int $duration;
    public readonly string $cover_url;
    public readonly string $video_url;
    public readonly string $share_url;
    public readonly int $width;
    public readonly int $height;
    public readonly bool $is_top;
    /** @var array{like_count:int, comment_count:int, share_count:int, collect_count:int, play_count:int} */
    public readonly array $total;
    /** @var array{user_id:string, sec_user_id:string, display_id:string, nickname:string, avatar_url:string} */
    public readonly array $author;
    /** @var list<array{tag_id:string, tag_name:string, level:int}> */
    public readonly array $tags;

    /** @var FeedItemArray */
    private array $data;

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
            'duration' => 'int',
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

    /**
     * @param FeedItemArray $data
     */
    public function __construct(array $data)
    {
        $this->platform = $data['platform'];
        $this->type = $data['type'];
        $this->item_id = $data['item_id'];
        $this->desc = $data['desc'];
        $this->create_time = $data['create_time'];
        $this->duration = $data['duration'];
        $this->cover_url = $data['cover_url'];
        $this->video_url = $data['video_url'];
        $this->share_url = $data['share_url'];
        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->is_top = $data['is_top'];
        $this->total = $data['total'];
        $this->author = $data['author'];
        $this->tags = $data['tags'];
        $this->data = $data;
    }

    /**
     * @param FeedItemArray $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return FeedItemArray
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return FeedItemArray
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
