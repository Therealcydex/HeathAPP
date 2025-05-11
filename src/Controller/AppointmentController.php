<?php
namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Doctor;
use App\Entity\Prescription;
use App\Form\AppointmentType;
use App\Form\PrescriptionType;
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
    $session = $request->getSession();
    $googleToken = $session->get('google_token');

    // Redirect to Google Login if user is not authenticated
    if (!$googleToken) {
        return $this->redirectToRoute('google_login');
    }
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the appointment (with the doctor selected via the form)
            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

        return $this->redirectToRoute('google_add_event', ['appointmentId' => $appointment->getId()]);
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
    public function viewAppointments(Request $request): Response
    {
        $searchTerm = $request->query->get('search', '');
        $page = max(1, $request->query->getInt('page', 1)); 
        $limit = 4;
    
        $appointmentRepository = $this->entityManager->getRepository(Appointment::class);
    
        // If a search term exists, return all matching appointments (ignoring pagination)
        if (!empty($searchTerm)) {
            $appointments = $appointmentRepository->createQueryBuilder('a')
                ->leftJoin('a.doctor', 'd')
                ->where('a.clientName LIKE :search OR d.name LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%')
                ->orderBy('a.appointmentDate', 'ASC')
                ->getQuery()
                ->getResult();
    
            return $this->json([
                'appointments' => array_map(fn($appointment) => [
                    'id' => $appointment->getId(),
                    'clientName' => $appointment->getClientName(),
                    'doctorName' => $appointment->getDoctor() ? $appointment->getDoctor()->getName() : 'No Doctor Assigned',
                    'appointmentDate' => $appointment->getAppointmentDate() ? $appointment->getAppointmentDate()->format('Y-m-d') : 'N/A',
                    'prescriptions' => array_map(fn($p) => ['id' => $p->getId()], $appointment->getPrescriptions()->toArray()),
                ], $appointments),
            ]);
        }
    
        // Default paginated appointments when no search
        $totalAppointments = $appointmentRepository->count([]);
        $appointments = $appointmentRepository->findBy([], ['appointmentDate' => 'ASC'], $limit, ($page - 1) * $limit);
    
        return $this->render('appointment/view_appointments.html.twig', [
            'appointments' => $appointments,
            'currentPage' => $page,
            'totalPages' => ceil($totalAppointments / $limit),
        ]);
    }
    #[Route('/search', name: 'search_appointments', methods: ['GET'])]
public function searchAppointments(Request $request): Response
{
    $query = $request->query->get('q'); // Get the search query from the request

    $appointments = $this->entityManager->getRepository(Appointment::class)
        ->createQueryBuilder('a')
        ->where('a.clientName LIKE :query')
        ->setParameter('query', '%' . $query . '%')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();

    $results = [];
    foreach ($appointments as $appointment) {
        $results[] = [
            'id' => $appointment->getId(),
            'clientName' => $appointment->getClientName(),
            'doctor' => $appointment->getDoctor() ? $appointment->getDoctor()->getName() : 'No Doctor Assigned',
            'date' => $appointment->getAppointmentDate()?->format('Y-m-d'),
        ];
    }

    return $this->json($results);
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

    #[Route('/prescription/{id}', name: 'view_prescription', methods: ['GET'])]
    public function viewPrescription(Prescription $prescription): Response
    {
        return $this->render('appointment/view_prescription.html.twig', [
            'prescription' => $prescription,
        ]);
    }

    #[Route('/prescription/add/{appointmentId}', name: 'add_prescription', methods: ['GET', 'POST'])]
    public function addPrescription(Request $request, int $appointmentId, EntityManagerInterface $entityManager): Response
    {
        $appointment = $entityManager->getRepository(Appointment::class)->find($appointmentId);
        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found');
        }

        $prescription = new Prescription();
        $prescription->setAppointment($appointment);

        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prescription);
            $entityManager->flush();
            return $this->redirectToRoute('view_appointments');
        }

        return $this->render('appointment/add_prescription.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
