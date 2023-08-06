<?php

declare(strict_types=1);

namespace App\UI\Http\Controller\User;

use App\Application\User\Command\ChangePassword\ChangePasswordUserCommand;
use App\Application\User\Command\DeleteAccount\DeleteAccountUserCommand;
use App\Application\User\Command\UpdateProfile\UpdateProfileUserCommand;
use App\Domain\User\User;
use App\UI\Http\Controller\AbstractRenderController;
use App\UI\Http\Form\User\ChangePasswordUserFormType;
use App\UI\Http\Form\User\ProfileUserFormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractRenderController
{
    #[Route('user/profile', name: 'user_profile', methods: ['GET', 'HEAD', 'PATCH'])]
    public function edit(Request $request): Response
    {
        $form = $this->createForm(ProfileUserFormType::class, $this->getUser(), ['method' => 'PATCH']);
        $form->handleRequest($request);

        if ($this->isFormSubmittedAndValid($form)) {
            $this->dispatch(new UpdateProfileUserCommand(
                firstname: $form->get('firstname')->getData(),
                lastname: $form->get('lastname')->getData(),
                email: $form->get('email')->getData()
            ));

            $this->addFlash('success', $this->translator->trans('alert.update_profile', domain: 'alerts'));
        }

        return $this->render('user/profile/edit.html.twig', compact('form'));
    }

    #[Route('user/profile/change-password', name: 'user_change_password', methods: ['GET', 'HEAD', 'PATCH'])]
    public function changePassword(Request $request): Response
    {
        $form = $this->createForm(ChangePasswordUserFormType::class, options: [
            'method' => 'patch',
        ]);
        $form->handleRequest($request);

        if ($this->isFormSubmittedAndValid($form)) {
            /** @var User $user */
            $user = $this->getUser();

            $this->dispatch(
                new ChangePasswordUserCommand(
                    user: $user,
                    plainPassword: $form->get('plainPassword')->getData()
                )
            );

            $this->addFlash('success', $this->translator->trans('alert.change_password', domain: 'alerts'));

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile/change-password.html.twig', compact('form'));
    }

    #[Route('user/profile/{id}/delete', name: 'user_delete', methods: ['GET', 'HEAD'])]
    public function deleteAccount(User $user): RedirectResponse
    {
        $this->dispatch(
            new DeleteAccountUserCommand(user: $user)
        );

        $this->addFlash('success', $this->translator->trans('alert.delete_account', domain: 'alerts'));

        return $this->redirectToRoute('home');
    }
}
