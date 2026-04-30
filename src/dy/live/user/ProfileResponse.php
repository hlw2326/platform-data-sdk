<?php

namespace Hlw\Collect\Dy\Live\User;

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
    public static function parse(array $res): array
    {
        $profile = $res['data']['user_profile'] ?? [];
        if (!is_array($profile)) {
            return [];
        }

        $base = $res['data']['user_profile']['base_info'] ?? [];
        $follow = $res['data']['user_profile']['follow_info'] ?? [];
        $auth = $res['data']['user_profile']['auth_info'] ?? [];
        if (!is_array($base)) {
            $base = [];
        }
        if (!is_array($follow)) {
            $follow = [];
        }
        if (!is_array($auth)) {
            $auth = [];
        }

        return [
            'platform' => 'dy',
            'type' => 'user',
            'user_id' => (string)($base['id_str'] ?? $base['id'] ?? ''),
            'sec_user_id' => (string)($base['sec_uid'] ?? ''),
            'display_id' => (string)($base['display_id'] ?? ''),
            'nickname' => (string)($base['nickname'] ?? ''),
            'signature' => (string)($base['signature'] ?? ''),
            'avatar_url' => self::firstUrl($base['avatar_thumb'] ?? []) ?: self::firstUrl($base['avatar_medium'] ?? []),
            'gender' => self::normalizeGender($base['gender'] ?? 0),
            'city' => (string)($base['city'] ?? $base['location_city'] ?? ''),
            'total' => [
                'follower_count' => (int)($follow['follower_count'] ?? 0),
                'following_count' => (int)($follow['following_count'] ?? 0),
                'feed_count' => (int)($profile['aweme_count'] ?? $base['aweme_count'] ?? 0),
                'liked_count' => (int)($profile['total_favorited'] ?? $base['total_favorited'] ?? 0),
            ],
            'verified' => ($auth['verify_content'] ?? $base['custom_verify'] ?? $base['enterprise_verify_reason'] ?? '') !== '',
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

    private static function normalizeGender(mixed $gender): int
    {
        return match ((string)$gender) {
            '1', 'M', 'm', 'male' => 1,
            default => 0,
        };
    }
}
