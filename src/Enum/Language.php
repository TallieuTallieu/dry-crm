<?php

namespace Tnt\Crm\Enum;

enum Language: string
{
    case Dutch = 'nl';
    case French = 'fr';
    case English = 'en';
    case German = 'de';

    public static function enum(): array
    {
        return array_map(function (self $item) {
            return [$item->value, $item->name];
        }, self::cases());
    }
}
