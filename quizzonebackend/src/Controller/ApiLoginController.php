<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ApiLoginController
{
    private $authUtils;
    private $validator;

    public function __construct(AuthenticationUtils $authUtils, ValidatorInterface $validator, SessionInterface $session)
    {
        $this->authUtils = $authUtils;
        $this->validator = $validator;
        $this->session = $session;
    }


    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
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

        try {
            // Wykorzystaj custom authenticator do autoryzacji użytkownika
            $passport = new Passport(
                new UserBadge($username),
                new PasswordCredentials($password)
            );

            // Uwierzytelnij użytkownika i zapisz token w sesji
            $this->tokenStorage->setToken($passport);

            return new JsonResponse(['username' => $username, 'message' => 'Logged in successfully']);
        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Usuń dane użytkownika z sesji
        $this->session->remove('user_id');

        return new JsonResponse(['message' => 'Logged out successfully']);
    }
}
