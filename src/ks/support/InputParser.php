<?php

namespace Hlw\Collect\Ks\Support;

use RuntimeException;

class InputParser
{
    public static function eid(string $input, int $timeout = 8000): string
    {
        $url = self::extractUrl($input) ?? $input;
        $eid = self::parseEid($url);
        if ($eid !== null) {
            return $eid;
        }

        if (str_contains($url, 'v.kuaishou.com') || $url !== $input) {
            $eid = self::parseEid(Redirect::follow($url, $timeout));
            if ($eid !== null) {
                return $eid;
            }
        }

        throw new RuntimeException('无法从输入中识别快手用户标识: ' . substr($input, 0, 50));
    }

    public static function parseEid(string $input): ?string
    {
        $trimmed = trim($input);
        if (preg_match('/^[a-zA-Z0-9_\-]+$/', $trimmed)) {
            return $trimmed;
        }
        if (preg_match('/kuaishou\.com\/(?:profile|u|fw\/user)\/([a-zA-Z0-9_\-]+)/', $trimmed, $match)) {
            return $match[1];
        }
        if (preg_match('/[?&](?:eid|userId|fid|shareObjectId)=([a-zA-Z0-9_\-]+)/', $trimmed, $match)) {
            return $match[1];
        }
        return null;
    }

    private static function extractUrl(string $input): ?string
    {
        return preg_match('/https?:\/\/[^\s]+/', $input, $match) ? $match[0] : null;
    }
}
