<?php

namespace App\Controller;
use App\Service\TranslationService;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
final class PostController extends AbstractController
{
    #[Route('/backoffice/posts', name: 'backoffice_posts')]
    public function backofficePosts(PostRepository $postRepository,Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $limit = 7;

        $queryBuilder = $postRepository->createQueryBuilder('p');

        $adapter = new DoctrineORMAdapter($queryBuilder);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        $query = $request->query->all();
        unset($query['page']);
        return $this->render('backoffice/posts.html.twig', [
            'posts' => $pagerfanta,
            'query' => $query,
        ]);
    }
    #[Route('/forum', name: 'app_forum')]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get the current page from the request (default to 1 if not provided)
        $page = $request->query->getInt('page', 1);

        $limit = 7;

        $queryBuilder = $postRepository->createQueryBuilder('p');

        $adapter = new DoctrineORMAdapter($queryBuilder);

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        $query = $request->query->all();
        unset($query['page']);

        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $user = $entityManager->getRepository(User::class)->find(2);
            if ($user) {
                $post->setAuthor($user);
            }
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum');
        }

        return $this->render('forum/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $pagerfanta,
            'query' => $query,
        ]);
    }
    #[Route('/post/{id}', name: 'post_show')]
    public function show(Post $post, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        $comments = $post->getComments();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setIdPost($post);
            $user = $entityManager->getRepository(User::class)->find(1);
            if ($user) {
                $comment->setIdUser($user);
            }
            $post->setCommentCount($post->getCommentCount()+1);
            $comment->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('forum/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/post_back/{id}', name: 'comment_list')]
    public function getcomment(Post $post): Response
    {
        $comments = $post->getComments();

        return $this->render('backoffice/comments.html.twig', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }
    #[Route('/post/delete/{id}', name: 'post_delete')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }
    #[Route('/post_back/delete/{id}', name: 'post_back_delete')]
    public function delete_back(Post $post, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('backoffice_posts');
    }
    #[Route('/post/edit/{id}', name: 'post_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_forum');
        }

        return $this->render('post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    private $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }


    #[Route('/translate', name: 'translate_text')]

    public function translateText(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $texts = $data['texts']; // Array of texts to translate
        $targetLanguage = $data['targetLanguage'];

        $translatedTexts = $this->translationService->translate($texts, $targetLanguage);

        return new JsonResponse(['translatedTexts' => $translatedTexts]);
    }
    #[Route('/filter-posts', name: 'filter_posts')]
// src/Controller/YourController.php

    public function filterPosts(Request $request, PostRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Extract filter/search criteria
        $filters = $data['filters'] ?? [];
        $searchTerm = $data['search'] ?? '';
        $page = $data['page'] ?? 1; // Default to page 1
        $limit = $data['limit'] ?? 7; // Default to 7 posts per page

        // Fetch filtered/search results
        $query = $repository->createFilteredQuery($filters, $searchTerm);

        // Paginate results using Pagerfanta
        $adapter = new DoctrineORMAdapter($query);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        // Prepare response data
        $posts = [];
        foreach ($pagerfanta->getCurrentPageResults() as $post) {
            $posts[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'category' => $post->getCategory(),
                'author' => [
                    'name' => $post->getAuthor()->getName(),
                    'lastname' => $post->getAuthor()->getLastname(),
                    'role' => $post->getAuthor()->getRole(),
                ],
                'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json([
            'posts' => $posts,
            'pagination' => $this->renderView('pagerfanta/tailwind_pagination.html.twig', [
                'pager' => $pagerfanta, // Pass the Pagerfanta object
                'route' => 'filter_posts',
                'query' => array_merge($filters, ['search' => $searchTerm]),
                'pageParameterName' => 'page',
            ]),
        ]);
    }
#[Route('/filter-posts-backoffice', name: 'filter_posts_backoffice', methods: ['POST'])]
    public function filterPostsBackoffice(Request $request, PostRepository $repository): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    // Extract filter/search criteria
    $searchTerm = $data['search'] ?? '';
    $sortBy = $data['sortBy'] ?? '';
    $page = $data['page'] ?? 1; // Default to page 1
    $limit = $data['limit'] ?? 7; // Default to 7 posts per page

    // Fetch filtered/search results
    $query = $repository->createFilteredQueryBackoffice($searchTerm, $sortBy);

    // Paginate results using Pagerfanta
    $adapter = new DoctrineORMAdapter($query);
    $pagerfanta = new Pagerfanta($adapter);
    $pagerfanta->setMaxPerPage($limit);
    $pagerfanta->setCurrentPage($page);

    // Prepare response data
    $posts = [];
    foreach ($pagerfanta->getCurrentPageResults() as $post) {
        $posts[] = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'author' => [
                'name' => $post->getAuthor()->getName(),
                'lastname' => $post->getAuthor()->getLastname(),
            ],
            'createdAt' => $post->getCreatedAt()->format('Y-m-d H:i'),
            'commentCount' => $post->getCommentCount(),
        ];
    }

    return $this->json([
        'posts' => $posts,
        'pagination' => $this->renderView('pagerfanta/tailwind_pagination.html.twig', [
            'pager' => $pagerfanta, // Pass the Pagerfanta object
            'route' => 'filter_posts_backoffice',
            'query' => ['search' => $searchTerm, 'sortBy' => $sortBy],
            'pageParameterName' => 'page',
        ]),
    ]);
}
}