<?php

declare(strict_types=1);

namespace App\Application\User\Command\ChangePassword;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function __invoke(ChangePasswordUserCommand $changePasswordUserCommand): void
    {
        $user = $changePasswordUserCommand->getUser();

        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $changePasswordUserCommand->getPlainPassword())
        );

        $this->userRepository->update($user);
    }
}
