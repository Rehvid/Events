<?php

declare(strict_types=1);

namespace App\UI\Http\Controller\Home;

use App\UI\Http\Controller\AbstractRenderController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractRenderController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home.html.twig');
    }
}
