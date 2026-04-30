<?php

namespace Hlw\Collect\Request;

use RuntimeException;

class BaseRequest
{
    public function __construct(
        protected array $defaultHeaders = [],
        protected array $defaultParams = []
    ) {
    }

    public function request(string $url, string $method, array $options = []): mixed
    {
        $finalUrl = $this->buildUrl($url, $options);
        $headers = $this->buildHeaders($options);
        $body = $this->buildBody($options, $headers);

        $ch = curl_init($finalUrl);
        if ($ch === false) {
            throw new RuntimeException('无法初始化 curl');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_TIMEOUT_MS => (int)($options['timeout'] ?? 10000),
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        if ($response === false && in_array(curl_errno($ch), [60, 77], true)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($ch);
        }
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException($error !== '' ? $error : '请求失败');
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $response;
    }

    public function get(string $url, array $options = []): mixed
    {
        return $this->request($url, 'GET', $options);
    }

    public function post(string $url, array $options = []): mixed
    {
        return $this->request($url, 'POST', $options);
    }

    protected function buildUrl(string $baseUrl, array $options = []): string
    {
        $parts = parse_url($baseUrl);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            throw new RuntimeException('无效 URL: ' . $baseUrl);
        }

        $params = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $params);
        }

        foreach ($this->defaultParams as $key => $value) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = $value;
            }
        }

        foreach ($options['params'] ?? [] as $key => $value) {
            $params[$key] = $value;
        }

        foreach ($options['removeParams'] ?? [] as $key) {
            unset($params[$key]);
        }

        $path = $parts['path'] ?? '';
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return $parts['scheme'] . '://' . $parts['host'] . $port . $path . ($query !== '' ? '?' . $query : '');
    }

    protected function buildHeaders(array $options = []): array
    {
        $headers = $this->defaultHeaders;
        if (isset($options['userAgent'])) {
            $headers['User-Agent'] = $options['userAgent'];
        }
        if (isset($options['cookies'])) {
            $headers['Cookie'] = $options['cookies'];
        }
        foreach ($options['headers'] ?? [] as $key => $value) {
            $headers[$key] = $value;
        }
        return $headers;
    }

    protected function buildBody(array $options, array &$headers): ?string
    {
        if (!array_key_exists('body', $options)) {
            return null;
        }

        $body = $options['body'];
        if (is_string($body)) {
            $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/x-www-form-urlencoded';
            return $body;
        }

        if (($options['bodyType'] ?? 'json') === 'form') {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            return http_build_query($body, '', '&', PHP_QUERY_RFC3986);
        }

        $headers['Content-Type'] = 'application/json';
        return json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function formatHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $key => $value) {
            $result[] = $key . ': ' . $value;
        }
        return $result;
    }
}
