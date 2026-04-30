<?php

namespace Hlw\Collect\Dy\Web\User;

use Hlw\Collect\Dy\Support\UserInfo;

class ProfileResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    public function toUserInfo(): ?array
    {
        return is_string($this->raw) ? UserInfo::parse($this->raw) : null;
    }
}
