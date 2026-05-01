<?php

require __DIR__ . '/../src/Dy.php';
require __DIR__ . '/../src/Ks.php';
require __DIR__ . '/../src/request/BaseRequest.php';
require __DIR__ . '/../src/support/Redirect.php';
require __DIR__ . '/../src/dy/support/Options.php';
require __DIR__ . '/../src/dy/support/Redirect.php';
require __DIR__ . '/../src/dy/support/InputParser.php';
require __DIR__ . '/../src/dy/support/UserInfo.php';
require __DIR__ . '/../src/dy/h5/Request.php';
require __DIR__ . '/../src/dy/h5/H5.php';
require __DIR__ . '/../src/dy/h5/user/ProfileResponse.php';
require __DIR__ . '/../src/dy/h5/user/Profile.php';
require __DIR__ . '/../src/dy/h5/V1.php';
require __DIR__ . '/../src/dy/live/Request.php';
require __DIR__ . '/../src/dy/live/Live.php';
require __DIR__ . '/../src/dy/live/user/ProfileResponse.php';
require __DIR__ . '/../src/dy/live/user/Profile.php';
require __DIR__ . '/../src/dy/live/V1.php';
require __DIR__ . '/../src/dy/web/Request.php';
require __DIR__ . '/../src/dy/web/Web.php';
require __DIR__ . '/../src/dy/web/support/WebParams.php';
require __DIR__ . '/../src/dy/web/user/ProfileResponse.php';
require __DIR__ . '/../src/dy/web/user/Profile.php';
require __DIR__ . '/../src/dy/web/aweme/PostResponse.php';
require __DIR__ . '/../src/dy/web/aweme/Aweme.php';
require __DIR__ . '/../src/dy/web/signature/ABogus.php';
require __DIR__ . '/../src/dy/web/V1.php';
require __DIR__ . '/../src/ks/support/Options.php';
require __DIR__ . '/../src/ks/support/Redirect.php';
require __DIR__ . '/../src/ks/support/InputParser.php';
require __DIR__ . '/../src/ks/mini/Request.php';
require __DIR__ . '/../src/ks/mini/Sig3.php';
require __DIR__ . '/../src/ks/mini/Mini.php';
require __DIR__ . '/../src/ks/mini/Endpoint.php';
require __DIR__ . '/../src/ks/mini/feed/ListResponse.php';
require __DIR__ . '/../src/ks/mini/feed/Feed.php';
require __DIR__ . '/../src/ks/mini/photo/Photo.php';
require __DIR__ . '/../src/ks/mini/user/ProfileResponse.php';
require __DIR__ . '/../src/ks/mini/user/User.php';
require __DIR__ . '/../src/ks/mini/V1.php';

use Hlw\Collect\Dy;
use Hlw\Collect\Ks;
use Hlw\Collect\Dy\Web\Signature\ABogus;
use Hlw\Collect\Ks\Mini\Sig3;
use Hlw\Collect\Ks\Mini\V1 as KsMiniV1;

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assert_no_camel_keys(array $value, string $message): void
{
    foreach ($value as $key => $item) {
        if (is_string($key) && preg_match('/[a-z][A-Z]/', $key)) {
            throw new RuntimeException($message . ': ' . $key);
        }
        if (is_array($item)) {
            assert_no_camel_keys($item, $message);
        }
    }
}

