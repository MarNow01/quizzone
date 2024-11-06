<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AttemptQuizController extends AbstractController
{
    #[Route('/api/startquiz/{id}', name: 'api_start_quiz', methods: ['POST'])]
    public function startQuiz(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, rozwiązywać quiz.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Wyszukiwanie quizu po ID
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quozu.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $attemptQuiz = new attemptQuiz();
        $attemptQuiz -> setUser($user);
        $attemptQuiz -> setQuiz($quiz);
        $attemptQuiz->setDateOfCreation(new \DateTime());
        
    
        foreach ($quiz->getQuestions() as $question) {
            $attemptQuestion = new AttemptQuestion();
            $attemptQuestion->setQuestion($question);
            $attemptQuestion -> setAttemptQuiz($attemptQuiz);
            $attemptQuestion->setDateOfCreation(new \DateTime());
            $entityManager->persist($attemptQuestion);
        }
        
        $entityManager->persist($attemptQuiz);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Użytkownik podjął próbę rozwiązania quizu.'
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/attemptquiz/{id}', name: 'api_attempt_quiz', methods: ['GET'])]
    public function attemptQuiz(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $attemptQuiz = $entityManager->getRepository(AttemptQuiz::class)->find($id);
        if (!$attemptQuiz) {
            return new JsonResponse(['error' => 'Błąd pobierania pytania.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $questions = [];
        foreach ($attemptQuiz -> getQuiz() -> getQuestions() as $question) {
            $questions[] = [
                'id' => $question->getId(),
                'image' => $question->getImage() ? $question->getImage():null,
                'content' => $question->getContent(),
                'answerA' => $question->getAnswerA(),
                'answerB' => $question->getAnswerB(),
                'answerC' => $question->getAnswerC(),
                'answerD' => $question->getAnswerD(),
            ];
        }

        return new JsonResponse([
            'attemptQuiz' => [
                'id' => $attemptQuiz -> getId(),
                'name' => $attemmptQuiz -> getQuiz() -> getName(),
                'questions' => $questions,
            ]
        ]);
    }
}
