<?php

namespace Hlw\Collect\Ks\Mini\User;

use Hlw\Collect\Types\UserInfo;

/**
 * @phpstan-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 * @psalm-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 */
class ProfileResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    /**
     * @return UserInfoArray|array{}
     */
    public function toArray(): array
    {
        return is_array($this->raw) ? self::parse($this->raw) : [];
    }

    public function toUserInfo(): ?UserInfo
    {
        $data = $this->toArray();
        return $data === [] ? null : UserInfo::fromArray($data);
    }

    /**
     * @return UserInfoArray|array{}
     */
    public static function parse(array $response): array
    {
        $profile = $response['userProfile']['profile'] ?? $response['user'] ?? $response['profile'] ?? $response['owner'] ?? $response['data']['user'] ?? [];
        if (!is_array($profile)) {
            return [];
        }

        $count = $response['userProfile']['ownerCount'] ?? [];
        if (!is_array($count)) {
            $count = [];
        }

        return [
            'platform' => 'ks',
            'type' => 'user',
            'user_id' => (string)($profile['user_id'] ?? $profile['userId'] ?? $profile['id'] ?? $response['shareInfo']['webShareVerifyData']['objectId'] ?? ''),
            'sec_user_id' => (string)($profile['userEid'] ?? $profile['eid'] ?? $profile['kwaiId'] ?? ''),
            'display_id' => (string)($profile['kwaiId'] ?? $profile['displayId'] ?? $profile['user_name'] ?? ''),
            'nickname' => (string)($profile['user_name'] ?? $profile['name'] ?? $profile['userName'] ?? $profile['profileUserName'] ?? ''),
            'signature' => (string)($profile['user_text'] ?? $profile['description'] ?? $profile['profileText'] ?? ''),
            'avatar_url' => (string)($profile['headurl'] ?? $profile['avatar'] ?? $profile['headUrl'] ?? $profile['avatarUrl'] ?? self::firstUrl($profile['headurls'] ?? [])),
            'gender' => self::normalizeGender($profile['user_sex'] ?? $profile['gender'] ?? ''),
            'city' => (string)($response['userProfile']['cityName'] ?? $profile['cityName'] ?? $profile['city'] ?? ''),
            'total' => [
                'follower_count' => (int)($count['fan'] ?? $profile['fanCount'] ?? $profile['fansCount'] ?? 0),
                'following_count' => (int)($count['follow'] ?? $profile['followCount'] ?? $profile['followingCount'] ?? 0),
                'feed_count' => (int)($count['photo'] ?? $count['photo_public'] ?? $profile['photoCount'] ?? $profile['photoNum'] ?? 0),
                'liked_count' => (int)($count['like'] ?? $profile['likeCount'] ?? $profile['likedCount'] ?? 0),
            ],
            'verified' => (bool)($profile['verified'] ?? $response['userProfile']['verified'] ?? false),
        ];
    }

    private static function firstUrl(mixed $urls): string
    {
        if (!is_array($urls)) {
            return '';
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

    private static function normalizeGender(mixed $gender): int
    {
        return match ((string)$gender) {
            'M', 'm', '1', 'male' => 1,
            default => 0,
        };
    }
}
