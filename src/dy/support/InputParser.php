<?php

namespace Hlw\Collect\Dy\Support;

use RuntimeException;

class InputParser
{
    public static function secUid(string $input, int $timeout = 8000): string
    {
        $trimmed = trim($input);
        if (preg_match('/^MS4wLjAB/', $trimmed)) {
            return $trimmed;
        }
        if (preg_match('/[?&]sec_uid=([^&\s]+)/', $trimmed, $match)) {
            return urldecode($match[1]);
        }
        if (preg_match('/\/(?:share\/)?user\/([A-Za-z0-9_\-]+)/', $trimmed, $match)) {
            return $match[1];
        }
        if (preg_match('/https?:\/\/v\.douyin\.com\/[A-Za-z0-9_\-]+\/?/', $trimmed, $match)) {
            return self::secUid(Redirect::follow($match[0], $timeout), $timeout);
        }
        throw new RuntimeException('无法从输入中识别抖音用户标识: ' . substr($input, 0, 50));
    }

    public static function awemeId(string $input, int $timeout = 8000): string
    {
        $trimmed = trim($input);
        if (preg_match('/^\d+$/', $trimmed)) {
            return $trimmed;
        }
        if (preg_match('/\/(?:video|note)\/(\d+)/', $trimmed, $match)) {
            return $match[1];
        }
        if (preg_match('/https?:\/\/v\.douyin\.com\/[A-Za-z0-9_\-]+\/?/', $trimmed, $match)) {
            return self::awemeId(Redirect::follow($match[0], $timeout), $timeout);
        }
        throw new RuntimeException('无法从输入中识别抖音作品标识: ' . substr($trimmed, 0, 50));
    }
}
