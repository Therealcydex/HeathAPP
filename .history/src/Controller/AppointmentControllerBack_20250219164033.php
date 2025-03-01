<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Prescription;
use App\Repository\PrescriptionRepository;
use App\Form\AppointmentTypeBack;
use App\Form\PrescriptionTypeBack;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/appointment')]
final class AppointmentControllerBack extends AbstractController
{
    #[Route('/appointmentBack', name: 'app_appointment_index', methods: ['GET'])]
public function index(AppointmentRepository $appointmentRepository): Response
{
    return $this->render('appointmentBack/index.html.twig', [
        'appointments' => $appointmentRepository->findAll(),
    ]);
}


    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentTypeBack::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // No need to handle doctor explicitly, it will be handled by the form
            $entityManager->persist($appointment);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('appointmentBack/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }
    
    

    #[Route('/{id}', name: 'back_app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('appointmentBack/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AppointmentTypeBack::class, $appointment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // The doctor will be updated based on the form submission
            $entityManager->flush();
    
            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('appointmentBack/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'back_app_appointment_delete', methods: ['POST'])]
public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
{
    // Check if the CSRF token is valid
    if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->request->get('_token'))) {
        // Remove the appointment from the database
        $entityManager->remove($appointment);
        $entityManager->flush();
    }

    // Redirect to the back-office appointment index page
    return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
}

    #[Route('/appointmentBack/{id}/prescriptions', name: 'back_app_appointment_prescriptions', methods: ['GET'])]
public function viewPrescriptions(Appointment $appointment): Response
{
    // Assuming you have a getter for prescriptions in your Appointment entity
    $prescriptions = $appointment->getPrescriptions(); 

    return $this->render('appointmentBack/prescriptions.html.twig', [
        'appointment' => $appointment,
        'prescriptions' => $prescriptions,
    ]);
}

// Back-office route
#[Route('/{id}/add-prescription', name: 'back_add_prescription', methods: ['GET', 'POST'])]
public function addPrescription(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
{
    $prescription = new Prescription();
    $prescription->setAppointment($appointment);

    $form = $this->createForm(PrescriptionTypeBack::class, $prescription);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($prescription);
        $entityManager->flush();

        return $this->redirectToRoute('back_app_appointment_show', ['id' => $appointment->getId()]);
    }

    return $this->render('appointmentBack/add_prescription.html.twig', [
        'form' => $form->createView(),
        'appointment' => $appointment,
    ]);
}
#[Route('/prescription/{id}/delete', name: 'app_prescription_delete', methods: ['POST'])]
public function deletePrescription(Prescription $prescription, EntityManagerInterface $entityManager): Response
{
    $appointmentId = $prescription->getAppointment()->getId(); // Get the appointment ID

    $entityManager->remove($prescription);
    $entityManager->flush();

    return $this->redirectToRoute('back_app_appointment_prescriptions', ['id' => $appointmentId]);
}

#[Route('/prescription/{id}/edit', name: 'app_prescription_edit', methods: ['GET', 'POST'])]
public function editPrescription(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(PrescriptionTypeBack::class, $prescription);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('back_app_appointment_prescriptions', ['id' => $prescription->getAppointment()->getId()]);
    }

    return $this->render('appointmentBack/edit_prescription.html.twig', [
        'form' => $form->createView(),
        'prescription' => $prescription,
    ]);
}


}
