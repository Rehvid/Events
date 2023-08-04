<?php

declare(strict_types=1);

namespace App\Application\User\Command\Register;

use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\User;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function __invoke(RegisterUserCommand $registerUserCommand): void
    {
        $user = new User(
            email: $registerUserCommand->getEmail(),
            firstname: $registerUserCommand->getFirstname(),
            lastname: $registerUserCommand->getLastname(),
            roles: $registerUserCommand->getRoles()
        );

        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $registerUserCommand->getPlainPassword())
        );

        $this->userRepository->add($user);
    }
}
