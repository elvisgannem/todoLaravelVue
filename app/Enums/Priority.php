<?php

namespace App\Enums;

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    public function label(): string
    {
        return match($this) {
            Priority::LOW => 'Low',
            Priority::MEDIUM => 'Medium',
            Priority::HIGH => 'High',
        };
    }

    public function color(): string
    {
        return match($this) {
            Priority::LOW => 'green',
            Priority::MEDIUM => 'yellow',
            Priority::HIGH => 'red',
        };
    }

    public static function options(): array
    {
        return [
            ['value' => self::LOW->value, 'label' => self::LOW->label()],
            ['value' => self::MEDIUM->value, 'label' => self::MEDIUM->label()],
            ['value' => self::HIGH->value, 'label' => self::HIGH->label()],
        ];
    }
}
