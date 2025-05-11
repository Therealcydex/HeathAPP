<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;



#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository, 
        Request $request, 
        PaginatorInterface $paginator): Response
    {
        $data=$produitRepository->findAll();
        $products=$paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            5
        );
        return $this->render('produit/index.html.twig', [
            'produits' => $products,
        ]);
    }

    #[Route('/front', name: 'app_produit_index_front', methods: ['GET'])]
    public function indexFront(ProduitRepository $produitRepository,
            Request $request, 
            PaginatorInterface $paginator): Response
    {
        $data=$produitRepository->findAll();

        $products=$paginator->paginate(
            $data,
            $request->query->getInt('page',1),
            6
        );
        return $this->render('produit/indexFront.html.twig', [
            'produits' => $products,
        ]);
	
    
    }


    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProduitRepository $pr): Response
    {
        $produit = new Produit();
        $filesystem = new Filesystem();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filter_nom = $this->filterwords($produit->getNom());
            $filter_description = $this->filterwords($produit->getDescription());

            $produit->setNom($filter_nom);
            $produit->setDescription($filter_description);
            $pr->save($produit, true);
            
            $uploadedFile = $form->get('image')->getData();
            $formData =  $uploadedFile->getPathname();
            $sourcePath = strval($formData);
            $destinationPath = 'uploads/'.strval($produit->getId()).'.png';
            $produit->setImage($destinationPath);
            $filesystem->copy($sourcePath, $destinationPath);

            $pr->save($produit, true);
            

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
    public function filterwords($text)
    {
        $filterWords = array('fokaleya', 'bhim', 'msatek', 'slut');
        $filterCount = count($filterWords);
        $str = "";
        $data = preg_split('/\s+/',  $text);
        foreach($data as $s){
            $g = false;
            foreach ($filterWords as $lib) {
                if($s == $lib){
                    $t = "";
                    for($i =0; $i<strlen($s); $i++) $t .= "*";
                    $str .= $t . " ";
                    $g = true;
                    break;
                }
            }
            if(!$g)
            $str .= $s . " ";
        }
        return $str;
    }
}
