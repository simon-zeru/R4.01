<?php

namespace App\Controller;

use App\Service\ChangeMonnaieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(
        path: '/',
        name: 'app_default_index',
    )]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    // TODO : route et contrôleur de la page de contact
    #[Route(
        path: '/contact',
        name: 'app_default_contact',
    )]
    public function contact(): Response
    {
        return $this->render('default/contact.html.twig');
    }
}
