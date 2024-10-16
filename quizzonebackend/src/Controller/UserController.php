<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/', name: 'app_course')]
    public function hello(): Response
    {
        return $this->render('user/hello.html.twig', [
            'message' => 'Hello World',
        ]);
    }
}