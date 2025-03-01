<?php

        namespace App\Controller;

        use App\Entity\Comment;
        use App\Form\CommentType;
        use App\Repository\CommentRepository;
        use Doctrine\ORM\EntityManagerInterface;
        use Pagerfanta\Pagerfanta;
        use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
        use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
        use Symfony\Component\HttpFoundation\Request;
        use Symfony\Component\HttpFoundation\Response;
        use Symfony\Component\Routing\Attribute\Route;

        final class CommentController extends AbstractController
        {
            #[Route('/comment', name: 'app_comment')]
            public function index(): Response
            {
                return $this->render('comment/index.html.twig', [
                    'controller_name' => 'CommentController',
                ]);
            }

            #[Route('/comment/delete/{id}', name: 'comment_delete')]
            public function delete(Comment $comment,EntityManagerInterface $entityManager): Response
            {
                $postId = $comment->getIdPost()->getId();
                $comment->getIdPost()->setCommentCount($comment->getIdPost()->getCommentCount()-1);
                $entityManager->remove($comment);
                $entityManager->flush();

                return $this->redirectToRoute('post_show', ['id' => $postId]);
            }
            #[Route('/comment_back/delete/{id}', name: 'comment_back_delete')]
            public function delete_back(Comment $comment,EntityManagerInterface $entityManager): Response
            {
                $postId = $comment->getIdPost()->getId();
                $entityManager->remove($comment);
                $entityManager->flush();

                return $this->redirectToRoute('comment_list', ['id' => $postId]);
            }

            #[Route('/comment/edit/{id}', name: 'comment_edit')]
            public function edit(Comment $comment, Request $request, EntityManagerInterface $entityManager): Response
            {
                $form = $this->createForm(CommentType::class, $comment);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $postId = $comment->getIdPost()->getId();
                    $entityManager->flush();
                    return $this->redirectToRoute('post_show', ['id' => $postId]);
                }

                return $this->render('comment/edit.html.twig', [
                    'form' => $form->createView(),
                    'comment' => $comment,
                ]);
            }
            #[Route('/filter-comments-backoffice', name: 'filter_comments_backoffice', methods: ['POST'])]
            public function filterCommentsBackoffice(Request $request, CommentRepository $repository): \Symfony\Component\HttpFoundation\JsonResponse
            {
                $data = json_decode($request->getContent(), true);

                // Extract filter/search criteria
                $searchTerm = $data['search'] ?? '';
                $sortBy = $data['sortBy'] ?? '';
                $page = $data['page'] ?? 1; // Default to page 1
                $limit = $data['limit'] ?? 7; // Default to 7 comments per page

                // Fetch filtered/search results
                $query = $repository->createFilteredQueryBackoffice($searchTerm, $sortBy);

                // Paginate results using Pagerfanta
                $adapter = new DoctrineORMAdapter($query);
                $pagerfanta = new Pagerfanta($adapter);
                $pagerfanta->setMaxPerPage($limit);
                $pagerfanta->setCurrentPage($page);

                // Prepare response data
                $comments = [];
                foreach ($pagerfanta->getCurrentPageResults() as $comment) {
                    $comments[] = [
                        'id' => $comment->getId(),
                        'user' => [
                            'name' => $comment->getIdUser()->getName(),
                            'lastname' => $comment->getIdUser()->getLastname(),
                        ],
                        'content' => $comment->getContent(),
                        'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i'),
                    ];
                }

                return $this->json([
                    'comments' => $comments,
                    'pagination' => $this->renderView('pagerfanta/tailwind_pagination.html.twig', [
                        'pager' => $pagerfanta, // Pass the Pagerfanta object
                        'route' => 'filter_comments_backoffice',
                        'query' => ['search' => $searchTerm, 'sortBy' => $sortBy],
                        'pageParameterName' => 'page',
                    ]),
                ]);
            }
        }