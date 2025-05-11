<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Twilio\Rest\Client;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/', name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/newFront/{id}', name: 'app_commande_new_front', methods: ['GET', 'POST'])]
    public function newFront(Request $request, CommandeRepository $cr, ProduitRepository $pr, Produit $produit): Response 
    {
        $sid = $_ENV['TWILIO_ACCOUNT_SID'];
        $token = $_ENV['TWILIO_AUTH_TOKEN'];
        $twilioClient = new Client($sid, $token);
    
        $commande = new Commande();
        $commande->setDate(new DateTime());
        $commande->setStatut("En Attente");
        $commande->setTotale($produit->getPrix());
        $commande->setProduits($produit);
    
        $cr->save($commande, true);
    
        $toNumber = '+21626061821';
        $fromNumber = $_ENV['TWILIO_FROM_NUMBER'];
        $message = $twilioClient->messages->create(
            $toNumber,
            [
                'from' => $fromNumber,
                'body' => 'Votre commande a été ajoutée avec succès. Veuillez consulter l\'espace client. Merci pour votre fidélité!',
            ]
        );
    
        return $this->redirectToRoute('app_produit_index_front', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/pdf', name: 'app_commande_pdf', methods: ['GET'])]     
    public function AfficheTicketPDF(CommandeRepository $repo, $id)
    {
        $pdfoptions = new Options();
        $pdfoptions->set('defaultFont', 'Arial');
        $pdfoptions->setIsRemoteEnabled(true);
        

        $dompdf = new Dompdf($pdfoptions);

        $commandes = $repo->find($id);

        // Check if the commande exists
        if (!$commandes) {
            throw $this->createNotFoundException('Your commande does not exist');
        }

        $html = $this->renderView('commande/pdfExport.html.twig', [
            'commande' => $commandes
        ]);

        $html = '<div>' . $html . '</div>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A6', 'landscape');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        return new Response($pdfOutput, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="cabinetPDF.pdf"'
        ]);
    }
}
