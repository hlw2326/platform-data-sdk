<?php

namespace Hlw\Collect\Ks\Mini;

use Hlw\Collect\Ks\Mini\Feed\Feed;
use Hlw\Collect\Ks\Mini\Feed\ListResponse;
use Hlw\Collect\Ks\Mini\Photo\Photo;
use Hlw\Collect\Ks\Mini\User\ProfileResponse;
use Hlw\Collect\Ks\Mini\User\User;
use Hlw\Collect\Ks\Support\InputParser;
use Hlw\Collect\Ks\Support\Options;
use RuntimeException;

class V1
{
    private const DEFAULT_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 MicroMessenger/7.0.20.1781(0x6700143B) NetType/WIFI MiniProgramEnv/Windows WindowsWechat/WMPF WindowsWechat(0x63090a13) UnifiedPCWindowsWechat(0xf254162e) XWEB/18163';

    public readonly User $user;
    public readonly Feed $feed;
    public readonly Photo $photo;

    public function __construct(?Request $client = null, ?Sig3 $sig3 = null, array|string $defaultOptions = [])
    {
        $client ??= new Request([
            'User-Agent' => self::DEFAULT_UA,
            'Referer' => 'https://servicewechat.com/wx79a83b1a1e8a7978/691/page-frame.html',
            'Content-Type' => 'application/json',
        ]);
        $sig3 ??= new Sig3();
        $defaultOptions = Options::normalize($defaultOptions);

        $this->user = new User($client, $defaultOptions, $sig3);
        $this->feed = new Feed($client, $defaultOptions, $sig3);
        $this->photo = new Photo($client, $defaultOptions, $sig3);
    }

    public function getUserFeeds(string $eid, array $options = []): mixed
    {
        return $this->feed->listByEid($eid, $options)->raw();
    }

    public function getUserFeedsByInput(string $input, array $options = []): mixed
    {
        return $this->feed->list($input, $options)->raw();
    }

    public function getUserProfile(string $eid, array $options = []): mixed
    {
        return $this->user->infoByEid($eid, $options)->raw();
    }

    public function getUserProfileByInput(string $input, array $options = []): mixed
    {
        $eid = $this->requireEid($input);
        return $this->getUserProfile($eid, $options);
    }

    public function getPhotoComments(string $photoId, array $options = []): mixed
    {
        return $this->photo->commentList($photoId, $options);
    }

    public function getRecommend(array $options = []): mixed
    {
        return $this->feed->recommend($options);
    }

    public function parseEid(string $input): ?string
    {
        return InputParser::parseEid($input);
    }

    public function resolveEid(string $input): ?string
    {
        try {
            return InputParser::eid($input);
        } catch (RuntimeException) {
            return null;
        }
    }

    public function parseFeed(array $feed): array
    {
        return ListResponse::parseFeed($feed);
    }

    public function feedItems(array $response): array
    {
        return (new ListResponse($response))->toArray();
    }

    public function userInfo(array $response): array
    {
        return ProfileResponse::parse($response);
    }

    private function requireEid(string $input): string
    {
        return InputParser::eid($input);
    }
}
