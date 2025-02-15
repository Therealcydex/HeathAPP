<?php
// src/Controller/HomeController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig');
    }

    #[Route('/404', name: 'not_found')]
    public function notFound(): Response
    {
        return $this->render('404.html.twig');
    }


    #[Route('/back', name: 'back')]
    public function indexback(): Response
    {
        return $this->render('baseBack.html.twig');
    }
}
