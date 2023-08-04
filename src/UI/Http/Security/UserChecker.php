<?php

declare(strict_types=1);

namespace App\UI\Http\Security;

use App\Domain\User\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (! $user instanceof AppUser) {
            return;
        }

        if (! $user->isVerified()) {
            throw new CustomUserMessageAccountStatusException('User has not verified email yet.');
        }
    }
}
