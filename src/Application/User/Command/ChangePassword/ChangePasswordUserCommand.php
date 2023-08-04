<?php

declare(strict_types=1);

namespace App\Application\User\Command\ChangePassword;

use App\Domain\User\User;
use App\Shared\Application\Command\CommandInterface;

class ChangePasswordUserCommand implements CommandInterface
{
    public function __construct(private readonly User $user, private readonly string $plainPassword)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }
}
