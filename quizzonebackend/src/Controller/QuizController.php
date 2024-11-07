<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use App\Repository\QuestionRepository;
use App\Entity\Quiz;
use App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class QuizController extends AbstractController
{
    #[Route('/api/quizes', name: 'api_quizzes', methods: ['GET'])]
    public function quizzes(QuizRepository $quizRepository): JsonResponse
    {
        $quizzes = $quizRepository->findAll();
        
        $data = [];
        foreach ($quizzes as $quiz) {
            $category = null;
            if($quiz->getCategory())$category = $quiz->getCategory()->getName();
            $data[] = [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'author_name' => $quiz->getAuthor()->getUsername(),
                'category' => $category,
                'created' => $quiz->getDateOfCreation(),
            ];
        }

        return new JsonResponse(['quizes' => $data], Response::HTTP_OK);
    }

    #[Route('/api/userquizes', name: 'api_user_quizes', methods: ['GET'])]
    public function userquizzes(QuizRepository $quizRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by zobaczyć swoje quizy.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $quizzes = $quizRepository->findBy(['author' => $user]);
        
        $data = [];
        foreach ($quizzes as $quiz) {
            $category = null;
            if($quiz->getCategory())$category = $quiz->getCategory()->getName();
            $data[] = [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'category' => $category,
                'created' => $quiz->getDateOfCreation(),
            ];
        }

        return new JsonResponse(['quizes' => $data], Response::HTTP_OK);
    }

    #[Route('/api/quiz/{id}', name: 'api_quiz_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quizu.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $category = null;
        if($quiz->getCategory())$category = $quiz->getCategory()->getName();

        $questions = [];
        foreach ($quiz->getQuestions() as $question) {
            $questions[] = [
                'id' => $question->getId(),
                'content' => $question->getContent(),
                'answerA' => $question->getAnswerA(),
                'answerB' => $question->getAnswerB(),
                'answerC' => $question->getAnswerC(),
                'answerD' => $question->getAnswerD(),
                'correctAnswer' => $question->getCorrectAnswer(),
            ];
        }

        return new JsonResponse([
            'quiz' => [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'author' => $quiz->getAuthor()->getUsername(),
                'category' => $category,
                'questions' => $questions,
            ]
        ]);
    }

    #[Route('/api/quiz/{id}', name: 'api_quiz_delete', methods: ['DELETE'])]
    public function deleteQuiz(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quizu.'], JsonResponse::HTTP_NOT_FOUND);
        }
        
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by usunąć quiz'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        if($quiz->getAuthor() != $user){
            return new JsonResponse(['error' => 'Tylko autor quizu może go usunąć '], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $entityManager->remove($quiz);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz usunięty pomyślnie.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/quiz/new', name: 'api_quiz_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by utworzyć quiz.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse(['error' => 'Nazwa quizu jest wymagana.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $quiz = new Quiz();
        $quiz->setName($data['name']);
        $quiz->setAuthor($user);
        $quiz->setDateOfCreation(new \DateTime()); // Ustawienie daty na aktualną datę i czas

        $entityManager->persist($quiz);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Quiz created successfully.',
            'quiz' => [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'author' => $quiz->getAuthor()->getUsername(),
                'created' => $quiz->getDateOfCreation()->format('Y-m-d H:i:s'), // Formatowanie daty do odpowiedniego formatu
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/quiz/{id}/add-question', name: 'api_add_question', methods: ['POST'])]
    public function addQuestion(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by dodać pytanie.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Wyszukiwanie quizu po ID
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quozu.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['content']) || empty($data['answerA']) || empty($data['answerB']) || empty($data['correctAnswer'])) {
            return new JsonResponse(['error' => 'Nie podano wszystkich wymaganych pól.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $question = new Question();
        $question->setContent($data['content']);
        $question->setAnswerA($data['answerA']);
        $question->setAnswerB($data['answerB']);
        $question->setAnswerC($data['answerC']);
        $question->setAnswerD($data['answerD']);
        $question->setCorrectAnswer($data['correctAnswer']);
        $question->setQuiz($quiz);

        $entityManager->persist($question);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Pytanie dodane pomyślnie.',
            'question' => [
                'id' => $question->getId(),
                'content' => $question->getContent(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/quiz/{id}', name: 'api_quiz_update', methods: ['PUT'])]
    public function updateQuiz(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quizu.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse(['error' => 'Nazwa quizu jest wymagana.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $quiz->setName($data['name']);
        $quiz->setDateOfCreation(new \DateTime());

        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz zmodyfikowany pomyślnie.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/quiz/{quizId}/questions/{questionId}', name: 'api_question_update', methods: ['PUT'])]
    public function updateQuestion(int $quizId, int $questionId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $question = $entityManager->getRepository(Question::class)->find($questionId);

        if (!$question || $question->getQuiz()->getId() !== $quizId) {
            return new JsonResponse(['error' => 'Nie znaleziono pytania.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['content'])) {
            return new JsonResponse(['error' => 'Treść pytania jest wymagana.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $question->setContent($data['content']);
        $question->setAnswerA($data['answerA']);
        $question->setAnswerB($data['answerB']);
        $question->setAnswerC($data['answerC']);
        $question->setAnswerD($data['answerD']);
        $question->setCorrectAnswer($data['correctAnswer']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Pytanie zmodyfiokowane pomyślnie.'], JsonResponse::HTTP_OK);
    }

}
