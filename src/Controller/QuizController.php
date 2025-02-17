<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Form\AnswerTypeFront;
use App\Entity\Question;



#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_quiz_index', methods: ['GET'])]
    public function index(QuizRepository $quizRepository): Response
    {
        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($quiz);
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('quiz/new.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }

    #[Route('//{id<\d+>}', name: 'app_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quiz);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_quiz_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/quizList', name: 'app_front_quiz_index')]
    public function quizList(QuizRepository $quizRepository): Response
    {
        return $this->render('front-quiz/index.html.twig', [
            'quizzes' => $quizRepository->findAll(),
        ]);
    }

    #[Route('/quiz//{id<\d+>}/take', name: 'app_front_quiz_take')]
    public function takeQuiz(int $id, QuizRepository $quizRepository, SessionInterface $session): Response
    {
        $quiz = $quizRepository->find($id);
    
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }
    
        // Get questions
        $questions = $quiz->getQuestions();
        
        if (count($questions) === 0) {
            $this->addFlash('error', 'This quiz has no questions.');
            return $this->redirectToRoute('app_front_quiz_index');
        }
    
        // Store questions in session to track progress
        $session->set('quiz_questions', $questions);
        $session->set('current_question_index', 0);
        $session->set('quiz_id', $quiz->getId());
        $session->set('score', 0);
    
        return $this->redirectToRoute('app_front_quiz_question');
    }
    

    #[Route('/quiz/question/{index}', name: 'app_front_quiz_question', defaults: ['index' => 0])]
    public function showQuestion(SessionInterface $session, Request $request, int $index): Response
    {
        $questions = $session->get('quiz_questions', []);

        // If quiz is finished, redirect to results page
        if ($index >= count($questions)) {
            return $this->redirectToRoute('app_front_quiz_finish');
        }

        $question = $this->getDoctrine()->getRepository(Question::class)
            ->createQueryBuilder('q')
            ->leftJoin('q.answers', 'a')
            ->addSelect('a')
            ->where('q.id = :id')
            ->setParameter('id', $questions[$index]->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$question) {
            throw $this->createNotFoundException('Question not found');
        }

        // Check if answers exist
        if ($question->getAnswers()->count() === 0) {
            throw new \Exception('No answers found for question ID: ' . $question->getId());
        }

        // ✅ Pass the possible answers to the form
        $form = $this->createForm(AnswerTypeFront::class, null, [
            'answers' => $question->getAnswers(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedAnswers = $form->get('answers')->getData(); // ✅ Get selected answers
            $correctAnswers = $question->getAnswers()->filter(fn($a) => $a->isIsCorrect()); // ✅ Get correct answers

            // Compare selected answers with correct ones
            $correctCount = count(array_intersect(
                array_map(fn($a) => $a->getId(), $selectedAnswers),
                array_map(fn($a) => $a->getId(), $correctAnswers->toArray())
            ));

            if ($correctCount === count($correctAnswers) && count($selectedAnswers) === count($correctAnswers)) {
                $session->set('score', $session->get('score', 0) + 1);
            }

            // Move to next question
            return $this->redirectToRoute('app_front_quiz_question', ['index' => $index + 1]);
        }

        return $this->render('front-quiz/question.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
            'is_last_question' => ($index == count($questions) - 1),
            'current_index' => $index, // ✅ Track current index
        ]);
    }




    #[Route('/quiz/finish', name: 'app_front_quiz_finish')]
    public function finishQuiz(SessionInterface $session): Response
    {
        $score = $session->get('score', 0);
        $totalQuestions = count($session->get('quiz_questions', []));

        // Clear session
        $session->remove('quiz_questions');
        $session->remove('current_question_index');
        $session->remove('quiz_id');

        return $this->render('front-quiz/finish.html.twig', [
            'score' => $score,
            'total' => $totalQuestions,
        ]);
    }





}
