<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/', name: 'api')]
    public function hello(): Response
    {
        return $this->render('user/hello.html.twig', [
            'message' => 'Hello World',
        ]);
    }

    #[Route('/api/user', name: 'api_user', methods: ['GET'])]
    public function user(): JsonResponse
    {
        // Uzyskiwanie aktualnie zalogowanego użytkownika
        $user = $this->getUser();

        // Jeśli użytkownik nie jest zalogowany, zwróć błąd
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Zwróć dane użytkownika w formacie JSON
        return new JsonResponse(['username' => $user->getUsername()]);
    }

    #[Route('/api/quizes', name: 'api_quizes', methods: ['GET'])]
    public function quizes(): JsonResponse
    {
        return new JsonResponse(['quiz' => ["test abc", "prawko360"]]);
    }
}
