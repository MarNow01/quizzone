<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use App\Repository\QuestionRepository;
use App\Repository\CommentRepository;
use App\Repository\OpinionRepository;
use App\Repository\UserRepository;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Comment;
use App\Entity\Opinion;
use App\Entity\Category;
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
                'isTrueOrFalse' => $question->isTrueOrFalse(),
                'isOpen' => $question->isOpen(),
                'content' => $question->getContent(),
                'answerA' => $question->getAnswerA(),
                'answerB' => $question->getAnswerB(),
                'answerC' => $question->getAnswerC(),
                'answerD' => $question->getAnswerD(),
                'timeLimit' => $question->getTimeLimit(),
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

    #[Route('/api/quizinfo/{id}', name: 'api_quiz_info', methods: ['GET'])]
    public function info(int $id, EntityManagerInterface $entityManager, OpinionRepository $opinionRepository): JsonResponse
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quizu.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $category = null;
        if($quiz->getCategory())$category = $quiz->getCategory()->getName();

        $opinions = $opinionRepository->findBy(['quiz'=>$quiz]);

        $opinionssum = array_reduce($opinions, function($carry, $opinion){
            return $carry + $opinion->getValue();
        },0);

        if (count($opinions) == 0){
            $averageOpinion = 0;
        }
        else{
            $averageOpinion = $opinionssum/count($opinions);
        }
        

        return new JsonResponse([
            'quiz' => [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'author' => $quiz->getAuthor()->getUsername(),
                'category' => $category,
                'averageOpinion' => $averageOpinion,
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
        $category = $entityManager->getRepository(Category::class)->find($data['categoryId']);
        $quiz->setCategory($category);
        $quiz->setAuthor($user);
        $quiz->setDateOfCreation(new \DateTime());

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

        if (empty($data['content']) || empty($data['correctAnswer']) || empty($data['type'])) {
            return new JsonResponse(['error' => 'Nie podano wszystkich wymaganych pól.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $question = new Question();
        if($data['type'] == "true-false"){
            $question->setAnswerA($data['answerA']);
            $question->setAnswerB($data['answerB']);
            $question->setTrueOrFalse(true);
            $question->setOpen(false);
        }
        else if($data['type'] == "open"){
            $question->setTrueOrFalse(false);
            $question->setOpen(true);
        }
        else{
            $question->setAnswerA($data['answerA']);
            $question->setAnswerB($data['answerB']);
            $question->setAnswerC($data['answerC']);
            $question->setAnswerD($data['answerD']);
            $question->setTrueOrFalse(false);
            $question->setOpen(false);
        }
        
        if($data['timer']){
            $question->setTimeLimit($data['timer']);
        }
        $question->setContent($data['content']);
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
        $category = $entityManager->getRepository(Category::class)->find($data['categoryId']);
        $quiz->setCategory($category);
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

    #[Route('/api/quiz/{id}/comment', name: 'add_comment', methods: ['POST'])]
    public function addComment(int $id, Request $request, QuizRepository $quizRepository, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse 
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, napisać komentarz.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return new JsonResponse(['error' => 'Brak wymaganych pól'], 400);
        }

        $quiz = $quizRepository->find($id);

        if (!$quiz || !$user) {
            return new JsonResponse(['error' => 'Błędny quiz lub autor'], 404);
        }

        $comment = new Comment();
        $comment->setContent($data['content'])
                ->setAuthor($user)
                ->setQuiz($quiz)
                ->setDateOfCreation(new \DateTime());

        $em->persist($comment);
        $em->flush();

        return new JsonResponse([
            'id' => $comment->getId(),
            'content' => $comment->getContent(),
            'authorId' => $user->getId(),
            'quizId' => $quiz->getId(),
            'dateOfCreation' => $comment->getDateOfCreation()->format('Y-m-d H:i:s')
        ], 201);
    }

    #[Route('/api/comment/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment(int $id, EntityManagerInterface $em, CommentRepository $commentRepository): JsonResponse 
    {
        $user = $this -> getUser();
        if ($user != $comment->getAuthor()){
            return new JsonResponse(['error' => 'Zalogowany uzytkownik nie może usunąć tego komentarza.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $comment = $commentRepository->find($id);
        if (!$comment) {
            return new JsonResponse(['error' => 'Nie znaleziono komentarza'], 404);
        }

        $em->remove($comment);
        $em->flush();

        return new JsonResponse(['message' => 'Usunięto komentarz'], 200);
    }

    #[Route('/api/quiz/{id}/getcomments', name: 'get_comments', methods: ['GET'])]
    public function getComments(int $id, QuizRepository $quizRepository, CommentRepository $commentRepository): JsonResponse
    {

        $quiz = $quizRepository->find($id);

        if (!$quiz) {
            return new JsonResponse(['error' => 'Nie znaleziono quizu'], 404);
        }

        $comments = $commentRepository->findBy(['quiz' => $quiz]);

        $commentsData = array_map(function ($comment) {
            return [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'authorName' => $comment->getAuthor()->getUsername(),
                'dateOfCreation' => $comment->getDateOfCreation()->format('Y-m-d H:i:s')
            ];
        }, $comments);

        return new JsonResponse($commentsData, 200);
    }
}
