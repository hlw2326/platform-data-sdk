<?php

namespace Hlw\Collect\Ks\Mini\Photo;

use Hlw\Collect\Ks\Mini\Endpoint;

class Photo extends Endpoint
{
    public function commentList(string $photoId, array|string $options = []): mixed
    {
        $options = $this->options($options);
        $data = ['photoId' => $photoId, 'count' => $options['count'] ?? 20];
        if (isset($options['pcursor'])) {
            $data['pcursor'] = $options['pcursor'];
        }
        unset($options['count'], $options['pcursor']);

        return $this->signedPost('/photo/comment/list', $data, $options);
    }
}
