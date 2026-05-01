<?php

namespace Hlw\Collect\Dy\Support;

/**
 * @phpstan-import-type UserInfoArray from \Hlw\Collect\Types\UserInfoType
 * @psalm-import-type UserInfoArray from \Hlw\Collect\Types\UserInfoType
 */
class UserInfo
{
    /**
     * @return UserInfoArray|null
     */
    public static function parse(string $html): ?array
    {
        return self::parsePaceUserInfoPush($html)
            ?? self::parsePaceNicknamePush($html)
            ?? self::parseUserInfoRes($html);
    }

    /**
     * @return UserInfoArray|null
     */
    private static function parsePaceUserInfoPush(string $html): ?array
    {
        if (!preg_match_all('/self\.__pace_f\.push\(\[\d+,\s*"(\d+:\[.{20,})"\]\)/sU', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $match) {
            if (!str_contains($match, 'nickname')) {
                continue;
            }

            $raw = self::decodeUnicodeEscapes($match);
            $raw = str_replace(['\\"', '\\\\'], ['"', '\\'], $raw);
            $userIdx = strpos($raw, '"userInfo":{');
            if ($userIdx === false) {
                continue;
            }

            $data = self::decodeJsonObjectAt($raw, strpos($raw, '{', $userIdx + 10));
            if (is_array($data) && isset($data['nickname'])) {
                return self::normalize($data);
            }
        }

        return null;
    }

    /**
     * @return UserInfoArray|null
     */
    private static function parsePaceNicknamePush(string $html): ?array
    {
        if (!preg_match_all('/self\.__pace_f\.push\(\[\d+,\s*"([^"]{100,})"\]/sU', $html, $matches)) {
            return null;
        }

        foreach ($matches[1] as $match) {
            if (!str_contains($match, 'nickname')) {
                continue;
            }

            $decoded = urldecode(self::decodeUnicodeEscapes($match));
            $idx = strpos($decoded, '"nickname"');
            if ($idx === false) {
                continue;
            }

            $data = self::decodeJsonObjectAt($decoded, self::findObjectStart($decoded, $idx));
            if (is_array($data) && isset($data['nickname'])) {
                return self::normalize($data);
            }
        }

        return null;
    }

    /**
     * @return UserInfoArray|null
     */
    private static function parseUserInfoRes(string $html): ?array
    {
        if (!preg_match('/"userInfoRes"\s*:\s*(\{[\s\S]*?\})\s*,\s*"secUid"/', $html, $match)) {
            return null;
        }

        $data = json_decode(str_replace('u002F', '/', $match[1]), true);
        $user = $data['user_info'] ?? null;
        if (!is_array($user) || !isset($user['nickname'])) {
            return null;
        }

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

    private static function decodeUnicodeEscapes(string $raw): string
    {
        $decoded = preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            fn($match) => html_entity_decode('&#x' . $match[1] . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $raw
        );

        return $decoded ?? $raw;
    }

    private static function decodeJsonObjectAt(string $raw, int|false $start): ?array
    {
        $json = self::sliceJsonObject($raw, $start);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    private static function findObjectStart(string $raw, int $from): int|false
    {
        for ($start = $from; $start >= 0; $start--) {
            if ($raw[$start] === '{') {
                return $start;
            }
        }

        return false;
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
                'fan_count' => (int)($obj['mplatform_followers_count'] ?? $obj['followerCount'] ?? $obj['follower_count'] ?? $obj['fan_count'] ?? 0),
                'follow_count' => (int)($obj['followingCount'] ?? $obj['following_count'] ?? $obj['follow_count'] ?? 0),
                'work_count' => (int)($obj['awemeCount'] ?? $obj['aweme_count'] ?? $obj['work_count'] ?? 0),
                'like_count' => (int)($obj['total_favorited'] ?? $obj['totalFavorited'] ?? $obj['like_count'] ?? 0),
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
