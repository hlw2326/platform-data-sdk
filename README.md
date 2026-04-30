# Platform Data SDK

`hlw2326/platform-data-sdk` 是一个面向内容平台的数据解析 SDK，用于把抖音、快手等平台接口返回整理成结构化数组，方便后续入库、缓存或业务分析。

当前 PHP 版本主要支持：

- 抖音 H5 / Live / Web 用户信息与作品列表解析
- 快手小程序用户信息、主页作品、评论、推荐流接口
- 分享链接解析、短链跳转、签名参数生成
- 统一的 `toUserInfo()` / `toArray()` 输出格式

小红书、视频号等平台接口可以按当前目录风格继续扩展。

## 环境要求

- PHP >= 8.1
- ext-curl
- Composer

## 安装

如果包已发布到 Composer：

```bash
composer require hlw2326/platform-data-sdk
```

本地开发时，在项目根目录执行：

```bash
composer install
```

## 基础用法

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Hlw\Collect\Dy;
use Hlw\Collect\Ks;
```

## 抖音

抖音接口通常需要 Web Cookie。建议把 Cookie 放到本地文件中，不要提交到 Git。

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Hlw\Collect\Dy;

$input = '抖音分享文本或用户主页链接';
$cookies = trim(file_get_contents(__DIR__ . '/tests/cookies_dy_web.text'));

$dyLiveV1 = Dy::Live($cookies)->v1();
$dyWebV1 = Dy::Web($cookies)->v1();

$userInfo = $dyLiveV1->user->profile($input)->toUserInfo();
$feedList = $dyWebV1->aweme->post($input, ['count' => 18])->toArray();

print_r([
    'user' => $userInfo,
    'feed_list' => $feedList,
]);
```

常用入口：

- `Dy::H5($options)->v1()->user->info($input)`
- `Dy::Live($options)->v1()->user->profile($input)`
- `Dy::Web($options)->v1()->user->info($input)`
- `Dy::Web($options)->v1()->aweme->post($input, ['count' => 18])`
- `Dy::aBogus($params, $ua)`

## 快手小程序

快手小程序接口需要 Cookie，`did` 可以显式传入，也可以从 Cookie 中自动读取。

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Hlw\Collect\Ks;

$input = '快手分享文本或用户主页链接';
$cookies = trim(file_get_contents(__DIR__ . '/tests/cookies_ks_mini.txt'));

$ksV1 = Ks::Mini([
    'cookies' => $cookies,
    'timeout' => 15000,
])->v1();

$userInfo = $ksV1->user->info($input)->toUserInfo();
$feedList = $ksV1->feed->list($input, ['count' => 12])->toArray();

print_r([
    'user_info' => $userInfo,
    'feed_list' => $feedList,
]);
```

常用入口：

- `Ks::Mini($options)->v1()->user->info($input)`
- `Ks::Mini($options)->v1()->user->infoByEid($eid)`
- `Ks::Mini($options)->v1()->feed->list($input, ['count' => 12])`
- `Ks::Mini($options)->v1()->feed->listByEid($eid, ['count' => 12])`
- `Ks::Mini($options)->v1()->photo->commentList($photoId, ['count' => 20])`
- `Ks::Mini($options)->v1()->feed->recommend(['count' => 10])`
- `Ks::sig3($input)`

## 输出约定

SDK 对外归一化输出使用下划线字段，适合直接落库，例如：

```php
[
    'user_id' => '123456',
    'avatar_url' => 'https://...',
    'fan_count' => 100,
    'feed_list' => [
        [
            'id' => '作品ID',
            'title' => '作品标题',
            'cover' => 'https://...',
            'video_url' => 'https://...',
            'like_count' => 10,
            'comment_count' => 2,
            'create_time' => 1700000000,
        ],
    ],
]
```

## Cookie 文件

调试脚本默认读取：

- `tests/cookies_dy_web.text`
- `tests/cookies_ks_mini.txt`

这些文件已被 `.gitignore` 忽略。请只放在本地，不要提交到仓库。

## 调试脚本

```bash
php tests/smoke.php
php tests/debug_douyin_user.php
php tests/debug_kuaishou_mini.php
```

`smoke.php` 不依赖真实 Cookie，适合快速检查 SDK 基础功能。两个 `debug_*` 脚本会请求真实平台接口，需要提前准备 Cookie。

## 目录结构

```text
src/
  Dy.php
  Ks.php
  dy/
    h5/
    live/
    web/
    support/
  ks/
    mini/
    support/
  request/
tests/
  smoke.php
  debug_douyin_user.php
  debug_kuaishou_mini.php
```

## 安全说明

- 不要把 Cookie、token、did 等真实账号信息写进源码
- 不要提交 `vendor/`、本地 Cookie 文件或临时调试文件
- 真实接口可能受到平台风控、登录状态、Cookie 过期等影响

## License

MIT
