<?php

declare(strict_types=1);

namespace App\Domain\User;

enum Role: string
{
    case USER = 'ROLE_USER';
    case VERIFIED_USER = 'ROLE_VERIFIED_USER';

    case ADMIN = 'ROLE_ADMIN';

    public static function valueToArray(Role $case): array
    {
        return match ($case) {
            self::USER => [self::USER->value],
            self::VERIFIED_USER => [self::VERIFIED_USER->value],
            self::ADMIN => [self::ADMIN->value]
        };
    }
}
