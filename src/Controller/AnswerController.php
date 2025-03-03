<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/answer')]
class AnswerController extends AbstractController
{
    #[Route('/', name: 'app_answer_index', methods: ['GET'])]
    public function index(Request $request, AnswerRepository $answerRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sortField = $request->query->get('sort', 'a.text');
        $sortOrder = $request->query->get('order', 'asc');
        $page = max(1, (int) $request->query->get('page', 1));

        // Fetch query results
        $query = $answerRepository->findBySearchAndSortQuery($searchTerm, $sortField, $sortOrder);

        // Paginate results (10 items per page)
        $pagination = $paginator->paginate($query, $page, 10);

        return $this->render('answer/index.html.twig', [
            'answers' => $pagination,
            'searchTerm' => $searchTerm,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'totalPages' => $pagination->getPageCount(),
            ]
        ]);
    }

    #[Route('/search', name: 'app_answer_search', methods: ['GET'])]
    public function search(Request $request, AnswerRepository $answerRepository, PaginatorInterface $paginator): JsonResponse
    {
        $searchTerm = $request->query->get('search', '');
        $sortField = $request->query->get('sort', 'a.text');
        $sortOrder = $request->query->get('order', 'asc');
        $page = max(1, (int) $request->query->get('page', 1));

        $query = $answerRepository->findBySearchAndSortQuery($searchTerm, $sortField, $sortOrder);
        $pagination = $paginator->paginate($query, $page, 10);

        return new JsonResponse([
            'html' => $this->renderView('answer/_answer_list.html.twig', [
                'answers' => $pagination,
                'sortField' => $sortField,
                'sortOrder' => $sortOrder,
                'searchTerm' => $searchTerm,
                'pagination' => [
                    'currentPage' => $pagination->getCurrentPageNumber(),
                    'totalPages' => $pagination->getPageCount(),
                ]
            ])
        ]);
    }

    #[Route('/new', name: 'app_answer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($answer);
            $entityManager->flush();

            return $this->redirectToRoute('app_answer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('answer/new.html.twig', [
            'answer' => $answer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_answer_show', methods: ['GET'])]
    public function show(Answer $answer): Response
    {
        return $this->render('answer/show.html.twig', [
            'answer' => $answer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_answer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_answer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('answer/edit.html.twig', [
            'answer' => $answer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_answer_delete', methods: ['POST'])]
    public function delete(Request $request, Answer $answer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$answer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($answer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_answer_index', [], Response::HTTP_SEE_OTHER);
    }
}
