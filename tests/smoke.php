<?php

require __DIR__ . '/../src/Dy.php';
require __DIR__ . '/../src/Ks.php';
require __DIR__ . '/../src/request/BaseRequest.php';
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

assert_true(!is_file(__DIR__ . '/../src/Collect.php'), 'Collect.php 应该已删除');

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
    'timestamp' => 1700000000000,
]);
assert_true($feed['id'] === 'p1' && $feed['create_time'] === 1700000000 && $feed['video_url'] === 'video' && $feed['like_count'] === 7, 'parseFeed 解析失败');
assert_true(!array_key_exists('createTime', $feed) && !array_key_exists('videoUrl', $feed) && !array_key_exists('likeCount', $feed), 'parseFeed 不应返回驼峰字段');
assert_no_camel_keys($feed, 'parseFeed 不应返回驼峰字段');
$dyH5V1 = Dy::H5('a=b')->v1();
$dyLiveV1 = Dy::Live('a=b')->v1();
$dyWebV1 = Dy::Web('a=b')->v1();
assert_true($dyH5V1->user instanceof Hlw\Collect\Dy\H5\User\Profile, 'Dy::H5 user 属性入口失败');
assert_true($dyLiveV1->user instanceof Hlw\Collect\Dy\Live\User\Profile, 'Dy::Live user 属性入口失败');
assert_true($dyWebV1->user instanceof Hlw\Collect\Dy\Web\User\Profile, 'Dy::Web user 属性入口失败');
assert_true($dyWebV1->aweme instanceof Hlw\Collect\Dy\Web\Aweme\Aweme, 'Dy::Web aweme 属性入口失败');
assert_true(method_exists($dyWebV1->aweme, 'post'), 'Dy::Web aweme post 入口失败');
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

$liveResponse = new Hlw\Collect\Dy\Live\User\ProfileResponse([
    'data' => [
        'user_profile' => [
            'base_info' => ['sec_uid' => 'sec', 'id_str' => 'uid', 'display_id' => 'display', 'nickname' => 'nick'],
            'follow_info' => ['follower_count' => 1, 'following_count' => 2],
        ],
    ],
]);
assert_true($liveResponse->raw()['data']['user_profile']['base_info']['nickname'] === 'nick', 'Live raw 失败');
assert_true($liveResponse->toUserInfo()['nickname'] === 'nick', 'Live toUserInfo 失败');
assert_no_camel_keys($liveResponse->toUserInfo(), 'Live toUserInfo 不应返回驼峰字段');

$awemeResponse = new Hlw\Collect\Dy\Web\Aweme\PostResponse(['aweme_list' => [['aweme_id' => 'a1', 'desc' => 'd', 'statistics' => ['digg_count' => 2]]]]);
$aweme = $awemeResponse->toArray()[0];
assert_true($aweme['aweme_id'] === 'a1' && $aweme['like_count'] === 2, 'Web aweme toArray 失败');
assert_true(!array_key_exists('awemeId', $aweme) && !array_key_exists('likeCount', $aweme), 'Web aweme 不应返回驼峰字段');
assert_no_camel_keys($aweme, 'Web aweme 不应返回驼峰字段');

$ksInfoResponse = new Hlw\Collect\Ks\Mini\User\ProfileResponse([
    'userProfile' => [
        'profile' => [
            'kwaiId' => 'kwai',
            'userId' => 'uid',
            'name' => 'nick',
        ],
        'ownerCount' => ['fan' => 3, 'follow' => 4, 'photo' => 5],
    ],
]);
assert_true($ksInfoResponse->toUserInfo()['name'] === 'nick', 'Ks toUserInfo 失败');
assert_true($ksInfoResponse->toUserInfo()['user_id'] === 'uid' && $ksInfoResponse->toUserInfo()['fan_count'] === 3, 'Ks toUserInfo 下划线字段失败');
assert_no_camel_keys($ksInfoResponse->toUserInfo(), 'Ks toUserInfo 不应返回驼峰字段');

$ksFeedResponse = new Hlw\Collect\Ks\Mini\Feed\ListResponse(['feeds' => [['photoId' => 'p1', 'caption' => 'title']]]);
assert_true($ksFeedResponse->toArray()[0]['id'] === 'p1', 'Ks feed toArray 失败');

echo "smoke ok\n";
