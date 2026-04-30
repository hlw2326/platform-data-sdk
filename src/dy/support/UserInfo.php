<?php

namespace Hlw\Collect\Dy\Support;

class UserInfo
{
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
            'sec_uid' => $obj['secUid'] ?? $obj['sec_uid'] ?? '',
            'uid' => $obj['uid'] ?? '',
            'display_id' => $obj['uniqueId'] ?? $obj['unique_id'] ?? $obj['shortId'] ?? $obj['short_id'] ?? '',
            'nickname' => $obj['nickname'] ?? '',
            'gender' => $obj['gender'] ?? 0,
            'signature' => $obj['desc'] ?? $obj['signature'] ?? '',
            'city' => $obj['city'] ?? '',
            'avatar_url' => $obj['avatar_url'] ?? $obj['avatarUrl'] ?? $obj['avatar_thumb']['url_list'][0] ?? '',
            'fan_count' => $obj['mplatform_followers_count'] ?? $obj['followerCount'] ?? $obj['follower_count'] ?? 0,
            'follow_count' => $obj['followingCount'] ?? $obj['following_count'] ?? 0,
            'photo_count' => $obj['awemeCount'] ?? $obj['aweme_count'] ?? 0,
            'like_count' => (int)($obj['total_favorited'] ?? $obj['totalFavorited'] ?? 0),
            'cert_label' => $certLabel,
        ];
    }
}
