<?php

namespace App\Enums;

enum UserType: int
{
    case ADMIN = 1;
    case USER = 2;

    public function name(): string
    {
        return match ($this) {
            static::ADMIN => ucfirst(__('admin')),
            static::USER  => ucfirst(__('user')),
        };
    }
}
