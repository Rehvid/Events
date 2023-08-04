<?php

declare(strict_types=1);

namespace App\Application\User\Query\FindByEmail;

use App\Shared\Application\Query\QueryInterface;

final class FindByEmailQuery implements QueryInterface
{
    public function __construct(private readonly string $email)
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
