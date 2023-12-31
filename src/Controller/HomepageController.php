<?php

namespace App\Controller;

use App\Repository\ChatRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(ChatRepositoryInterface $chatRepository): Response
    {
        return $this->render('homepage/index.html.twig', [
            'chats' => $chatRepository->findAll(),
        ]);
    }
}
