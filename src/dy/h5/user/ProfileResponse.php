<?php

namespace Hlw\Collect\Dy\H5\User;

use Hlw\Collect\Dy\Support\UserInfo as UserInfoParser;
use Hlw\Collect\Types\UserInfoType;

/**
 * @phpstan-import-type UserInfoArray from \Hlw\Collect\Types\UserInfoType
 * @psalm-import-type UserInfoArray from \Hlw\Collect\Types\UserInfoType
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

    public function toUserInfo(): ?UserInfoType
    {
        $data = $this->toArray();
        return $data === [] ? null : UserInfoType::fromArray($data);
    }
}
