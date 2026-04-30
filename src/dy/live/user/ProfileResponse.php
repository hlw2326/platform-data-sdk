<?php

namespace Hlw\Collect\Dy\Live\User;

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

    public static function parse(array $res): array
    {
        $base = $res['data']['user_profile']['base_info'] ?? [];
        $follow = $res['data']['user_profile']['follow_info'] ?? [];
        $gradeText = $res['data']['user_profile']['basic_area']['grade_icon']['content']['alternative_text'] ?? '';
        preg_match('/\d+/', $gradeText, $gradeMatch);

        return [
            'sec_uid' => $base['sec_uid'] ?? '',
            'uid' => $base['id_str'] ?? '',
            'display_id' => $base['display_id'] ?? '',
            'nickname' => $base['nickname'] ?? '',
            'gender' => $base['gender'] ?? 0,
            'signature' => $base['signature'] ?? '',
            'city' => $base['city'] ?? '',
            'avatar_url' => $base['avatar_thumb']['url_list'][0] ?? '',
            'fan_count' => $follow['follower_count'] ?? 0,
            'follow_count' => $follow['following_count'] ?? 0,
            'photo_count' => 0,
            'like_count' => 0,
            'grade_level' => isset($gradeMatch[0]) ? (int)$gradeMatch[0] : 0,
        ];
    }
}
