<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    #[Route('/book', name: 'book_appointment', methods: ['GET', 'POST'])]
    public function book(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new appointment instance and form
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('appointment_success');
        }

        // Fetch all existing appointments
        $appointments = $entityManager->getRepository(Appointment::class)->findAll();

        return $this->render('appointment/book.html.twig', [
            'form' => $form->createView(),
            'appointments' => $appointments, // Pass appointments to the template
        ]);
    }

    #[Route('/success', name: 'appointment_success', methods: ['GET'])]
    public function success(): Response
    {
        return new Response('Appointment successfully booked!');
    }
}
