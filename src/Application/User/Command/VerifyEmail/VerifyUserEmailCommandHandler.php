<?php

declare(strict_types=1);

namespace App\Application\User\Command\VerifyEmail;

use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;

final class VerifyUserEmailCommandHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @throws UserNotFoundException
     */
    public function __invoke(VerifyUserEmailCommand $verifyUserEmailCommand): void
    {
        $user = $this->userRepository->findByEmail($verifyUserEmailCommand->getEmail());

        if (null === $user) {
            throw new UserNotFoundException('User not found');
        }

        $user->verifyEmail(
            isVerified: $verifyUserEmailCommand->isVerified(),
            roles: $verifyUserEmailCommand->getRoles()
        );

        $this->userRepository->update($user);
    }
}
