<?php

require __DIR__ . '/../vendor/autoload.php';

use Hlw\Collect\Ks;

$input = 'https://v.kuaishou.com/KRaWlxRy 看了这么多快手，还是「婷妹儿666666」最好玩了！ 复制此消息，打开【快手】直接观看！';

function loadKuaishouMiniCookie(): string
{
    $source = __DIR__ . '/cookies_ks_mini.txt';
    $content = is_file($source) ? trim((string)file_get_contents($source)) : '';
    if ($content === '') {
        throw new RuntimeException('无法读取快手小程序 Cookie: ' . $source);
    }

    return $content;
}

function cookieValue(string $cookies, string $key): string
{
    foreach (explode(';', $cookies) as $part) {
        $part = trim($part);
        if (str_starts_with($part, $key . '=')) {
            return urldecode(substr($part, strlen($key) + 1));
        }
    }

    return '';
}

try {
    $cookies = loadKuaishouMiniCookie();
    $did = getenv('KS_MINI_DID') ?: cookieValue($cookies, 'did');

    $ksV1 = Ks::Mini([
        'cookies' => $cookies,
        'did' => $did,
        'timeout' => 15000,
    ])->v1();

    $profileResponse = $ksV1->user->info($input);
    $feedResponse = $ksV1->feed->list($input, ['count' => 6]);

    $userInfo = $profileResponse->toUserInfo();
    $feeds = $feedResponse->toArray();

    echo json_encode([
        'user_info' => $userInfo,
        'feed_list' => $feeds,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
