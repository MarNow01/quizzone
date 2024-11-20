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
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik niezalogowany'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['user' => [
            'username' => $user->getUsername(),
            'profilePicture' => $user->getProfilePicture(),
            'points' => $user->getPoints(),
            ]
        ]);
    }

    #[Route('/api/user/profile-picture', name: 'api_user_profile_picture', methods: ['PUT'])]
    public function editProfilePicture(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik niezalogowany'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $profilePicture = $data['profilePicture'] ?? null;

        if ($profilePicture < 1 || $profilePicture > 6) {
            return new JsonResponse(['error' => 'Wartość profilePicture musi być od 1 do 6'], Response::HTTP_BAD_REQUEST);
        }

        $user->setProfilePicture($profilePicture);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['success' => 'Zdjęcie profilowe zostało zaktualizowane'], Response::HTTP_OK);
    }
}
