<?php
namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\AttemptQuizRepository;
use App\Repository\AchievementRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class AchievementService
{
    private UserRepository $userRepository;
    private AttemptQuizRepository $attemptQuizRepository;
    private AchievementRepository $achievementRepository;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(
        UserRepository $userRepository,
        AttemptQuizRepository $attemptQuizRepository,
        AchievementRepository $achievementRepository,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->userRepository = $userRepository;
        $this->attemptQuizRepository = $attemptQuizRepository;
        $this->achievementRepository = $achievementRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }
    
    private function getUser()
    {
        return $this->security->getUser();
    }
    

    public function checkAchievements(): bool
    {
        $isGetAchievement = false;
        $user = $this->getUser();
        $achievements = $user->getAchievements();
        //pierszwe osiągniecie
        if (count($achievements) === 0) {
            $newAchievement = $this->achievementRepository->findOneBy(['id' => 1]);
            if ($newAchievement) {
                $user->addAchievement($newAchievement);
                $this->entityManager->persist($user);
                $this->entityManager->flush();
    
                $isGetAchievement = true;
            }
        }
        //Osiągniecie za 100 quizów
        $achievementId = 2;
        $hasAchievement = false;
        foreach ($achievements as $achievement) {
            if ($achievement->getId() === $achievementId) {
                $hasAchievement = true;
                $isGetAchievement = true;
                break;
            }
        }
        if (!$hasAchievement) {
            $quizCount = $this->attemptQuizRepository->count(['User' => $user->getId()]);
            if ($quizCount >= 100) {
                $newAchievement = $this->achievementRepository->findOneBy(['id' => $achievementId]);
                if ($newAchievement) {
                    $user->addAchievement($newAchievement);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $isGetAchievement = true;
                }
            }
        }
        //Osiągniecie za 500 quizów
        $achievementId = 4;
        $hasAchievement = false;
        foreach ($achievements as $achievement) {
            if ($achievement->getId() === $achievementId) {
                $hasAchievement = true;
                $isGetAchievement = true;
                break;
            }
        }
        if (!$hasAchievement) {
            $quizCount = $this->attemptQuizRepository->count(['User' => $user->getId()]);
            if ($quizCount >= 500) {
                $newAchievement = $this->achievementRepository->findOneBy(['id' => $achievementId]);
                if ($newAchievement) {
                    $user->addAchievement($newAchievement);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $isGetAchievement = true;
                }
            }
        }
         // Osiągnięcie QuizMaster
        $achievementId = 3;
        $hasAchievement = false;

        foreach ($achievements as $achievement) {
            if ($achievement->getId() === $achievementId) {
                $hasAchievement = true;
                $isGetAchievement = true;
                break;
            }
        }

        if (!$hasAchievement) {
            $attempts = $this->attemptQuizRepository->findBy(['User' => $user->getId()]);
            
            $quizAttemptCounts = [];
            foreach ($attempts as $attempt) {
                $quizId = $attempt->getQuiz()->getId();
                if (!isset($quizAttemptCounts[$quizId])) {
                    $quizAttemptCounts[$quizId] = 0;
                }
                $quizAttemptCounts[$quizId]++;
            }

            $achieved = false;
            foreach ($quizAttemptCounts as $count) {
                if ($count >= 10) {
                    $achieved = true;
                    break;
                }
            }

            if ($achieved) {
                $newAchievement = $this->achievementRepository->findOneBy(['id' => $achievementId]);
                if ($newAchievement) {
                    $user->addAchievement($newAchievement);
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                    $isGetAchievement = true;
                }
            }
        }
    
        return $isGetAchievement;
    }
}
