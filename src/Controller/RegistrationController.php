<?php 

// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Set role and status based on user type
            $role = $form->get('role')->getData();
            if ($role === User::ROLE_DOCTOR) {
                $user->setRoles([User::ROLE_DOCTOR]);
                $user->setStatus('pending'); // Doctors need approval
            } else {
                $user->setRoles([User::ROLE_PATIENT]);
                $user->setStatus('approved'); // Patients are approved automatically
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect based on role
            if ($role === User::ROLE_PATIENT) {
                return $this->redirectToRoute('app_login');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}