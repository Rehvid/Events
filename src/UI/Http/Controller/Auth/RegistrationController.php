<?php

declare(strict_types=1);

namespace App\UI\Http\Controller\Auth;

use App\Application\User\Command\Register\RegisterUserCommand;
use App\Application\User\Query\FindByEmail\FindByEmailQuery;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\Role;
use App\UI\Http\Controller\AbstractRenderController;
use App\UI\Http\Form\User\RegisterUserFormType;
use App\UI\Http\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractRenderController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'register', methods: ['GET', 'HEAD', 'POST'])]
    public function register(
        Request $request,
        EmailVerifier $emailVerifier,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(RegisterUserFormType::class);
        $form->handleRequest($request);

        if ($this->isFormSubmittedAndValid($form)) {
            $email = $form->get('email')->getData();

            $this->dispatch(new RegisterUserCommand(
                email: $email,
                plainPassword: $form->get('plainPassword')->getData(),
                firstname: $form->get('firstname')->getData(),
                lastname: $form->get('lastname')->getData(),
                roles: Role::valueToArray(Role::USER)
            ));

            try {
                $user = $this->ask(new FindByEmailQuery($email));
            } catch (UserNotFoundException $e) {
                return $this->render('security/register.html.twig', ['error' => $e->getMessage(), 'form' => $form]);
            }

            $emailVerifier->sendEmailConfirmation(
                'verify_email',
                $user,
                (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('security/confirmation_email.html.twig')
            );

            $this->addFlash('success', $this->translator->trans('alert.registration', domain: 'alerts'));

            return $this->redirectToRoute('login');
        }

        return $this->render('security/register.html.twig', compact('form'));
    }
}
