<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Opinion;
use App\Repository\UserRepository;
use App\Repository\QuizRepository;
use App\Repository\OpinionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class OpinionController extends AbstractController
{
    #[Route('/api/quiz/{id}/addoreditopinion', name: 'add_or_edit_opinion', methods: ['POST'])]
    public function addOpinion(int $id, Request $request, QuizRepository $quizRepository, UserRepository $userRepository, EntityManagerInterface $em, OpinionRepository $opinionRepository): JsonResponse 
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany aby wystawić opinię.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['value'])) {
            return new JsonResponse(['error' => 'Brak wymaganych pól'], 400);
        }

        $quiz = $quizRepository->find($id);

        if (!$quiz || !$user) {
            return new JsonResponse(['error' => 'Błędny quiz lub autor'], 404);
        }

        $oldOpinion = $opinionRepository->findBy(['quiz'=>$quiz,'user'=>$user]);

        $status = 201;

        if ($oldOpinion){
            $oldOpinion = $oldOpinion[0];
            $oldOpinion->setValue($data['value']);
            $em->persist($oldOpinion);
            $em->flush();  
            $status = 202;
        }
        else{
            $opinion = new Opinion();
            $opinion->setValue($data['value'])
                    ->setUser($user)
                    ->setQuiz($quiz);

            $em->persist($opinion);
            $em->flush();
        }

        return new JsonResponse([
            'status'=>'Sukces'
        ], $status);
    }
}
