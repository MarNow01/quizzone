<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiLoginController
{
    private $authUtils;
    private $validator;

    public function __construct(AuthenticationUtils $authUtils, ValidatorInterface $validator)
    {
        $this->authUtils = $authUtils;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse
    {
        // Odczytaj dane z żądania
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // Sprawdź, czy dane zostały podane
        if (empty($username) || empty($password)) {
            return new JsonResponse(['error' => 'Username and password are required.'], 400);
        }

        // Użyj AuthenticationUtils do zalogowania
        $error = $this->authUtils->getLastAuthenticationError();
        if ($error instanceof AuthenticationException) {
            return new JsonResponse(['error' => $error->getMessage()], 403);
        }

        // Zwróć dane użytkownika po pomyślnym logowaniu
        return new JsonResponse(['username' => $username, 'message' => 'Logged in successfully']);
    }
}
