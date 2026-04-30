<?php

namespace Hlw\Collect\Dy\Support;

use RuntimeException;

class Redirect
{
    public static function follow(string $url, int $timeout = 8000, int $maxRedirects = 5): string
    {
        for ($i = 0; $i < $maxRedirects; $i++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_HEADER => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT_MS => $timeout,
                CURLOPT_HTTPHEADER => ['User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148'],
            ]);
            $headers = curl_exec($ch);
            if ($headers === false && in_array(curl_errno($ch), [60, 77], true)) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                $headers = curl_exec($ch);
            }
            curl_close($ch);

            if (is_string($headers) && preg_match('/^Location:\s*(.+)$/mi', $headers, $match)) {
                $url = self::resolveUrl($url, trim($match[1]));
                continue;
            }

            return $url;
        }

        throw new RuntimeException('重定向次数过多');
    }

    private static function resolveUrl(string $base, string $location): string
    {
        if (str_starts_with($location, 'http')) {
            return $location;
        }
        if (str_starts_with($location, '//')) {
            return (parse_url($base, PHP_URL_SCHEME) ?: 'https') . ':' . $location;
        }
        if (str_starts_with($location, '/')) {
            return (parse_url($base, PHP_URL_SCHEME) ?: 'https') . '://' . parse_url($base, PHP_URL_HOST) . $location;
        }
        return $location;
    }
}
