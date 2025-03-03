<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/question')]
class QuestionController extends AbstractController
{
    #[Route('/', name: 'app_question_index', methods: ['GET'])]
    public function index(Request $request, QuestionRepository $questionRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sortField = $request->query->get('sort', 'q.text');
        $sortOrder = $request->query->get('order', 'asc');
        $page = max(1, (int) $request->query->get('page', 1));

        $query = $questionRepository->findBySearchAndSortQuery($searchTerm, $sortField, $sortOrder);
        $pagination = $paginator->paginate($query, $page, 2);

        return $this->render('question/index.html.twig', [
            'questions' => $pagination,
            'searchTerm' => $searchTerm,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'pagination' => [
                'currentPage' => $pagination->getCurrentPageNumber(),
                'totalPages' => $pagination->getPageCount(),
            ]
        ]);
    }

    #[Route('/search', name: 'app_question_search', methods: ['GET'])]
    public function search(Request $request, QuestionRepository $questionRepository, PaginatorInterface $paginator): JsonResponse
    {
        $searchTerm = $request->query->get('search', '');
        $sortField = $request->query->get('sort', 'q.text');
        $sortOrder = $request->query->get('order', 'asc');
        $page = max(1, (int) $request->query->get('page', 1));

        $query = $questionRepository->findBySearchAndSortQuery($searchTerm, $sortField, $sortOrder);
        $pagination = $paginator->paginate($query, $page, 2);

        return new JsonResponse([
            'html' => $this->renderView('question/_question_list.html.twig', [
                'questions' => $pagination,
                'sortField' => $sortField,
                'sortOrder' => $sortOrder,
                'searchTerm' => $searchTerm,
                'pagination' => [
                    'currentPage' => $pagination->getCurrentPageNumber(),
                    'totalPages' => $pagination->getPageCount(),
                ]
            ]),
            'pagination' => $this->renderView('question/_pagination.html.twig', [
                'pagination' => [
                    'currentPage' => $pagination->getCurrentPageNumber(),
                    'totalPages' => $pagination->getPageCount(),
                ],
                'searchTerm' => $searchTerm,
                'sortField' => $sortField,
                'sortOrder' => $sortOrder,
            ])
        ]);
    }

    #[Route('/new', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('question/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
    }
}