<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $session = $request->getSession();
        /** @var UserInterface $user */
        $user = $token->getUser();

        // Set user details in the session if you want to maintain state
        $request->getSession()->set('user', [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);

        return new JsonResponse([
            'message' => 'Login successful',
            'user' => [
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ]
        ], 200);
    }
}
