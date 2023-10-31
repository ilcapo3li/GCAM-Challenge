<?php

namespace App\Enums;

enum Status: int
{
    case TODO = 1;
    case IN_PROCESS = 2;
    case COMPLETED = 3;

    public function name(): string
    {
        return match ($this) {
            static::TODO => ucwords(__('to do')),
            static::IN_PROCESS => ucwords(__('in process')),
            static::COMPLETED => ucfirst(__('completed')),
        };
    }
}
