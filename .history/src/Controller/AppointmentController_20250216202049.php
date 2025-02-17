<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Prescription;
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
    private EntityManagerInterface $entityManager;

    // Injecting the EntityManagerInterface via constructor
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/book', name: 'book_appointment', methods: ['GET', 'POST'])]
    public function book(Request $request): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            return $this->redirectToRoute('appointment_success');
        }

        return $this->render('appointment/book.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/success', name: 'appointment_success', methods: ['GET'])]
    public function success(): Response
    {
        return new Response('Appointment successfully booked!');
    }

    #[Route('/view', name: 'view_appointments', methods: ['GET'])]
    public function viewAppointments(): Response
    {
        // Fetching all appointments from the database
        $appointments = $this->entityManager->getRepository(Appointment::class)->findAll();

        // Pass the appointments to the template
        return $this->render('appointment/view_appointments.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/{id}/show', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        // Render the template to show the appointment details
        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment): Response
    {
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('view_appointments');
        }
    
        return $this->render('appointment/edit.html.twig', [
            'form' => $form->createView(),
            'appointment' => $appointment
        ]);
    }

    #[Route('/{id}/delete', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment): RedirectResponse
    {
        // Handling related prescriptions before deleting the appointment to avoid foreign key constraint violations
        foreach ($appointment->getPrescriptions() as $prescription) {
            $this->entityManager->remove($prescription);
        }

        // Verify the CSRF token to prevent attacks
        if ($this->isCsrfTokenValid('delete' . $appointment->getId(), $request->request->get('_token'))) {
            // Remove the appointment and flush the changes
            $this->entityManager->remove($appointment);
            $this->entityManager->flush();
        }

        // Redirect back to the appointment booking page or the view appointments page
        return $this->redirectToRoute('view_appointments');
    }

    
}
