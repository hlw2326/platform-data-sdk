<?php

namespace Hlw\Collect\Ks\Mini\User;

class ProfileResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function toUserInfo(): array
    {
        return is_array($this->raw) ? self::parse($this->raw) : [];
    }

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
            'eid' => $profile['kwaiId'] ?? $profile['eid'] ?? $profile['id'] ?? $profile['userId'] ?? '',
            'user_id' => (string)($profile['user_id'] ?? $profile['userId'] ?? $profile['id'] ?? ''),
            'name' => $profile['user_name'] ?? $profile['name'] ?? $profile['userName'] ?? $profile['profileUserName'] ?? '',
            'avatar_url' => $profile['headurl'] ?? $profile['avatar'] ?? $profile['headUrl'] ?? $profile['avatarUrl'] ?? '',
            'description' => $profile['user_text'] ?? $profile['description'] ?? $profile['profileText'] ?? '',
            'city' => $response['userProfile']['cityName'] ?? '',
            'gender' => $profile['user_sex'] ?? '',
            'fan_count' => $count['fan'] ?? $profile['fanCount'] ?? $profile['fansCount'] ?? 0,
            'follow_count' => $count['follow'] ?? $profile['followCount'] ?? $profile['followingCount'] ?? 0,
            'photo_count' => $count['photo'] ?? $profile['photoCount'] ?? $profile['photoNum'] ?? 0,
        ];
    }
}
