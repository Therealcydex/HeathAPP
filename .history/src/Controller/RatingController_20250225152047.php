<?php

namespace App\Controller;

use App\Entity\Doctor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rating')]
class RatingController extends AbstractController
{
    #[Route('/doctor/{id}', name: 'rate_doctor', methods: ['POST'])]
    public function rateDoctor(Request $request, Doctor $doctor, EntityManagerInterface $entityManager): Response
    {
        $rating = (float) $request->request->get('rating');

        if ($rating < 1 || $rating > 5) {
            return new Response('Invalid rating value.', 400);
        }

        $doctor->setRating($rating);
        $entityManager->flush();

        $this->addFlash('success', 'Rating submitted successfully!');
        return $this->redirectToRoute('book_appointment');
    }
}
