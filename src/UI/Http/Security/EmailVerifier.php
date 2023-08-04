<?php

declare(strict_types=1);

namespace App\UI\Http\Security;

use App\Application\User\Command\VerifyEmail\VerifyUserEmailCommand;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Role;
use App\Domain\User\User;
use App\Shared\Application\Command\CommandBusInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class EmailVerifier
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly MailerInterface $mailer,
        private readonly CommandBusInterface $commandBus
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        /** @var User $user */
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail(),
            ['email' => $user->getEmail()]
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $userInterface): void
    {
        /** @var User $user */
        $user = $userInterface;

        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), (string) $user->getId(), $user->getEmail());

        try {
            $this->commandBus->dispatch(new VerifyUserEmailCommand(
                email: $user->getEmail(),
                isVerified: true,
                roles: Role::valueToArray(Role::VERIFIED_USER)
            ));
        } catch (UserNotFoundException $e) {
            new RedirectResponse($this->urlGenerator->generate('login'));
        }
    }
}
