<?php

namespace Hlw\Collect\Dy\Web\User;

use Hlw\Collect\Dy\Support\UserInfo as UserInfoParser;
use Hlw\Collect\Types\UserInfo;

/**
 * @phpstan-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 * @psalm-import-type UserInfoArray from \Hlw\Collect\Types\UserInfo
 */
class ProfileResponse
{
    public function __construct(private mixed $raw)
    {
    }

    public function raw(): mixed
    {
        return $this->raw;
    }

    /**
     * @return UserInfoArray|array{}
     */
    public function toArray(): array
    {
        return is_string($this->raw) ? (UserInfoParser::parse($this->raw) ?? []) : [];
    }

    public function toUserInfo(): ?UserInfo
    {
        $data = $this->toArray();
        return $data === [] ? null : UserInfo::fromArray($data);
    }
}
