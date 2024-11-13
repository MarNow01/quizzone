<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\AttemptQuiz;
use App\Entity\AttemptQuestion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

class AttemptQuizController extends AbstractController
{
    #[Route('/api/attemptquiz/{id}', name: 'api_attempt_quiz', methods: ['GET'])]
    public function attemptQuiz(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $attemptQuiz = $entityManager->getRepository(AttemptQuiz::class)->find($id);
        if (!$attemptQuiz) {
            return new JsonResponse(['error' => 'Błąd pobierania pytania.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $questions = [];
        foreach ($attemptQuiz -> getAttemptQuestions() as $question) {
            $questions[] = [
                'id' => $question->getId(),
                'image' => $question->getQuestion()->getImage() ? $question->getImage():null,
                'content' => $question->getQuestion()->getContent(),
                'answerA' => $question->getQuestion()->getAnswerA(),
                'answerB' => $question->getQuestion()->getAnswerB(),
                'answerC' => $question->getQuestion()->getAnswerC(),
                'answerD' => $question->getQuestion()->getAnswerD(),
            ];
        }

        return new JsonResponse([
            'attemptQuiz' => [
                'id' => $attemptQuiz -> getId(),
                'name' => $attemptQuiz -> getQuiz() -> getName(),
                'questions' => $questions,
            ]
        ]);
    }

    #[Route('/api/score/{id}', name: 'api_score', methods: ['GET'])]
    public function score(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $attemptQuiz = $entityManager->getRepository(AttemptQuiz::class)->find($id);
        if (!$attemptQuiz) {
            return new JsonResponse(['error' => 'Błąd sprawdzania wyniku.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $correct = 0;
        $incorrect = 0;
        $notAnswered = 0;
        $all = 0;

        foreach ($attemptQuiz -> getAttemptQuestions() as $attemptQuestion) {
            if($attemptQuestion -> getAnsweredAnswer() == null){
                $notAnswered ++;
            }
            else if($attemptQuestion -> getAnsweredAnswer() == $attemptQuestion -> getQuestion() -> getCorrectAnswer()){
                $correct ++;
            }
            else{
                $incorrect ++;
            }
            $all++;
        }

        return new JsonResponse([
            'score' => [
                'correct' => $correct,
                'incorrect' => $incorrect,
                'notAnswered' => $notAnswered,
                'all' => $all,
                'quizId' => $attemptQuiz->getQuiz()->getId(),
            ]
        ]);
    }

    #[Route('/api/startquiz/{id}', name: 'api_start_quiz', methods: ['POST'])]
    public function startQuiz(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by rozwiązywać quiz.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

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
            'message' => 'Użytkownik podjął próbę rozwiązania quizu.',
            'id_attempt' => $attemptQuiz->getId(),
            'name' => $attemptQuiz->getQuiz()->getName(),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/answerToOne/{id}', name: 'api_answerToOne', methods: ['POST'])]
    public function answerToOne(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by odpowiedzieć.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $attemptQuestion = $entityManager->getRepository(AttemptQuestion::class)->find($id);
        if (!$attemptQuestion) {
            return new JsonResponse(['error' => 'Błąd bazy danych - nie znaleziono attemptQuestiona o tym ID.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user != $attemptQuestion->getAttemptQuiz()->getUser()) {
            return new JsonResponse(['error' => 'Zalogowany uzytkownik nie ma dostępu do tego podejścia.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        //Odebranie odpowiedzi 
        $data = json_decode($request->getContent(), true);

        if (empty($data['answer'])) {
            return new JsonResponse(['error' => 'Wartość "answer" jest wymagana'], JsonResponse::HTTP_BAD_REQUEST);
        }

        //Zapisanie odpowiedzi
        $attemptQuestion->setAnsweredAnswer($data['answer']);
        $entityManager->persist($attemptQuestion);
        $entityManager->flush();

        //sprawdzanie odpwowiedzi
        $answer = '';
        if($attemptQuestion->getAnsweredAnswer() == $attemptQuestion->getQuestion()->getCorrectAnswer()){
            $answer = 'Poprawna odpowiedź';
        }
        else{
            $answer = 'Niepoprawna odpowiedź';
        }

        return new JsonResponse([
            'results' => [
                'answer' => $answer,
            ]
        ]);
    }

    #[Route('/api/answerToAll/{id}', name: 'api_answerToAll', methods: ['POST'])]
    public function answerToAll(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by odpowiedzieć.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $attemptQuiz = $entityManager->getRepository(attemptQuiz::class)->find($id);
        if (!$attemptQuiz) {
            return new JsonResponse(['error' => 'Błąd bazy danych - nie znaleziono attemptQuiz o tym ID.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!$user != $attemptQuiz()->getUser()) {
            return new JsonResponse(['error' => 'Zalogowany uzytkownik nie ma dostępu do tego podejścia.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Odebranie odpowiedzi 
        $data = json_decode($request->getContent(), true);

        if (empty($data['answers']) || !is_array($data['answers'])) {
            return new JsonResponse(['error' => 'Wartość "answers" jest wymagana. Poprawny format: "answers" => [{"id": int, "answer": string}].'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Wczytanie attemptQuestionów z bazy
        $attemptQuestions = $attemptQuiz->getAttemptQuestions();

        // Przetwarzanie i zapisywanie odpowiedzi
        foreach ($data['answers'] as $answerData) {
            $attemptQuestionId = $answerData['id'] ?? null;
            $answer= $answerData['answer'] ?? null;

            if (!$attemptQuestionId || !$answer) {
                continue;
            }

            // Znalezienie odpowiedniego attemptQuestion na podstawie ID
            foreach ($attemptQuestions as $attemptQuestion) {
                if ($attemptQuestion->getId() === $attemptQuestionId) {
                    $attemptQuestion->setAnsweredAnswer($answer);
                    $entityManager->persist($attemptQuestion);
                    break;
                }
            }
        }

        $entityManager->flush();

        return new JsonResponse([
            'result' => "Odpowiedzi zapisane",
        ]);
    }
}
