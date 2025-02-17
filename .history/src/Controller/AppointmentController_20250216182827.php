<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/appointment')]
final class AppointmentController extends AbstractController
{
    #[Route(name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/appointment/{id}/prescriptions', name: 'app_appointment_prescriptions', methods: ['GET'])]
public function viewPrescriptions(Appointment $appointment): Response
{
    // Assuming you have a getter for prescriptions in your Appointment entity
    $prescriptions = $appointment->getPrescriptions(); 

    return $this->render('appointment/prescriptions.html.twig', [
        'appointment' => $appointment,
        'prescriptions' => $prescriptions,
    ]);
}

#[Route('/{id}/add-prescription', name: 'add_prescription', methods: ['GET', 'POST'])]
public function addPrescription(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
{
    $prescription = new Prescription();
    $prescription->setAppointment($appointment);

    $form = $this->createForm(PrescriptionType::class, $prescription);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($prescription);
        $entityManager->flush();

        return $this->redirectToRoute('app_appointment_show', ['id' => $appointment->getId()]);
    }

    return $this->render('appointment/add_prescription.html.twig', [
        'form' => $form->createView(),
        'appointment' => $appointment,
    ]);
}
}
