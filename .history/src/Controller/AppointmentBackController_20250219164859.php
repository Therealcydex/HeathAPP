<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Appointment;
use App\Entity\Prescription;
use App\Repository\PrescriptionRepository;
use App\Form\AppointmentTypeBack;
use App\Form\PrescriptionTypeBack;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class AppointmentBackController extends AbstractController
{
    #[Route('/appointment/back', name: 'app_appointment_index',methods:['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->render('appointment_back/index.html.twig', [
            'controller_name' => 'AppointmentBackController',
        ]);
    }
}
