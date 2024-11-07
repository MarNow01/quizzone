<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Quiz;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class CategoryController extends AbstractController
{
    #[Route('api/categories', name: 'api_categories')]
    public function categories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];
        }

        return new JsonResponse(['categories' => $data], Response::HTTP_OK);
    }

    #[Route('api/category/{id}', name: 'api_category')]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            return new JsonResponse(['error' => 'Nie znaleziono kategorii'], JsonResponse::HTTP_NOT_FOUND);
        }


        $quizes = [];
        foreach ($category->getQuizzes() as $quiz) {
            $quizes[] = [
                'id' => $quiz->getId(),
                'name' => $quiz->getName(),
                'author_name' => $quiz->getAuthor()->getUsername(),
                'date_of_creation' => $quiz->getDateOfCreation(),
            ];
        }

        return new JsonResponse([
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'quizes' => $quizes,
            ]
        ]);
    }

    #[Route('/api/category/new', name: 'api_category_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        //Dodać autoryzacje admina
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by utworzyć kategorię.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse(['error' => 'Nazwa kategorii jest wymagana.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $category = new Category();
        $category->setName($data['name']);

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Kategoria utworzona pomyślnie.',
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/category/{id}', name: 'api_category_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        //Dodać autoryzacje admina
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik musi być zalogowany, by usunąć kategorię.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Nie znaleziono kategorii.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Kategoria usunięta poprawnie.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/category/{id}', name: 'api_category_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            return new JsonResponse(['error' => 'Kategoria nie znaleziona.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name'])) {
            return new JsonResponse(['error' => 'Nazwa kategorii jest wymagana.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $category->setName($data['name']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Kategoria zmodyfikowana pomyślnie.'], JsonResponse::HTTP_OK);
    }
}
