<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/', name: 'app_course')]
    public function hello(): Response
    {
        return $this->render('user/hello.html.twig', [
            'message' => 'Hello World',
        ]);
    }
    #[Route('/user', name: 'app_user', methods: ['GET'])]
    //#[IsGranted('IS_AUTHENTICATED_FULLY')] // Tylko dla zalogowanych użytkowników
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
}