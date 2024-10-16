<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface; // Import loggera

class ApiRegistrationController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger): JsonResponse
    {
        $logger->warning('Odpalam akcje rejestracji');
        $data = json_decode($request->getContent(), true);

        // Walidacja danych
        if (empty($data['username']) || empty($data['password'])) {
            $logger->warning('Registration attempt with missing username or password', [
                'data' => $data
            ]);
            return new JsonResponse(['message' => 'Username and password are required'], 400);
        }

        // Sprawdzenie, czy użytkownik już istnieje
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            $logger->warning('Registration attempt with existing username', [
                'username' => $data['username']
            ]);
            return new JsonResponse(['message' => 'Username already exists'], 409); // Conflict
        }

        // Tworzenie nowego użytkownika
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT)); // Haszowanie hasła
        $logger->info('Creating a new user', [
            'username' => $data['username']
        ]);

        // Walidacja użytkownika
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $logger->error('Validation failed', [
                'errors' => (string) $errors,
                'data' => $data
            ]);
            return new JsonResponse(['message' => (string) $errors], 400); // Invalid input
        }

        // Zapisz użytkownika
        try {
            $entityManager->persist($user);
            $entityManager->flush();
            $logger->info('User registered successfully', [
                'username' => $data['username']
            ]);
        } catch (\Exception $e) {
            $logger->error('Error occurred while registering user', [
                'exception' => $e->getMessage()
            ]);
            return new JsonResponse(['message' => 'An error occurred while registering the user.'], 500); // Internal server error
        }

        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }
}
