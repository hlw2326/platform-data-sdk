<?php

namespace Hlw\Collect\Ks\Mini\User;

use Hlw\Collect\Ks\Mini\Endpoint;

class User extends Endpoint
{
    public function info(string $input, array|string $options = []): ProfileResponse
    {
        return $this->infoByEid($this->eid($input, $options), $options);
    }

    public function infoByEid(string $eid, array|string $options = []): ProfileResponse
    {
        return new ProfileResponse($this->signedPost('/user/profile', ['eid' => $eid], $options));
    }
}
