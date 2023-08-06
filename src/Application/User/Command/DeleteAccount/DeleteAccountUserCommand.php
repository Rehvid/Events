<?php

declare(strict_types=1);

namespace App\Application\User\Command\DeleteAccount;

use App\Domain\User\User;
use App\Shared\Application\Command\CommandInterface;

class DeleteAccountUserCommand implements CommandInterface
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
