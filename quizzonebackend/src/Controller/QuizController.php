<?php

namespace App\Controller;

use App\Repository\QuizRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuizController extends AbstractController
{
    #[Route('/api/quizes', name: 'api_quizzes', methods: ['GET'])]
    public function quizzes(QuizRepository $quizRepository): JsonResponse
    {
        // Pobranie wszystkich quizów
        $quizzes = $quizRepository->findAll();
        
        // Przygotowanie tablicy danych do zwrócenia
        $data = [];
        foreach ($quizzes as $quiz) {
            $data[] = [
                'id' => $quiz->getId(), // Załóżmy, że masz metodę getId()
                'name' => $quiz->getName(),
                'created' => $quiz->getDateOfCreation(),
            ];
        }

        // Zwrócenie odpowiedzi JSON
        return new JsonResponse(['quizes' => $data], Response::HTTP_OK);
    }

}
