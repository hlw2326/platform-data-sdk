<?php

namespace Hlw\Collect\Ks\Mini;

use Hlw\Collect\Ks\Support\InputParser;
use Hlw\Collect\Ks\Support\Options;

abstract class Endpoint
{
    private const HOST = 'https://wxmini-api.uyouqu.com/rest/wd/wechatApp';
    private const DEFAULT_SMALL_APP_VERSION = 'v3.190.0';

    protected Sig3 $sig3;

    public function __construct(
        protected Request $client,
        protected array $defaultOptions = [],
        ?Sig3 $sig3 = null
    ) {
        $this->sig3 = $sig3 ?? new Sig3();
    }

    protected function options(array|string $options = []): array
    {
        return Options::merge($this->defaultOptions, $options);
    }

    protected function eid(string $input, array|string $options = []): string
    {
        $options = $this->options($options);
        return InputParser::eid($input, (int)($options['timeout'] ?? 8000));
    }

    protected function signedPost(string $path, array $data, array|string $options = []): mixed
    {
        $options = $this->options($options);
        $did = $this->did($options);
        $smallAppVersion = $options['smallAppVersion'] ?? self::DEFAULT_SMALL_APP_VERSION;
        unset($options['did'], $options['smallAppVersion']);

        return $this->client->post($this->buildUrl($path, $data, $did, $smallAppVersion), [
            ...$options,
            'body' => $data,
        ]);
    }

    private function buildUrl(string $path, array $data, string $did, string $smallAppVersion): string
    {
        $dataString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $input = "appId=ks_wechat_small_app_2clientid=13did={$did}smallAppVersion={$smallAppVersion}{$dataString}";

        return self::HOST . $path . '?__NS_sig3=' . $this->sig3->generate($input);
    }

    private function did(array $options): string
    {
        if (isset($options['did']) && is_string($options['did']) && $options['did'] !== '') {
            return $options['did'];
        }

        return $this->cookieValue($options['cookies'] ?? '', 'did');
    }

    private function cookieValue(mixed $cookies, string $key): string
    {
        if (!is_string($cookies) || $cookies === '') {
            return '';
        }

        foreach (explode(';', $cookies) as $part) {
            $part = trim($part);
            if (str_starts_with($part, $key . '=')) {
                return urldecode(substr($part, strlen($key) + 1));
            }
        }

        return '';
    }
}
