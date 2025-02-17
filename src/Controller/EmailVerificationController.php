<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw new AuthenticationException('Invalid verification token');
        }

        // Mark the user as verified
        $user->setVerified(true);
        $user->setVerificationToken(null);  // Remove the token after verification
        $entityManager->flush();

        return $this->redirectToRoute('app_login');
    }
}
