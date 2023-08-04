<?php

declare(strict_types=1);

namespace App\UI\Http\Controller\Home;

use App\UI\Http\Controller\AbstractRenderController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractRenderController
{
    // TODO: Dodać translacje do exceptionow, dodać routingi chronione, stworzyć od nowa migracje i zrobić gita potem zastanowic sie co dalej!

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home.html.twig');
    }
}
