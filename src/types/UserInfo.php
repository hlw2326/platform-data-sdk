<?php

namespace Hlw\Collect\Types;

use JsonSerializable;

/**
 * @phpstan-type UserInfoArray array{
 *     platform: 'dy'|'ks',
 *     type: 'user',
 *     user_id: string,
 *     sec_user_id: string,
 *     display_id: string,
 *     nickname: string,
 *     signature: string,
 *     avatar_url: string,
 *     gender: 0|1,
 *     city: string,
 *     total: array{
 *         follower_count: int,
 *         following_count: int,
 *         feed_count: int,
 *         liked_count: int
 *     },
 *     verified: bool
 * }
 * @psalm-type UserInfoArray = array{
 *     platform: 'dy'|'ks',
 *     type: 'user',
 *     user_id: string,
 *     sec_user_id: string,
 *     display_id: string,
 *     nickname: string,
 *     signature: string,
 *     avatar_url: string,
 *     gender: 0|1,
 *     city: string,
 *     total: array{
 *         follower_count: int,
 *         following_count: int,
 *         feed_count: int,
 *         liked_count: int
 *     },
 *     verified: bool
 * }
 */
final class UserInfo implements JsonSerializable
{
    public const TYPE = 'user';

    public readonly string $platform;
    public readonly string $type;
    public readonly string $user_id;
    public readonly string $sec_user_id;
    public readonly string $display_id;
    public readonly string $nickname;
    public readonly string $signature;
    public readonly string $avatar_url;
    public readonly int $gender;
    public readonly string $city;
    /** @var array{follower_count:int, following_count:int, feed_count:int, liked_count:int} */
    public readonly array $total;
    public readonly bool $verified;

    /** @var UserInfoArray */
    private array $data;

    /**
     * @return array<string, string|array<string, string>>
     */
    public static function schema(): array
    {
        return [
            'platform' => 'string:dy|ks',
            'type' => 'string:user',
            'user_id' => 'string',
            'sec_user_id' => 'string',
            'display_id' => 'string',
            'nickname' => 'string',
            'signature' => 'string',
            'avatar_url' => 'string',
            'gender' => 'int:0|1',
            'city' => 'string',
            'total' => [
                'follower_count' => 'int',
                'following_count' => 'int',
                'feed_count' => 'int',
                'liked_count' => 'int',
            ],
            'verified' => 'bool',
        ];
    }

    /**
     * @param UserInfoArray $data
     */
    public function __construct(array $data)
    {
        $this->platform = $data['platform'];
        $this->type = $data['type'];
        $this->user_id = $data['user_id'];
        $this->sec_user_id = $data['sec_user_id'];
        $this->display_id = $data['display_id'];
        $this->nickname = $data['nickname'];
        $this->signature = $data['signature'];
        $this->avatar_url = $data['avatar_url'];
        $this->gender = $data['gender'];
        $this->city = $data['city'];
        $this->total = $data['total'];
        $this->verified = $data['verified'];
        $this->data = $data;
    }

    /**
     * @param UserInfoArray $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return UserInfoArray
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return UserInfoArray
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
