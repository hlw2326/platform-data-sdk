<?php

require __DIR__ . '/../vendor/autoload.php';

use Hlw\Collect\Dy;

$input = '2- 长按复制此条消息，打开抖音搜索，查看TA的更多作品。 https://v.douyin.com/56rT_0ku-4c/ 5@8.com :1pm';
$cookieFile = __DIR__ . '/cookies_dy_web.text';
$cookies = is_file($cookieFile) ? trim(file_get_contents($cookieFile)) : '';

if ($cookies === '') {
    fwrite(STDERR, "请先在 php/tests/cookies_dy_web.text 写入 Cookie。\n");
    exit(1);
}

try {
    $dyLiveV1 = Dy::Live($cookies)->v1();
    $dyWebV1 = Dy::Web($cookies)->v1();

    $info = $dyLiveV1->user->profile($input)->toArray();
    $awemeResponse = $dyWebV1->aweme->post($input);
    $aweme = $awemeResponse->raw();
    $awemeList = $awemeResponse->toArray();

    file_put_contents(__DIR__ . '/dy.json', json_encode([
        'platform' => 'dy',
        'user_info' => $info,
        'feed_count' => count($aweme['aweme_list'] ?? []),
        'feed_list' => array_slice($awemeList, 0, 5),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL);
    
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
