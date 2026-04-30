<?php

namespace Hlw\Collect\Dy\Support;

/**
 * @phpstan-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 * @psalm-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 */
class UserInfo
{
    /**
     * @return UserInfoArray|null
     */
    public static function parse(string $html): ?array
    {
        if (preg_match_all('/self\.__pace_f\.push\(\[\d+,\s*"(\d+:\[.{20,})"\]\)/sU', $html, $matches)) {
            foreach ($matches[1] as $match) {
                if (!str_contains($match, 'nickname')) {
                    continue;
                }
                $raw = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn($m) => html_entity_decode('&#x' . $m[1] . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8'), $match);
                $raw = str_replace(['\\"', '\\\\'], ['"', '\\'], $raw);
                $userIdx = strpos($raw, '"userInfo":{');
                if ($userIdx === false) {
                    continue;
                }
                $start = strpos($raw, '{', $userIdx + 10);
                $json = self::sliceJsonObject($raw, $start);
                $data = $json !== null ? json_decode($json, true) : null;
                if (is_array($data) && isset($data['nickname'])) {
                    return self::normalize($data);
                }
            }
        }

        if (preg_match_all('/self\.__pace_f\.push\(\[\d+,\s*"([^"]{100,})"\]/sU', $html, $matches)) {
            foreach ($matches[1] as $match) {
                if (!str_contains($match, 'nickname')) {
                    continue;
                }
                $raw = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', fn($m) => html_entity_decode('&#x' . $m[1] . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8'), $match);
                $decoded = urldecode($raw);
                $idx = strpos($decoded, '"nickname"');
                if ($idx === false) {
                    continue;
                }
                $start = $idx;
                while ($start > 0 && $decoded[$start] !== '{') {
                    $start--;
                }
                $json = self::sliceJsonObject($decoded, $start);
                $data = $json !== null ? json_decode($json, true) : null;
                if (is_array($data) && isset($data['nickname'])) {
                    return self::normalize($data);
                }
            }
        }

        if (preg_match('/"userInfoRes"\s*:\s*(\{[\s\S]*?\})\s*,\s*"secUid"/', $html, $match)) {
            $data = json_decode(str_replace('u002F', '/', $match[1]), true);
            $user = $data['user_info'] ?? null;
            if (is_array($user) && isset($user['nickname'])) {
                return self::normalize([
                    'sec_uid' => $user['sec_uid'] ?? '',
                    'uid' => $user['uid'] ?? '',
                    'unique_id' => $user['unique_id'] ?? '',
                    'short_id' => $user['short_id'] ?? '',
                    'nickname' => $user['nickname'] ?? '',
                    'gender' => $user['gender'] ?? 0,
                    'desc' => $user['signature'] ?? '',
                    'avatar_url' => $user['avatar_thumb']['url_list'][0] ?? '',
                    'city' => $user['city'] ?? '',
                    'mplatform_followers_count' => $user['mplatform_followers_count'] ?? 0,
                    'following_count' => $user['following_count'] ?? 0,
                    'aweme_count' => $user['aweme_count'] ?? 0,
                    'total_favorited' => $user['total_favorited'] ?? 0,
                    'account_cert_info' => $user['account_cert_info'] ?? null,
                ]);
            }
        }

        return null;
    }

    private static function sliceJsonObject(string $raw, int|false $start): ?string
    {
        if ($start === false) {
            return null;
        }
        $depth = 0;
        $length = strlen($raw);
        for ($end = $start; $end < $length; $end++) {
            if ($raw[$end] === '{') {
                $depth++;
            } elseif ($raw[$end] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($raw, $start, $end - $start + 1);
                }
            }
        }

        return null;
    }

    /**
     * @return UserInfoArray
     */
    private static function normalize(array $obj): array
    {
        $certLabel = '';
        $cert = $obj['account_cert_info'] ?? null;
        if (is_string($cert)) {
            $cert = json_decode($cert, true);
        }
        if (is_array($cert)) {
            $certLabel = $cert['label_text'] ?? '';
        }

        return [
            'platform' => 'dy',
            'type' => 'user',
            'user_id' => (string)($obj['uid'] ?? $obj['id_str'] ?? $obj['id'] ?? ''),
            'sec_user_id' => (string)($obj['secUid'] ?? $obj['sec_uid'] ?? ''),
            'display_id' => (string)($obj['uniqueId'] ?? $obj['unique_id'] ?? $obj['shortId'] ?? $obj['short_id'] ?? $obj['display_id'] ?? ''),
            'nickname' => (string)($obj['nickname'] ?? ''),
            'signature' => (string)($obj['desc'] ?? $obj['signature'] ?? ''),
            'avatar_url' => (string)($obj['avatar_url'] ?? $obj['avatarUrl'] ?? $obj['avatar_thumb']['url_list'][0] ?? ''),
            'gender' => self::normalizeGender($obj['gender'] ?? 0),
            'city' => (string)($obj['city'] ?? ''),
            'total' => [
                'follower_count' => (int)($obj['mplatform_followers_count'] ?? $obj['followerCount'] ?? $obj['follower_count'] ?? 0),
                'following_count' => (int)($obj['followingCount'] ?? $obj['following_count'] ?? 0),
                'feed_count' => (int)($obj['awemeCount'] ?? $obj['aweme_count'] ?? 0),
                'liked_count' => (int)($obj['total_favorited'] ?? $obj['totalFavorited'] ?? 0),
            ],
            'verified' => $certLabel !== '',
        ];
    }

    private static function normalizeGender(mixed $gender): int
    {
        return match ((string)$gender) {
            '1', 'M', 'm', 'male' => 1,
            default => 0,
        };
    }
}
