<?php

namespace App\Enums;

enum TaskType: int
{
    case TASK = 1;
    case STORY = 2;
    case EPIC = 3;
    case SUB_TASK = 4;


    public function name(): string
    {
        return match ($this) {
            static::TASK => ucfirst(__('task')),
            static::STORY  => ucfirst(__('story')),
            static::EPIC => ucfirst(__('epic')),
            static::SUB_TASK  => ucwords(__('sub task')),
        };
    }
}