function assert_keys(array $value, array $expected, string $message): void
{
    $keys = array_keys($value);
    if ($keys !== $expected) {
        throw new RuntimeException($message . ': ' . json_encode($keys, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

function assert_same_keys(array $left, array $right, string $message): void
{
    $leftKeys = array_keys($left);
    $rightKeys = array_keys($right);
    if ($leftKeys !== $rightKeys) {
        throw new RuntimeException($message . ': ' . json_encode([$leftKeys, $rightKeys], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

function assert_private_option(object $object, string $key, mixed $expected, string $message): void
{
    $property = (new ReflectionObject($object))->getProperty('defaultOptions');
    $property->setAccessible(true);
    $options = $property->getValue($object);
    if (($options[$key] ?? null) !== $expected) {
        throw new RuntimeException($message . ': ' . json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

$normalizedUserKeys = [
    'platform',
    'type',
    'user_id',
    'sec_user_id',
    'display_id',
    'nickname',
    'signature',
    'avatar_url',
    'gender',
    'city',
    'total',
    'verified',
];
$normalizedUserTotalKeys = ['follower_count', 'following_count', 'feed_count', 'liked_count'];
$normalizedFeedKeys = [
    'platform',
    'type',
    'item_id',
    'desc',
    'create_time',
    'duration',
    'cover_url',
    'video_url',
    'share_url',
    'width',
    'height',
    'is_top',
    'total',
    'author',
    'tags',
];
$normalizedFeedTotalKeys = ['like_count', 'comment_count', 'share_count', 'collect_count', 'play_count'];
$normalizedAuthorKeys = ['user_id', 'sec_user_id', 'display_id', 'nickname', 'avatar_url'];

assert_true(!is_file(__DIR__ . '/../src/Collect.php'), 'Collect.php 应该已删除');
assert_true(!is_file(__DIR__ . '/../src/types/UserInfo.php'), 'UserInfo.php 应改名为 UserInfoType.php');
assert_true(is_file(__DIR__ . '/../src/types/UserInfoType.php'), 'UserInfoType 类型文件缺失');
assert_true(is_file(__DIR__ . '/../src/types/FeedItemType.php'), 'FeedItemType 类型文件缺失');
require __DIR__ . '/../src/types/UserInfoType.php';
require __DIR__ . '/../src/types/FeedItemType.php';
assert_true(Hlw\Collect\Types\UserInfoType::TYPE === 'user', 'UserInfoType TYPE 失败');
assert_true(Hlw\Collect\Types\FeedItemType::TYPE === 'feed', 'FeedItemType TYPE 失败');
assert_keys(Hlw\Collect\Types\UserInfoType::schema(), $normalizedUserKeys, 'UserInfoType schema 字段不一致');
assert_keys(Hlw\Collect\Types\FeedItemType::schema(), $normalizedFeedKeys, 'FeedItemType schema 字段不一致');
assert_keys(Hlw\Collect\Types\UserInfoType::schema()['total'], $normalizedUserTotalKeys, 'UserInfoType total schema 字段不一致');
assert_keys(Hlw\Collect\Types\FeedItemType::schema()['total'], $normalizedFeedTotalKeys, 'FeedItemType total schema 字段不一致');
assert_keys(Hlw\Collect\Types\FeedItemType::schema()['author'], $normalizedAuthorKeys, 'FeedItemType author schema 字段不一致');

$input = 'appId=ks_wechat_small_app_2clientid=13did=wxo_a2f10b9345f4f6e89dca211c76473ec85f2dsmallAppVersion=v3.190.0{"count":12,"eid":"2059195662"}';
$sig3 = (new Sig3(1700000000, 100, 1700000100))->generate($input);
assert_true($sig3 === 'cfdf9ba89f6fcef9f79291905f7e8728ef7bdaed8e8e8c8c8382819b', 'Sig3 固定向量不一致');

$ab = ABogus::generate('a=1&b=2', 'Mozilla/5.0 TestUA');
assert_true(strlen($ab) > 100, 'ABogus 输出过短');

$ks = new KsMiniV1();
assert_true($ks->parseEid('https://live.kuaishou.com/profile/abc123') === 'abc123', 'parseEid 链接解析失败');
assert_true($ks->parseEid('https://c.kuaishou.com/fw/user/yongshun797?fid=3861998951&shareObjectId=2142580308') === 'yongshun797', 'parseEid c.kuaishou 链接解析失败');
assert_true($ks->parseEid('https://c.kuaishou.com/fw/user/?fid=3861998951&shareObjectId=2142580308') === '3861998951', 'parseEid 参数解析失败');

$feed = $ks->parseFeed([
    'photoId' => 'p1',
    'caption' => 'title',
    'coverUrls' => [['url' => 'cover']],
    'mainMvUrls' => [['url' => 'video']],
    'likeCount' => 7,
    'commentCount' => 8,
    'forwardCount' => 9,
    'collectCount' => 10,
    'viewCount' => 11,
    'timestamp' => 1700000000000,
    'duration' => 1200,
]);
assert_true($feed['item_id'] === 'p1' && $feed['create_time'] === 1700000000 && $feed['video_url'] === 'video' && $feed['total']['like_count'] === 7, 'parseFeed 解析失败');
assert_true($feed['type'] === 'feed', 'Ks parseFeed type 应为 feed');
assert_keys($feed, $normalizedFeedKeys, 'Ks parseFeed 字段不一致');
assert_keys($feed['total'], $normalizedFeedTotalKeys, 'Ks parseFeed total 字段不一致');
assert_keys($feed['author'], $normalizedAuthorKeys, 'Ks parseFeed author 字段不一致');
assert_true(!array_key_exists('createTime', $feed) && !array_key_exists('videoUrl', $feed) && !array_key_exists('likeCount', $feed), 'parseFeed 不应返回驼峰字段');
assert_no_camel_keys($feed, 'parseFeed 不应返回驼峰字段');
$dyH5V1 = Dy::H5('a=b')->v1();
$dyLiveV1 = Dy::Live('a=b')->v1();
$dyWebV1 = Dy::Web('a=b')->v1();
$dyUa = 'Mozilla/5.0 CustomUA';
$dyLiveArrayV1 = Dy::Live(['cookies' => 'a=b', 'ua' => $dyUa])->v1();
$dyLiveArgsV1 = Dy::Live('a=b', $dyUa)->v1();
$dyWebArrayV1 = Dy::Web(['cookies' => 'a=b', 'ua' => $dyUa])->v1();
$dyWebArgsV1 = Dy::Web('a=b', $dyUa)->v1();
assert_true($dyH5V1->user instanceof Hlw\Collect\Dy\H5\User\Profile, 'Dy::H5 user 属性入口失败');
assert_true($dyLiveV1->user instanceof Hlw\Collect\Dy\Live\User\Profile, 'Dy::Live user 属性入口失败');
assert_true($dyWebV1->user instanceof Hlw\Collect\Dy\Web\User\Profile, 'Dy::Web user 属性入口失败');
assert_true($dyWebV1->aweme instanceof Hlw\Collect\Dy\Web\Aweme\Aweme, 'Dy::Web aweme 属性入口失败');
assert_true(method_exists($dyWebV1->aweme, 'post'), 'Dy::Web aweme post 入口失败');
assert_private_option($dyLiveArrayV1->user, 'cookies', 'a=b', 'Dy::Live array cookies 入口失败');
assert_private_option($dyLiveArrayV1->user, 'userAgent', $dyUa, 'Dy::Live array ua 入口失败');
assert_private_option($dyLiveArgsV1->user, 'cookies', 'a=b', 'Dy::Live 参数 cookies 入口失败');
assert_private_option($dyLiveArgsV1->user, 'userAgent', $dyUa, 'Dy::Live 参数 ua 入口失败');
assert_private_option($dyWebArrayV1->user, 'userAgent', $dyUa, 'Dy::Web user array ua 入口失败');
assert_private_option($dyWebArrayV1->aweme, 'userAgent', $dyUa, 'Dy::Web aweme array ua 入口失败');
assert_private_option($dyWebArgsV1->user, 'cookies', 'a=b', 'Dy::Web 参数 cookies 入口失败');
assert_private_option($dyWebArgsV1->user, 'userAgent', $dyUa, 'Dy::Web 参数 ua 入口失败');
assert_true(new Hlw\Collect\Dy\H5\Request() instanceof Hlw\Collect\Request\BaseRequest, 'Dy H5 Request 入口失败');
assert_true(new Hlw\Collect\Dy\Live\Request() instanceof Hlw\Collect\Request\BaseRequest, 'Dy Live Request 入口失败');
assert_true(new Hlw\Collect\Dy\Web\Request() instanceof Hlw\Collect\Request\BaseRequest, 'Dy Web Request 入口失败');
$ksV1 = Ks::Mini('sid=test')->v1();
assert_true($ksV1->user instanceof Hlw\Collect\Ks\Mini\User\User, 'Ks::Mini user 属性入口失败');
assert_true($ksV1->feed instanceof Hlw\Collect\Ks\Mini\Feed\Feed, 'Ks::Mini feed 属性入口失败');
assert_true(method_exists($ksV1->feed, 'list'), 'Ks::Mini feed list 入口失败');
assert_true(new Hlw\Collect\Ks\Mini\Request() instanceof Hlw\Collect\Request\BaseRequest, 'Ks Mini Request 入口失败');
assert_true(Hlw\Collect\Dy\Support\InputParser::secUid('https://www.iesdouyin.com/share/user/MS4wLjABtest?sec_uid=MS4wLjABfromQuery') === 'MS4wLjABfromQuery', 'secUid 解析失败');
assert_true(Hlw\Collect\Dy\Support\InputParser::awemeId('https://www.douyin.com/video/1234567890') === '1234567890', 'awemeId 解析失败');
$dyUserHtml = '"userInfoRes":{"user_info":{"sec_uid":"sec","uid":"uid","unique_id":"display","nickname":"nick","signature":"bio","gender":1,"avatar_thumb":{"url_list":["avatar"]},"city":"city","mplatform_followers_count":9,"following_count":8,"aweme_count":7,"total_favorited":6,"account_cert_info":"{\"label_text\":\"verified\"}"}},"secUid":"sec"';
$dyUserInfo = Hlw\Collect\Dy\Support\UserInfo::parse($dyUserHtml);
assert_true(is_array($dyUserInfo) && $dyUserInfo['nickname'] === 'nick' && $dyUserInfo['verified'] === true, 'Dy UserInfo HTML 解析失败');

$liveResponse = new Hlw\Collect\Dy\Live\User\ProfileResponse([
    'data' => [
        'user_profile' => [
            'base_info' => [
                'sec_uid' => 'sec',
                'id_str' => 'uid',
                'display_id' => 'display',
                'nickname' => 'nick',
                'signature' => 'bio',
                'gender' => 2,
                'city' => 'city',
                'avatar_thumb' => ['url_list' => ['avatar']],
            ],
            'follow_info' => ['follower_count' => 1, 'following_count' => 2],
            'aweme_count' => 3,
            'total_favorited' => 4,
            'auth_info' => ['verify_content' => 'verified'],
        ],
    ],
]);
assert_true($liveResponse->raw()['data']['user_profile']['base_info']['nickname'] === 'nick', 'Live raw 失败');
$liveArray = $liveResponse->toArray();
$liveInfo = $liveResponse->toUserInfo();
assert_true($liveArray['nickname'] === 'nick' && $liveArray['platform'] === 'dy', 'Live toArray 失败');
assert_true($liveInfo instanceof Hlw\Collect\Types\UserInfoType, 'Live toUserInfo 应返回 UserInfoType 对象');
assert_true($liveInfo->nickname === 'nick' && $liveInfo->platform === 'dy', 'Live toUserInfo 属性访问失败');
assert_true($liveInfo->type === 'user', 'Live toUserInfo type 应为 user');
assert_true($liveInfo->gender === 0, 'Live gender 女应返回 0');
assert_true($liveInfo->total['follower_count'] === 1 && $liveInfo->total['liked_count'] === 4, 'Live total 统计字段失败');
assert_true($liveInfo->toArray() === $liveArray, 'Live UserInfo toArray 应等于响应 toArray');
assert_keys($liveArray, $normalizedUserKeys, 'Live toArray 字段不一致');
assert_keys($liveArray['total'], $normalizedUserTotalKeys, 'Live toArray total 字段不一致');
assert_no_camel_keys($liveArray, 'Live toArray 不应返回驼峰字段');
$dyMaleInfo = Hlw\Collect\Dy\Live\User\ProfileResponse::parse([
    'data' => [
        'user_profile' => [
            'base_info' => ['gender' => 1],
        ],
    ],
]);
assert_true($dyMaleInfo['gender'] === 1, 'Live gender 男应返回 1');

$awemeResponse = new Hlw\Collect\Dy\Web\Aweme\PostResponse(['aweme_list' => [[
    'aweme_id' => 'a1',
    'desc' => 'd',
    'create_time' => 1700000000,
    'duration' => 1200,
    'video' => [
        'cover' => ['url_list' => ['cover']],
        'play_addr' => ['url_list' => ['video']],
        'width' => 720,
        'height' => 1280,
    ],
    'share_url' => 'share',
    'statistics' => ['digg_count' => 2, 'comment_count' => 3, 'share_count' => 4, 'collect_count' => 5, 'play_count' => 6],
    'author' => [
        'uid' => 'uid',
        'sec_uid' => 'sec',
        'short_id' => 'display',
        'nickname' => 'nick',
        'avatar_thumb' => ['url_list' => ['avatar']],
    ],
    'video_tag' => [['tag_id' => 't1', 'tag_name' => 'tag', 'level' => 1]],
]]]);
$aweme = $awemeResponse->toArray()[0];
assert_true($aweme['item_id'] === 'a1' && $aweme['total']['like_count'] === 2, 'Web aweme toArray 失败');
assert_true($aweme['type'] === 'feed', 'Web aweme type 应为 feed');
assert_keys($aweme, $normalizedFeedKeys, 'Web aweme 字段不一致');
assert_keys($aweme['total'], $normalizedFeedTotalKeys, 'Web aweme total 字段不一致');
assert_keys($aweme['author'], $normalizedAuthorKeys, 'Web aweme author 字段不一致');
assert_true(!array_key_exists('awemeId', $aweme) && !array_key_exists('likeCount', $aweme), 'Web aweme 不应返回驼峰字段');
assert_no_camel_keys($aweme, 'Web aweme 不应返回驼峰字段');
$feedItem = Hlw\Collect\Types\FeedItemType::fromArray($aweme);
assert_true($feedItem instanceof Hlw\Collect\Types\FeedItemType, 'FeedItemType fromArray 应返回 FeedItemType 对象');
assert_true($feedItem->platform === 'dy' && $feedItem->item_id === 'a1', 'FeedItemType 属性访问失败');
assert_true($feedItem->total['like_count'] === 2 && $feedItem->author['nickname'] === 'nick', 'FeedItemType 嵌套字段失败');
assert_true($feedItem->toArray() === $aweme, 'FeedItemType toArray 应等于原数组');
assert_true(json_decode(json_encode($feedItem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true) === $aweme, 'FeedItemType jsonSerialize 失败');
$dirtyAwemeResponse = new Hlw\Collect\Dy\Web\Aweme\PostResponse(['aweme_list' => [null, 'bad', ['aweme_id' => 'a2']]]);
$dirtyAwemeList = $dirtyAwemeResponse->toArray();
assert_true(count($dirtyAwemeList) === 1 && $dirtyAwemeList[0]['item_id'] === 'a2', 'Web aweme 应跳过异常列表项');

$ksInfoResponse = new Hlw\Collect\Ks\Mini\User\ProfileResponse([
    'userProfile' => [
        'profile' => [
            'kwaiId' => 'kwai',
            'userId' => 'uid',
            'name' => 'nick',
            'user_sex' => 'F',
        ],
        'ownerCount' => ['fan' => 3, 'follow' => 4, 'photo' => 5, 'like' => 6],
        'cityName' => 'city',
    ],
]);
$ksInfo = $ksInfoResponse->toUserInfo();
$ksArray = $ksInfoResponse->toArray();
assert_true($ksInfo instanceof Hlw\Collect\Types\UserInfoType, 'Ks toUserInfo 应返回 UserInfoType 对象');
assert_true($ksInfo->nickname === 'nick' && $ksInfo->platform === 'ks', 'Ks toUserInfo 属性访问失败');
assert_true($ksInfo->type === 'user', 'Ks toUserInfo type 应为 user');
assert_true($ksInfo->gender === 0, 'Ks gender 女应返回 0');
assert_true($ksInfo->user_id === 'uid' && $ksInfo->total['follower_count'] === 3, 'Ks toUserInfo 下划线字段失败');
assert_true($ksInfo->toArray() === $ksArray, 'Ks UserInfo toArray 应等于响应 toArray');
assert_keys($ksArray, $normalizedUserKeys, 'Ks toArray 字段不一致');
assert_keys($ksArray['total'], $normalizedUserTotalKeys, 'Ks toArray total 字段不一致');
assert_same_keys($liveArray, $ksArray, 'Dy/Ks user_info 字段必须一致');
assert_no_camel_keys($ksArray, 'Ks toArray 不应返回驼峰字段');
$ksMaleInfo = Hlw\Collect\Ks\Mini\User\ProfileResponse::parse([
    'userProfile' => [
        'profile' => ['user_sex' => 'M'],
    ],
]);
assert_true($ksMaleInfo['gender'] === 1, 'Ks gender 男应返回 1');

$ksFeedResponse = new Hlw\Collect\Ks\Mini\Feed\ListResponse(['feeds' => [['photoId' => 'p1', 'caption' => 'title']]]);
$ksFeed = $ksFeedResponse->toArray()[0];
assert_true($ksFeed['item_id'] === 'p1', 'Ks feed toArray 失败');
assert_keys($ksFeed, $normalizedFeedKeys, 'Ks feed 字段不一致');
assert_same_keys($aweme, $ksFeed, 'Dy/Ks feed 字段必须一致');

echo "smoke ok\n";
