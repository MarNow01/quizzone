<?php

namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\AchievementRepository;
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
    public function user(AchievementRepository $achievementRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik niezalogowany'], Response::HTTP_UNAUTHORIZED);
        }

        $points = $user->getPoints();

        $calculatePointsForLevel = function ($level) {
            return 10 + 5 * ($level - 1);
        };
    
        $level = 1;
        $requiredPoints = $calculatePointsForLevel($level);
    
        while ($points >= $requiredPoints) {
            $points -= $requiredPoints;
            $level++;
            $requiredPoints = $calculatePointsForLevel($level);
        }
    
        $have = $points;
        $nextLevel = $requiredPoints;
    
        $titles = [
            1 => 'Początkujący',
            3 => 'Młody adept',
            10 => 'Ekspert',
            15 => 'Mistrz Quizów',
        ];
    
        $title = 'Legenda';
        foreach ($titles as $lvl => $lvlTitle) {
            if ($level < $lvl) {
                break;
            }
            $title = $lvlTitle;
        }

        $achievements = [];
        foreach($user->getAchievements() as $achievement){
            $achievements[] = [
                'name' => $achievement->getName(),
                'description' => $achievement->getDescription(),
            ];
        }

        $countAchievements = $achievementRepository -> findAll();
        $countAchievements = count($countAchievements);
        $userAchievements = count($achievements);

        return new JsonResponse(['user' => [
            'username' => $user->getUsername(),
            'profilePicture' => $user->getProfilePicture(),
            'solved' => $user->getSolved(),
            'points' => $user->getPoints(),
            'level' => $level,
            'have' => $have,
            'nextLevel' => $nextLevel,
            'title' => $title,
            'achievements' => $achievements,
            'allAchievements' => $countAchievements,
            'userAchievements' => $userAchievements,
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

    #[Route('/api/leaderboard', name: 'api_leaderboard' , methods: ['GET'])]
    public function getLeaderboard(UserRepository $repository): JsonResponse
    {
        $users = $repository -> findAll();
        $leaderboard;
        foreach($users as $user){
            $leaderboard[] = [
                'username' => $user->getUsername(),
                'points' => $user->getPoints(),
                'solved' => $user->getSolved(),
            ];
        }
        return new JsonResponse (['leaderboard' => $leaderboard], Response::HTTP_OK);
    }
}
