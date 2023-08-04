<?php

declare(strict_types=1);

namespace App\UI\Http\Controller\Auth;

use App\Application\User\Query\FindByEmail\FindByEmailQuery;
use App\Domain\User\Exception\UserNotFoundException;
use App\UI\Http\Controller\AbstractRenderController;
use App\UI\Http\Security\ApplicationAuthenticator;
use App\UI\Http\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class VerifyEmailController extends AbstractRenderController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/create/verify/email/{email}', name: 'create_verify_email', methods: ['GET', 'HEAD'])]
    public function create(EmailVerifier $emailVerifier, string $email): Response
    {
        try {
            $user = $this->ask(new FindByEmailQuery($email));
        } catch (UserNotFoundException $e) {
            return $this->redirectToRoute('login');
        }

        $emailVerifier->sendEmailConfirmation(
            'verify_email',
            $user,
            (new TemplatedEmail())
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('security/confirmation_email.html.twig')
        );

        $this->addFlash('info', $this->translator->trans('alert.new_verify_email'));

        return $this->redirectToRoute('login');
    }

    #[Route('/verify/email', name: 'verify_email', methods: ['GET', 'HEAD'])]
    public function verifyUserEmail(
        Request $request,
        EmailVerifier $emailVerifier,
        UserAuthenticatorInterface $userAuthenticator,
        ApplicationAuthenticator $authenticator,
    ): Response {
        if (! $request->get('email')) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        }

        try {
            $user = $this->ask(new FindByEmailQuery($request->get('email')));
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('register');
        }

        $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );

        $this->addFlash('success', $this->translator->trans('alert.verify_email', domain: 'alerts'));

        return $this->redirectToRoute('home');
    }
}
