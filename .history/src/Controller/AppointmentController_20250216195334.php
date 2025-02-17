<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    #[Route('/{id}/delete', name: 'appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Verify the CSRF token to prevent attacks
        if ($this->isCsrfTokenValid('delete' . $appointment->getId(), $request->request->get('_token'))) {
            // Remove the appointment and flush the changes
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        // Redirect back to the appointment booking page
        return $this->redirectToRoute('book_appointment');
    }
}
