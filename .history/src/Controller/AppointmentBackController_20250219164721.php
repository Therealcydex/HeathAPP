<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentBackController extends AbstractController
{
    #[Route('/appointment/back', name: 'app_appointment_back')]
    public function index(): Response
    {
        return $this->render('appointment_back/index.html.twig', [
            'controller_name' => 'AppointmentBackController',
        ]);
    }
}
