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
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



#[Route('/quiz')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'app_quiz_index', methods: ['GET'])]
    public function index(Request $request, QuizRepository $quizRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sortField = $request->query->get('sort', 'name'); // Default sort by name
        $sortOrder = $request->query->get('order', 'asc'); // Default order asc

        $quizzes = $quizRepository->findBySearchAndSort($searchTerm, $sortField, $sortOrder);

        return $this->render('quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'searchTerm' => $searchTerm,
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
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
    public function quizList(Request $request, QuizRepository $quizRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $quizzes = $quizRepository->findBySearchTerm($searchTerm);

        return $this->render('front-quiz/index.html.twig', [
            'quizzes' => $quizzes,
            'searchTerm' => $searchTerm, // Pass search term to the template
        ]);
    }

    #[Route('/quiz//{id<\d+>}/take', name: 'app_front_quiz_take')]
    public function takeQuiz(int $id, QuizRepository $quizRepository, SessionInterface $session): Response
    {
        $quiz = $quizRepository->find($id);
    
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found!');
        }
    
        $questions = $quiz->getQuestions();
        
        if (count($questions) === 0) {
            $this->addFlash('error', 'This quiz has no questions.');
            return $this->redirectToRoute('app_front_quiz_index');
        }
    
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

        if ($question->getAnswers()->count() === 0) {
            throw new \Exception('No answers found for question ID: ' . $question->getId());
        }

        $form = $this->createForm(AnswerTypeFront::class, null, [
            'answers' => $question->getAnswers(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedAnswers = $form->get('answers')->getData();
            $correctAnswers = $question->getAnswers()->filter(fn($a) => $a->isIsCorrect()); 
            $correctCount = count(array_intersect(
                array_map(fn($a) => $a->getId(), $selectedAnswers),
                array_map(fn($a) => $a->getId(), $correctAnswers->toArray())
            ));

            if ($correctCount === count($correctAnswers) && count($selectedAnswers) === count($correctAnswers)) {
                $session->set('score', $session->get('score', 0) + 1);
            }

            return $this->redirectToRoute('app_front_quiz_question', ['index' => $index + 1]);
        }

        return $this->render('front-quiz/question.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
            'is_last_question' => ($index == count($questions) - 1),
            'current_index' => $index, 
        ]);
    }




    #[Route('/quiz/finish', name: 'app_front_quiz_finish')]
    public function finishQuiz(SessionInterface $session, MailerInterface $mailer): Response
    {
        $score = $session->get('score', 0);
        $totalQuestions = count($session->get('quiz_questions', []));
        $session->remove('quiz_questions');
        $session->remove('current_question_index');
        $session->remove('quiz_id');

        // Determine pass/fail status
        $passingScore = ceil($totalQuestions * 0.6); // Example: 60% to pass
        $status = ($score >= $passingScore) ? '✅ Congratulations! You passed the quiz.' : '❌ Sorry, you failed the quiz. Try again!';

        // Send email notification
        $recipientEmail = "baklouti.wassim@esprit.tn"; // Change this to your email for testing

        $email = (new Email())
            ->from('hammoudawassim696@gmail.com') // Change sender
            ->to($recipientEmail)
            ->subject('Quiz Completion Results')
            ->html("
                <h2>Quiz Results</h2>
                <p>You have completed the quiz.</p>
                <p><strong>Score:</strong> $score / $totalQuestions</p>
                <p><strong>Status:</strong> $status</p>
                <br>
                <p>Thank you for taking the quiz!</p>
            ");

        $mailer->send($email); // Send the email

        return $this->render('front-quiz/finish.html.twig', [
            'score' => $score,
            'total' => $totalQuestions,
        ]);
    }

    #[Route('/export/pdf', name: 'app_quiz_export_pdf')]
    public function exportPdf(QuizRepository $quizRepository): Response
    {
        // Retrieve all quizzes
        $quizzes = $quizRepository->findAll();

        // Render the HTML using our Twig template
        $html = $this->renderView('pdf/export.html.twig', [
            'quizzes' => $quizzes,
        ]);

        // Configure Dompdf according to your needs
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Get the generated PDF output
        $pdfOutput = $dompdf->output();

        // Return a PDF response (with appropriate headers)
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="quizzes.pdf"',
        ]);
    }





}
