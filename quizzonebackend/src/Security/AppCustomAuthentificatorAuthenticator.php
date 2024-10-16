<?php
namespace App\Security;

use App\Entity\User; // Upewnij się, że masz odpowiedni import dla klasy User
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Doctrine\ORM\EntityManagerInterface;

class AppCustomAuthentificatorAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $entityManager; // Zmienna do EntityManagera

    public function __construct(EntityManagerInterface $entityManager) // Konstruktor z EntityManagerem
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'api_login';
    }

    public function authenticate(Request $request): Passport
    {
        // Odczytaj dane logowania
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
    
        // Walidacja danych
        if (empty($username) || empty($password)) {
            throw new AuthenticationException('Username and password must be provided.');
        }
    
        // Sprawdź, czy użytkownik istnieje w bazie
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    
        if (!$user || !password_verify($password, $user->getPassword())) {
            throw new AuthenticationException('Invalid username or password.');
        }
    
        // Stwórz instancję Passport
        return new Passport(
            new UserBadge($username), // Użytkownik z identyfikatorem
            new CustomCredentials( // Niestandardowe poświadczenia
                function ($credentials, User $user) {
                    // Logika niestandardowego sprawdzania hasła
                    return password_verify($credentials, $user->getPassword());
                },
                $password // Przekaż hasło jako poświadczenie
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        return new JsonResponse([
            'message' => 'Login successful',
            'user' => [
                'email' => $user->getUsername(),  // lub inne informacje o użytkowniku
                'roles' => $user->getRoles(),
            ]
        ], 200);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {
        return new JsonResponse([
            'message' => 'Login failed',
            'error' => $exception->getMessageKey(),
        ], 401);
    }
}
