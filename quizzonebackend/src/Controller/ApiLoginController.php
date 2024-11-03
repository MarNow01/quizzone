<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use InvalidArgumentException;

class ApiLoginController extends AbstractController
{
    private $userProvider;
    private $jwtManager;
    
    public function __construct(UserProviderInterface $userProvider, JWTTokenManagerInterface $jwtManager)
    {
        $this->userProvider = $userProvider;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            throw new InvalidArgumentException('Zgubiona dane logowania');
        }

        $user = $this->userProvider->loadUserByUsername($data['username']);

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            throw new InvalidArgumentException('Niepoprawne dane logowania');
        }

        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
