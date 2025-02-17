<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch only unapproved doctors
        $doctors = $entityManager->getRepository(User::class)->findBy([
            'isApproved' => false,
        ]);

        // Filter only doctors (ROLE_DOCTOR)
        $pendingDoctors = array_filter($doctors, function ($doctor) {
            return in_array('ROLE_DOCTOR', $doctor->getRoles());
        });

        return $this->render('admin/index.html.twig', [
            'doctors' => $pendingDoctors,
        ]);
    }

    #[Route('/approve/{id}', name: 'approve_doctor', methods: ['GET'])]
    public function approveDoctor(User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$user || !in_array('ROLE_DOCTOR', $user->getRoles())) {
            throw $this->createNotFoundException('Doctor not found.');
        }

        $user->setIsApproved(true);
        $entityManager->flush();

        $this->addFlash('success', 'Doctor approved successfully.');
        return $this->redirectToRoute('admin_dashboard');
    }
}
